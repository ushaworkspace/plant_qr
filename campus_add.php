<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tree_code = trim($_POST['tree_code'] ?? '');
    $plant_name = trim($_POST['plant_name'] ?? '');
    $scientific_name = trim($_POST['scientific_name'] ?? '');
    $family = trim($_POST['family'] ?? '');
    $tree_type = trim($_POST['tree_type'] ?? '');
    $location = trim($_POST['location'] ?? '');

    /* NEW: Latitude and Longitude */
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');

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

    } elseif (
        $latitude != "" &&
        !is_numeric($latitude)
    ) {

        $message = "Latitude must be a valid number.";
        $message_type = "error";

    } elseif (
        $longitude != "" &&
        !is_numeric($longitude)
    ) {

        $message = "Longitude must be a valid number.";
        $message_type = "error";

    } elseif (
        ($latitude != "" && ($latitude < -90 || $latitude > 90)) ||
        ($longitude != "" && ($longitude < -180 || $longitude > 180))
    ) {

        $message = "Invalid latitude or longitude value.";
        $message_type = "error";

    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] != UPLOAD_ERR_OK) {

        $message = "Please upload the campus tree image.";
        $message_type = "error";

    } else {

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

            /* Check duplicate Tree Code */

            $check_sql = "SELECT id FROM campus_trees WHERE tree_code = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);

            mysqli_stmt_bind_param(
                $check_stmt,
                "s",
                $tree_code
            );

            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {

                $message = "Tree Code already exists. Please use another code.";
                $message_type = "error";

            } else {

                /* Create image folder */

                $upload_folder = "images/campus/";

                if (!is_dir($upload_folder)) {
                    mkdir($upload_folder, 0777, true);
                }

                /* Create unique filename */

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

                $image_path =
                    $upload_folder . $new_filename;

                /* Upload image */

                if (move_uploaded_file($image_tmp, $image_path)) {

                    /*
                     * Insert into campus_trees
                     *
                     * NEW FIELDS:
                     * latitude
                     * longitude
                     */

                    $sql = "INSERT INTO campus_trees
                    (
                        tree_code,
                        plant_name,
                        scientific_name,
                        family,
                        tree_type,
                        location,
                        latitude,
                        longitude,
                        description,
                        medicinal,
                        water,
                        sunlight,
                        soil,
                        temperature,
                        fertilizer,
                        care,
                        diseases,
                        image
                    )
                    VALUES
                    (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";

                    $stmt = mysqli_prepare($conn, $sql);

                    /*
                     * Convert empty values to NULL
                     * for database storage.
                     */

                    $latitude_value =
                        ($latitude === "") ? null : (float)$latitude;

                    $longitude_value =
                        ($longitude === "") ? null : (float)$longitude;

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssssddssssssssss",
                        $tree_code,
                        $plant_name,
                        $scientific_name,
                        $family,
                        $tree_type,
                        $location,
                        $latitude_value,
                        $longitude_value,
                        $description,
                        $medicinal,
                        $water,
                        $sunlight,
                        $soil,
                        $temperature,
                        $fertilizer,
                        $care,
                        $diseases,
                        $image_path
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        $message = "Campus tree added successfully!";
                        $message_type = "success";

                    } else {

                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }

                        $message = "Database Error: " . mysqli_error($conn);
                        $message_type = "error";
                    }

                    mysqli_stmt_close($stmt);

                } else {

                    $message = "Unable to upload image.";
                    $message_type = "error";
                }
            }

            mysqli_stmt_close($check_stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Campus Tree</title>

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
    margin-bottom: 8px;
}

.subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 30px;
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
    color: #333;
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

.file-box {
    background: #f1f8f2;
    padding: 15px;
    border: 1px dashed #66bb6a;
    border-radius: 10px;
}

.file-note {
    font-size: 12px;
    color: #666;
    margin-top: 6px;
}

.location-note {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
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

.back:hover {
    text-decoration: underline;
}

@media (max-width: 700px) {

    .container {
        padding: 22px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
}

</style>

</head>

<body>

<div class="container">

<h1>🌳 Add Campus Tree</h1>

<div class="subtitle">
    Add complete information about a tree in the college campus
</div>

<?php if ($message != ""): ?>

<div class="message <?php echo $message_type; ?>">
    <?php echo htmlspecialchars($message); ?>
</div>

<?php endif; ?>


<form method="POST" enctype="multipart/form-data">

<!-- BASIC INFORMATION -->

<div class="section-title">
    🌿 Basic Information
</div>

<div class="form-row">

    <div class="form-group">

        <label>Tree Code *</label>

        <input
            type="text"
            name="tree_code"
            placeholder="Example: TREE004"
            required
        >

    </div>

    <div class="form-group">

        <label>Plant Name *</label>

        <input
            type="text"
            name="plant_name"
            placeholder="Example: Neem"
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
            placeholder="Example: Azadirachta indica"
        >

    </div>

    <div class="form-group">

        <label>Family</label>

        <input
            type="text"
            name="family"
            placeholder="Example: Meliaceae"
        >

    </div>

</div>


<div class="form-row">

    <div class="form-group">

        <label>Tree Type</label>

        <select name="tree_type">

            <option value="">Select Type</option>

            <option value="Tree">Tree</option>

            <option value="Shrub">Shrub</option>

            <option value="Herb">Herb</option>

            <option value="Climber">Climber</option>

        </select>

    </div>

    <div class="form-group">

        <label>Campus Location *</label>

        <input
            type="text"
            name="location"
            placeholder="Example: Near Main Block"
            required
        >

    </div>

</div>


<!-- NEW: MAP COORDINATES -->

<div class="section-title">
    📍 Tree Map Location
</div>

<div class="form-row">

    <div class="form-group">

        <label>Latitude</label>

        <input
            type="text"
            name="latitude"
            placeholder="Example: 9.1667"
        >

        <div class="location-note">
            Enter the tree's latitude coordinate.
        </div>

    </div>

    <div class="form-group">

        <label>Longitude</label>

        <input
            type="text"
            name="longitude"
            placeholder="Example: 77.8667"
        >

        <div class="location-note">
            Enter the tree's longitude coordinate.
        </div>

    </div>

</div>


<!-- IMAGE -->

<div class="section-title">
    📷 Campus Tree Image
</div>

<div class="form-group file-box">

    <label>Upload Actual Campus Tree Image *</label>

    <input
        type="file"
        name="image"
        accept=".jpg,.jpeg,.png,.webp"
        required
    >

    <div class="file-note">
        JPG, JPEG, PNG or WEBP | Maximum 5 MB
    </div>

</div>


<!-- DESCRIPTION -->

<div class="section-title">
    📖 Description
</div>

<div class="form-group">

    <textarea
        name="description"
        placeholder="Enter description about this campus tree"
    ></textarea>

</div>


<!-- PLANT INFORMATION -->

<div class="section-title">
    🌱 Plant Information
</div>

<div class="form-group">

    <label>Medicinal / Uses</label>

    <textarea
        name="medicinal"
        placeholder="Enter medicinal uses or common uses"
    ></textarea>

</div>


<div class="form-row">

    <div class="form-group">

        <label>Water Requirement</label>

        <input
            type="text"
            name="water"
            placeholder="Example: Low to Moderate"
        >

    </div>

    <div class="form-group">

        <label>Sunlight</label>

        <input
            type="text"
            name="sunlight"
            placeholder="Example: Full sunlight"
        >

    </div>

</div>


<div class="form-row">

    <div class="form-group">

        <label>Soil</label>

        <input
            type="text"
            name="soil"
            placeholder="Example: Well-drained soil"
        >

    </div>

    <div class="form-group">

        <label>Temperature</label>

        <input
            type="text"
            name="temperature"
            placeholder="Example: 20°C - 35°C"
        >

    </div>

</div>


<div class="form-group">

    <label>Fertilizer</label>

    <input
        type="text"
        name="fertilizer"
        placeholder="Enter fertilizer information"
    >

</div>


<!-- CARE -->

<div class="section-title">
    🪴 Care / Maintenance
</div>

<div class="form-group">

    <textarea
        name="care"
        placeholder="Enter tree care and maintenance information"
    ></textarea>

</div>


<!-- DISEASES -->

<div class="section-title">
    🦠 Common Diseases
</div>

<div class="form-group">

    <textarea
        name="diseases"
        placeholder="Enter common diseases or problems"
    ></textarea>

</div>


<button type="submit">
    🌱 Add Campus Tree
</button>

</form>


<a class="back" href="index.php">
    ⬅ Back to Dashboard
</a>

</div>

</body>

</html>