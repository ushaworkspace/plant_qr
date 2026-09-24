"""
orb_match.py (v3 - SIFT + Homography + Color fusion, with descriptor caching)
------------------------------------------------------------------------------
WHAT CHANGED FROM v2 (ORB + color):

1. SIFT instead of ORB
   SIFT features are scale & rotation invariant, which matters a lot for
   phone photos taken at different distances/angles. ORB is faster but
   less reliable for this exact use case (matching a fresh phone photo
   against reference dataset photos).

2. RANSAC Homography verification
   Before, we counted ALL "good" ratio-test matches as the score. Problem:
   two completely different leafy/green images can share a few random
   texture matches just by chance (leaves all look similar at the patch
   level). Now we additionally check whether the matched points agree on
   one consistent geometric transform (homography). Only matches that
   pass this geometric check ("inliers") count. This removes a lot of
   false-positive noise and should directly help the "half of tree
   images predicted wrong" issue mentioned in your report.

3. Descriptor caching (descriptor_cache.pkl)
   Previously every single prediction request recomputed keypoints for
   EVERY dataset image from scratch. As your dataset grows (103+ images),
   this gets slow. Now dataset image descriptors are cached to disk and
   only recomputed if the file changes (mtime check). This does not
   change accuracy, only speed.

Called from PHP like:
    python.exe predict.py "uploaded.jpg"
"""

import os
import cv2
import numpy as np
import pickle

CACHE_FILE = os.path.join(os.path.dirname(__file__), "descriptor_cache.pkl")

sift = cv2.SIFT_create(nfeatures=1500)

FLANN_INDEX_KDTREE = 1
_index_params = dict(algorithm=FLANN_INDEX_KDTREE, trees=5)
_search_params = dict(checks=50)
flann = cv2.FlannBasedMatcher(_index_params, _search_params)

_cache = {}
_cache_loaded = False


def _load_cache():
    global _cache, _cache_loaded
    if _cache_loaded:
        return
    if os.path.exists(CACHE_FILE):
        try:
            with open(CACHE_FILE, "rb") as f:
                _cache = pickle.load(f)
        except Exception:
            _cache = {}
    _cache_loaded = True


def save_cache():
    """Call this once after a prediction run to persist the descriptor
    cache to disk. Only dataset images are cached (the uploaded image is
    never cached, since it's different every time)."""
    try:
        with open(CACHE_FILE, "wb") as f:
            pickle.dump(_cache, f)
    except Exception:
        pass


def load_and_prepare(image_path, resize_width=500):
    img = cv2.imread(image_path)
    if img is None:
        return None

    h, w = img.shape[:2]
    scale = resize_width / w
    img = cv2.resize(img, (resize_width, int(h * scale)))

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    gray = clahe.apply(gray)

    return gray


def get_descriptors(image_path, use_cache=True):
    """Returns (keypoints, descriptors). Dataset images are cached on disk
    keyed by (path, last-modified-time), so replacing/adding a dataset
    image automatically invalidates the old cached entry."""
    if use_cache:
        _load_cache()
        try:
            mtime = os.path.getmtime(image_path)
        except OSError:
            mtime = 0
        key = (image_path, mtime)
        if key in _cache:
            return _cache[key]

    gray = load_and_prepare(image_path)
    if gray is None:
        result = (None, None)
    else:
        kp, des = sift.detectAndCompute(gray, None)
        # keypoints aren't picklable directly, so store plain tuples
        if use_cache and kp is not None:
            kp_data = [(k.pt, k.size, k.angle, k.response, k.octave, k.class_id) for k in kp]
        else:
            kp_data = kp
        result = (kp_data, des)

    if use_cache:
        _cache[key] = result

    return result


def _to_keypoints(kp_data):
    if kp_data is None:
        return None
    if len(kp_data) > 0 and isinstance(kp_data[0], cv2.KeyPoint):
        return kp_data
    return [
        cv2.KeyPoint(x=pt[0], y=pt[1], size=size, angle=angle, response=response, octave=octave, class_id=class_id)
        for (pt, size, angle, response, octave, class_id) in kp_data
    ]


def color_histogram_score(img_path1, img_path2):
    """
    Compares two images using HSV color histograms. Plants often differ
    noticeably in leaf/fruit color and tone, which shape-only features
    ignore. This is a secondary signal alongside SIFT.
    """
    img1 = cv2.imread(img_path1)
    img2 = cv2.imread(img_path2)

    if img1 is None or img2 is None:
        return 0.0

    hsv1 = cv2.cvtColor(img1, cv2.COLOR_BGR2HSV)
    hsv2 = cv2.cvtColor(img2, cv2.COLOR_BGR2HSV)

    hist1 = cv2.calcHist([hsv1], [0, 1], None, [50, 60], [0, 180, 0, 256])
    hist2 = cv2.calcHist([hsv2], [0, 1], None, [50, 60], [0, 180, 0, 256])

    cv2.normalize(hist1, hist1, 0, 1, cv2.NORM_MINMAX)
    cv2.normalize(hist2, hist2, 0, 1, cv2.NORM_MINMAX)

    correlation = cv2.compareHist(hist1, hist2, cv2.HISTCMP_CORREL)
    correlation = max(0.0, correlation)

    return round(correlation * 100, 2)


def compare_images(img_path1, img_path2, ratio_thresh=0.75):
    kp1_data, des1 = get_descriptors(img_path1, use_cache=False)
    kp2_data, des2 = get_descriptors(img_path2, use_cache=True)

    if des1 is None or des2 is None or len(des1) < 2 or len(des2) < 2:
        return 0.0

    try:
        matches = flann.knnMatch(des1.astype(np.float32), des2.astype(np.float32), k=2)
    except cv2.error:
        return 0.0

    good_matches = []
    for pair in matches:
        if len(pair) == 2:
            m, n = pair
            if m.distance < ratio_thresh * n.distance:
                good_matches.append(m)

    smaller_kp_count = min(len(des1), len(des2))
    if smaller_kp_count == 0 or len(good_matches) < 4:
        return 0.0

    kp1 = _to_keypoints(kp1_data)
    kp2 = _to_keypoints(kp2_data)

    src_pts = np.float32([kp1[m.queryIdx].pt for m in good_matches]).reshape(-1, 1, 2)
    dst_pts = np.float32([kp2[m.trainIdx].pt for m in good_matches]).reshape(-1, 1, 2)

    try:
        _, mask = cv2.findHomography(src_pts, dst_pts, cv2.RANSAC, 5.0)
        inliers = int(mask.sum()) if mask is not None else len(good_matches)
    except cv2.error:
        inliers = len(good_matches)

    percentage_score = (inliers / smaller_kp_count) * 100
    return round(percentage_score, 2)


def combined_score(img_path1, img_path2, feature_weight=0.65, color_weight=0.35):
    """
    Fuses SIFT+homography feature matching with color histogram similarity.
    """
    feature_score = compare_images(img_path1, img_path2)
    color_score = color_histogram_score(img_path1, img_path2)

    return round((feature_score * feature_weight) + (color_score * color_weight), 2)