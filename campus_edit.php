<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid tree ID.");
}

/* Get existing tree */
$sql = "SELECT * FROM campus_trees WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Campus tree not found.");
}

$tree = mysqli_fetch_assoc($result);

$message = "";
$message_type = "";


/* UPDATE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tree_code = trim($_POST['tree_code'] ?? '');
    $plant_name = trim($_POST['plant_name'] ?? '');
    $scientific_name = trim($_POST['scientific_name'] ?? '');
    $family = trim($_POST['family'] ?? '');
    $tree_type = trim($_POST['tree_type'] ?? '');
    $location = trim($_POST['location'] ?? '');

    $description = trim($_POST['description'] ?? '');
    $medicinal = trim($_POST['medicinal'] ?? '');
    $water = trim($_POST['water'] ?? '');
    $sunlight = trim($_POST['sunlight'] ?? '');
    $soil = trim($_POST['soil'] ?? '');
    $temperature = trim($_POST['temperature'] ?? '');
    $fertilizer = trim($_POST['fertilizer'] ?? '');
    $care = trim($_POST['care'] ?? '');
    $diseases = trim($_POST['diseases'] ?? '');

    if ($tree_code == "" || $plant_name == "" || $location == "") {

        $message = "Please fill Tree Code, Plant Name and Location.";
        $message_type = "error";

    } else {

        /* Check duplicate tree code except current tree */

        $check_sql = "SELECT id FROM campus_trees
                      WHERE tree_code = ? AND id != ?";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param(
            $check_stmt,
            "si",
            $tree_code,
            $id
        );

        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {

            $message = "Tree Code already exists.";
            $message_type = "error";

        } else {

            $image_path = $tree['image'];

            /* New image uploaded? */

            if (
                isset($_FILES['image']) &&
                $_FILES['image']['error'] == UPLOAD_ERR_OK
            ) {

                $allowed_types = [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/jpg'
                ];

                $image_tmp = $_FILES['image']['tmp_name'];
                $image_size = $_FILES['image']['size'];
                $image_type = mime_content_type($image_tmp);

                if (!in_array($image_type, $allowed_types)) {

                    $message = "Only JPG, JPEG, PNG or WEBP images are allowed.";
                    $message_type = "error";

                } elseif ($image_size > 5 * 1024 * 1024) {

                    $message = "Image size must be less than 5 MB.";
                    $message_type = "error";

                } else {

                    $upload_folder = "images/campus/";

                    if (!is_dir($upload_folder)) {
                        mkdir($upload_folder, 0777, true);
                    }

                    $extension = strtolower(
                        pathinfo(
                            $_FILES['image']['name'],
                            PATHINFO_EXTENSION
                        )
                    );

                    $safe_code = preg_replace(
                        "/[^A-Za-z0-9_-]/",
                        "_",
                        $tree_code
                    );

                    $new_filename =
                        $safe_code . "_" . time() . "." . $extension;

                    $new_image_path =
                        $upload_folder . $new_filename;

                    if (move_uploaded_file(
                        $image_tmp,
                        $new_image_path
                    )) {

                        /* Delete old image */

                        if (
                            !empty($tree['image']) &&
                            file_exists($tree['image'])
                        ) {
                            unlink($tree['image']);
                        }

                        $image_path = $new_image_path;

                    } else {

                        $message = "Unable to upload new image.";
                        $message_type = "error";
                    }
                }
            }

            /* Continue only if no image error */

            if ($message == "") {

                $update_sql = "UPDATE campus_trees SET
                    tree_code = ?,
                    plant_name = ?,
                    scientific_name = ?,
                    family = ?,
                    tree_type = ?,
                    location = ?,
                    description = ?,
                    medicinal = ?,
                    water = ?,
                    sunlight = ?,
                    soil = ?,
                    temperature = ?,
                    fertilizer = ?,
                    care = ?,
                    diseases = ?,
                    image = ?
                    WHERE id = ?";

                $update_stmt = mysqli_prepare(
                    $conn,
                    $update_sql
                );

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "ssssssssssssssssi",
                    $tree_code,
                    $plant_name,
                    $scientific_name,
                    $family,
                    $tree_type,
                    $location,
                    $description,
                    $medicinal,
                    $water,
                    $sunlight,
                    $soil,
                    $temperature,
                    $fertilizer,
                    $care,
                    $diseases,
                    $image_path,
                    $id
                );

                if (mysqli_stmt_execute($update_stmt)) {

                    $message = "Campus tree updated successfully!";
                    $message_type = "success";

                    /* Refresh data */

                    $refresh_sql =
                        "SELECT * FROM campus_trees WHERE id = ?";

                    $refresh_stmt =
                        mysqli_prepare($conn, $refresh_sql);

                    mysqli_stmt_bind_param(
                        $refresh_stmt,
                        "i",
                        $id
                    );

                    mysqli_stmt_execute($refresh_stmt);

                    $tree = mysqli_fetch_assoc(
                        mysqli_stmt_get_result($refresh_stmt)
                    );

                    mysqli_stmt_close($refresh_stmt);

                } else {

                    $message =
                        "Database Error: " .
                        mysqli_error($conn);

                    $message_type = "error";
                }

                mysqli_stmt_close($update_stmt);
            }
        }

        mysqli_stmt_close($check_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Campus Tree</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
    padding: 35px 15px;
}

.container {
    width: 850px;
    max-width: 100%;
    margin: auto;
    background: white;
    padding: 35px;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

h1 {
    text-align: center;
    color: #2e7d32;
}

.subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 25px;
}

.section-title {
    margin-top: 28px;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid #c8e6c9;
    color: #2e7d32;
    font-size: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
}

input,
select,
textarea {
    width: 100%;
    padding: 11px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    font-family: Arial, sans-serif;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #2e7d32;
}

textarea {
    min-height: 90px;
    resize: vertical;
}

.current-image {
    width: 220px;
    max-width: 100%;
    border-radius: 12px;
    margin-bottom: 10px;
}

.file-box {
    background: #f1f8f2;
    padding: 15px;
    border: 1px dashed #66bb6a;
    border-radius: 10px;
}

button {
    width: 100%;
    margin-top: 25px;
    padding: 14px;
    border: none;
    border-radius: 9px;
    background: #2e7d32;
    color: white;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #1b5e20;
}

.message {
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
}

.success {
    background: #e8f5e9;
    color: #2e7d32;
}

.error {
    background: #ffebee;
    color: #c62828;
}

.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #2e7d32;
    text-decoration: none;
    font-weight: bold;
}

@media (max-width: 700px) {

    .container {
        padding: 22px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="container">

<h1>✏️ Edit Campus Tree</h1>

<div class="subtitle">
    Update campus tree information
</div>

<?php if ($message != ""): ?>

<div class="message <?php echo $message_type; ?>">
    <?php echo htmlspecialchars($message); ?>
</div>

<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<div class="section-title">
    🌿 Basic Information
</div>

<div class="form-row">

<div class="form-group">

<label>Tree Code *</label>

<input
    type="text"
    name="tree_code"
    value="<?php echo htmlspecialchars($tree['tree_code']); ?>"
    required
>

</div>

<div class="form-group">

<label>Plant Name *</label>

<input
    type="text"
    name="plant_name"
    value="<?php echo htmlspecialchars($tree['plant_name']); ?>"
    required
>

</div>

</div>


<div class="form-row">

<div class="form-group">

<label>Scientific Name</label>

<input
    type="text"
    name="scientific_name"
    value="<?php echo htmlspecialchars($tree['scientific_name']); ?>"
>

</div>

<div class="form-group">

<label>Family</label>

<input
    type="text"
    name="family"
    value="<?php echo htmlspecialchars($tree['family']); ?>"
>

</div>

</div>


<div class="form-row">

<div class="form-group">

<label>Tree Type</label>

<select name="tree_type">

<option value="">Select Type</option>

<option value="Tree"
<?php if ($tree['tree_type'] == 'Tree') echo 'selected'; ?>>
Tree
</option>

<option value="Shrub"
<?php if ($tree['tree_type'] == 'Shrub') echo 'selected'; ?>>
Shrub
</option>

<option value="Herb"
<?php if ($tree['tree_type'] == 'Herb') echo 'selected'; ?>>
Herb
</option>

<option value="Climber"
<?php if ($tree['tree_type'] == 'Climber') echo 'selected'; ?>>
Climber
</option>

</select>

</div>

<div class="form-group">

<label>Campus Location *</label>

<input
    type="text"
    name="location"
    value="<?php echo htmlspecialchars($tree['location']); ?>"
    required
>

</div>

</div>


<div class="section-title">
    📷 Tree Image
</div>

<div class="form-group file-box">

<?php if (!empty($tree['image'])): ?>

<img
    src="<?php echo htmlspecialchars($tree['image']); ?>"
    class="current-image"
    alt="Current Tree Image"
>

<br>

<?php endif; ?>

<label>Upload New Image (Optional)</label>

<input
    type="file"
    name="image"
    accept=".jpg,.jpeg,.png,.webp"
>

</div>


<div class="section-title">
    📖 Description
</div>

<div class="form-group">

<textarea name="description"><?php
echo htmlspecialchars($tree['description']);
?></textarea>

</div>


<div class="section-title">
    🌱 Plant Information
</div>

<div class="form-group">

<label>Medicinal / Uses</label>

<textarea name="medicinal"><?php
echo htmlspecialchars($tree['medicinal']);
?></textarea>

</div>


<div class="form-row">

<div class="form-group">

<label>Water Requirement</label>

<input
    type="text"
    name="water"
    value="<?php echo htmlspecialchars($tree['water']); ?>"
>

</div>

<div class="form-group">

<label>Sunlight</label>

<input
    type="text"
    name="sunlight"
    value="<?php echo htmlspecialchars($tree['sunlight']); ?>"
>

</div>

</div>


<div class="form-row">

<div class="form-group">

<label>Soil</label>

<input
    type="text"
    name="soil"
    value="<?php echo htmlspecialchars($tree['soil']); ?>"
>

</div>

<div class="form-group">

<label>Temperature</label>

<input
    type="text"
    name="temperature"
    value="<?php echo htmlspecialchars($tree['temperature']); ?>"
>

</div>

</div>


<div class="form-group">

<label>Fertilizer</label>

<input
    type="text"
    name="fertilizer"
    value="<?php echo htmlspecialchars($tree['fertilizer']); ?>"
>

</div>


<div class="section-title">
    🪴 Care / Maintenance
</div>

<div class="form-group">

<textarea name="care"><?php
echo htmlspecialchars($tree['care']);
?></textarea>

</div>


<div class="section-title">
    🦠 Common Diseases
</div>

<div class="form-group">

<textarea name="diseases"><?php
echo htmlspecialchars($tree['diseases']);
?></textarea>

</div>


<button type="submit">
    💾 Update Campus Tree
</button>

</form>


<a href="index.php" class="back">
    ⬅ Back to Dashboard
</a>

</div>

</body>

</html>