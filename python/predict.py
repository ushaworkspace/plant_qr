"""
predict.py (v3 - SIFT + Homography, top-K voting)
------------------------------------------------------
WHAT CHANGED FROM v2:

Old scoring per plant class:
    combined = (best_single_image_score * 0.7) + (average_of_ALL_images * 0.3)

Problem: averaging across ALL images in a class means a couple of bad
photos in that folder (blurry, weird angle, different plant part) drag
the whole class's score down, even if 3-4 images matched really well.
With only 6-13 images per class, one bad image has a big effect.

New scoring per plant class:
    Take the TOP_K best-matching images in that class and average just
    those. This is closer to "does this class have several genuinely
    similar photos" without being dragged down by class outliers.

Called from PHP like:
    python.exe predict.py "uploaded.jpg"
    python.exe predict.py "uploaded.jpg" --debug
"""

import os
import sys
from orb_match import combined_score, save_cache

DATASET_DIR = os.path.join(os.path.dirname(__file__), "..", "dataset")

# Tune this after testing with real photos from a few different phones.
MIN_CONFIDENCE_PERCENT = 5.0

# How many best-matching images per class to average.
TOP_K = 3

VALID_EXTENSIONS = (".jpg", ".jpeg", ".png", ".webp", ".jfif", ".avif")


def predict_plant(uploaded_image_path):
    if not os.path.exists(uploaded_image_path):
        print("ERROR: Uploaded image not found")
        return None

    if not os.path.isdir(DATASET_DIR):
        print("ERROR: Dataset folder not found:", DATASET_DIR)
        return None

    plant_scores = {}

    for plant_name in os.listdir(DATASET_DIR):
        plant_folder = os.path.join(DATASET_DIR, plant_name)
        if not os.path.isdir(plant_folder):
            continue

        scores = []
        for img_file in os.listdir(plant_folder):
            if not img_file.lower().endswith(VALID_EXTENSIONS):
                continue
            img_path = os.path.join(plant_folder, img_file)
            score = combined_score(uploaded_image_path, img_path)
            scores.append(score)

        if scores:
            plant_scores[plant_name] = scores

    # Persist descriptor cache for dataset images so next run is faster.
    save_cache()

    if not plant_scores:
        print("ERROR: No dataset images could be compared")
        return None

    all_results = []
    for plant_name, scores in plant_scores.items():
        scores_sorted = sorted(scores, reverse=True)
        top_k = scores_sorted[:TOP_K]
        top_k_avg = sum(top_k) / len(top_k)
        all_results.append((plant_name, top_k_avg, scores_sorted[0], sum(scores) / len(scores)))

    all_results.sort(key=lambda x: x[1], reverse=True)
    best_plant, best_value, _, _ = all_results[0]

    return best_plant, best_value, all_results


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("ERROR: No image path provided")
        sys.exit(1)

    uploaded_image = sys.argv[1]
    debug = "--debug" in sys.argv

    result = predict_plant(uploaded_image)

    if result is None:
        sys.exit(1)

    plant_name, confidence_score, all_results = result

    if confidence_score < MIN_CONFIDENCE_PERCENT:
        print("FINAL RESULT Plant: UNKNOWN")
        print(f"CONFIDENCE: {confidence_score:.2f}")
        print("NOTE: Below minimum confidence threshold - photo unclear or plant not in dataset")
    else:
        print(f"FINAL RESULT Plant: {plant_name}")
        print(f"CONFIDENCE: {confidence_score:.2f}")

    # Always print top-3 candidates in a machine-parseable way so PHP can
    # optionally offer "did you mean...?" suggestions when confidence is low.
    print("CANDIDATES:", ";".join(f"{name}:{val:.2f}" for name, val, _, _ in all_results[:3]))

    if debug:
        print("\n--- DEBUG: Top 5 candidates ---")
        for name, topk, top, avg in all_results[:5]:
            print(f"{name:20s} top{TOP_K}_avg={topk:.2f}  top1={top:.2f}  avg_all={avg:.2f}")