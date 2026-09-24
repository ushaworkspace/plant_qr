<?php

require_once "db.php";


/* ---------------------------------------
   Plant name normalization
---------------------------------------- */

function normalizePlantName($name)
{
    $name = strtolower(trim($name));

    $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);

    $name = preg_replace('/\b(leaves)\b/', 'leaf', $name);

    $removeWords = array(
        'tree',
        'plant',
        'plants',
        'shrub',
        'herb',
        'climber'
    );

    foreach ($removeWords as $word) {
        $name = preg_replace('/\b' . $word . '\b/', ' ', $name);
    }

    $name = preg_replace('/\s+/', ' ', $name);

    return trim($name);
}


$resultMessage = "";
$resultType = "";

$predictedPlant = "";
$confidence = 0;

$matchingTrees = array();


/* ---------------------------------------
   Image Upload
---------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_FILES["plant_image"])) {

        $resultMessage = "Please select a plant image.";
        $resultType = "error";

    } else {

        $file = $_FILES["plant_image"];

        /* Upload error */

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $resultMessage = "Unable to upload the image.";
            $resultType = "error";

        } else {

            /* Maximum file size = 5 MB */

            $maxSize = 5 * 1024 * 1024;

            if ($file["size"] > $maxSize) {

                $resultMessage =
                    "Image size must be less than 5 MB.";

                $resultType = "error";

            } else {

                /* Allowed image types */

                $allowedTypes = array(
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                );

                /* Check actual image */

                $imageInfo =
                    @getimagesize($file["tmp_name"]);

                if ($imageInfo === false) {

                    $resultMessage =
                        "Please upload a valid image.";

                    $resultType = "error";

                } elseif (
                    !in_array(
                        $imageInfo["mime"],
                        $allowedTypes
                    )
                ) {

                    $resultMessage =
                        "Only JPG, PNG and WEBP images are allowed.";

                    $resultType = "error";

                } else {

                    /* ---------------------------------------
                       Save uploaded image
                    ---------------------------------------- */

                    $originalName =
                        basename($file["name"]);

                    $safeName =
                        preg_replace(
                            '/[^a-zA-Z0-9._-]/',
                            '_',
                            $originalName
                        );

                    $fileName =
                        time() . "_" . $safeName;

                    $targetPath =
                        __DIR__ . "/images/" . $fileName;

                    if (
                        !move_uploaded_file(
                            $file["tmp_name"],
                            $targetPath
                        )
                    ) {

                        $resultMessage =
                            "Failed to save the uploaded image.";

                        $resultType = "error";

                    } else {

                        /* ---------------------------------------
                           Run Python OpenCV prediction
                        ---------------------------------------- */

                        $pythonFolder =
                            __DIR__ . "/python";

                        $pythonScript =
                            $pythonFolder . "/predict.py";

                        if (file_exists($pythonScript)) {

                            $pythonCommand =
                                'cd /d "' .
                                $pythonFolder .
                                '" && python predict.py "' .
                                $targetPath .
                                '" 2>&1';

                            $output =
                                shell_exec($pythonCommand);

                        } else {

                            $output = "";

                        }


                        /* ---------------------------------------
                           Read Python result
                        ---------------------------------------- */

                        $predictedPlant = "";

                        $confidence = 0;

                        if ($output !== null && $output !== "") {

                            /* Plant name */

                            if (
                                preg_match(
                                    '/FINAL RESULT Plant:\s*(.+)/i',
                                    $output,
                                    $match
                                )
                            ) {

                                $predictedPlant =
                                    trim($match[1]);

                            }

                            /* Confidence - used internally only */

                            if (
                                preg_match(
                                    '/CONFIDENCE:\s*([0-9.]+)/i',
                                    $output,
                                    $match
                                )
                            ) {

                                $confidence =
                                    floatval($match[1]);

                            }

                        }


                        /* ---------------------------------------
                           Find matching campus trees
                        ---------------------------------------- */

                        if ($predictedPlant !== "") {

                            $normalizedPrediction =
                                normalizePlantName(
                                    $predictedPlant
                                );


                            $sql =
                                "SELECT
                                    id,
                                    tree_code,
                                    plant_name,
                                    scientific_name,
                                    family,
                                    tree_type,
                                    location,
                                    image
                                 FROM campus_trees
                                 ORDER BY plant_name ASC";


                            $dbResult =
                                mysqli_query(
                                    $conn,
                                    $sql
                                );


                            if ($dbResult) {

                                while (
                                    $row =
                                    mysqli_fetch_assoc(
                                        $dbResult
                                    )
                                ) {

                                    $normalizedDBName =
                                        normalizePlantName(
                                            $row["plant_name"]
                                        );


                                    if (
                                        $normalizedPrediction ===
                                        $normalizedDBName
                                    ) {

                                        $matchingTrees[] =
                                            $row;

                                    }

                                }

                            }


                            /* ---------------------------------------
                               Result handling
                            ---------------------------------------- */

                            if (count($matchingTrees) > 0) {

                                $resultMessage =
                                    "Plant identified as: <strong>" .
                                    htmlspecialchars(
                                        $predictedPlant
                                    ) .
                                    "</strong>";

                                $resultMessage .=
                                    "<br><br>" .
                                    "Matching campus trees found: " .
                                    count($matchingTrees);

                                $resultType = "success";

                            } else {

                                $resultMessage =
                                    "Plant identified as: <strong>" .
                                    htmlspecialchars(
                                        $predictedPlant
                                    ) .
                                    "</strong>";

                                $resultMessage .=
                                    "<br><br>" .
                                    "No campus tree records were found for this plant.";

                                $resultType = "warning";

                            }

                        } else {

                            $resultMessage =
                                "Sorry, the plant could not be identified.";

                            $resultType = "error";

                        }

                    }

                }

            }

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Identify Campus Plant</title>


<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}


body {

    min-height: 100vh;

    background:
        linear-gradient(
            135deg,
            #e8f5e9,
            #f1f8e9
        );

    padding: 25px;
}


.container {

    width: 100%;

    max-width: 900px;

    margin: 30px auto;

    background: white;

    padding: 35px;

    border-radius: 20px;

    text-align: center;

    box-shadow:
        0 8px 25px rgba(0, 0, 0, 0.12);
}


.icon {

    font-size: 52px;

    margin-bottom: 10px;
}


h1 {

    color: #2e7d32;

    font-size: 28px;

    margin-bottom: 8px;
}


.subtitle {

    color: #666;

    font-size: 16px;

    margin-bottom: 25px;
}


/* Upload Box */

.upload-box {

    display: block;

    width: 100%;

    min-height: 190px;

    border: 2px dashed #81c784;

    border-radius: 15px;

    padding: 30px 20px;

    background: #f8fff8;

    cursor: pointer;

    transition: 0.3s;

    text-align: center;
}


.upload-box:hover {

    background: #f1f8f1;

    border-color: #43a047;

    transform: translateY(-2px);
}


.upload-icon {

    font-size: 45px;

    margin-bottom: 15px;
}


.upload-text {

    color: #2e7d32;

    font-weight: bold;

    font-size: 17px;

    margin-bottom: 8px;
}


.upload-info {

    color: #777;

    font-size: 13px;

    line-height: 1.5;
}


input[type="file"] {

    display: none;
}


/* Preview */

#preview {

    display: none;

    width: 100%;

    max-height: 280px;

    object-fit: contain;

    margin-top: 20px;

    border-radius: 12px;

    border: 1px solid #ddd;

    padding: 5px;

    background: #fafafa;
}


.file-name {

    margin-top: 10px;

    color: #555;

    font-size: 14px;

    word-break: break-word;
}


/* Identify button */

.identify-btn {

    width: 100%;

    border: none;

    margin-top: 22px;

    padding: 15px;

    border-radius: 11px;

    background: #43a047;

    color: white;

    font-size: 17px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;
}


.identify-btn:hover {

    background: #2e7d32;
}


.identify-btn:disabled {

    background: #9e9e9e;

    cursor: not-allowed;
}


/* Loading */

#loading {

    display: none;

    margin-top: 20px;

    color: #2e7d32;

    font-weight: bold;

    font-size: 15px;
}


.spinner {

    display: inline-block;

    width: 18px;

    height: 18px;

    border: 3px solid #c8e6c9;

    border-top: 3px solid #2e7d32;

    border-radius: 50%;

    animation: spin 1s linear infinite;

    vertical-align: middle;

    margin-right: 8px;
}


@keyframes spin {

    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }

}


/* Result */

.message {

    margin-top: 20px;

    padding: 15px;

    border-radius: 10px;

    line-height: 1.6;

    font-size: 15px;
}


.error {

    background: #ffebee;

    color: #c62828;
}


.warning {

    background: #fff8e1;

    color: #8d6e00;
}


.success {

    background: #e8f5e9;

    color: #2e7d32;
}


/* Matching Trees */

.results-title {

    margin-top: 30px;

    margin-bottom: 15px;

    color: #2e7d32;

    font-size: 22px;
}


.tree-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;

    text-align: left;
}


.tree-card {

    background: #f8fff8;

    border: 1px solid #c8e6c9;

    border-radius: 15px;

    overflow: hidden;

    box-shadow:
        0 4px 12px rgba(0,0,0,0.08);
}


.tree-card-image {

    width: 100%;

    height: 200px;

    object-fit: cover;

    display: block;

    background: #eeeeee;
}


.tree-card-content {

    padding: 18px;
}


.tree-card-content h3 {

    color: #2e7d32;

    margin-bottom: 7px;

    font-size: 20px;
}


.scientific {

    color: #666;

    font-style: italic;

    margin-bottom: 10px;
}


.tree-info {

    color: #555;

    line-height: 1.6;

    font-size: 14px;

    margin-bottom: 5px;
}


.view-btn {

    display: inline-block;

    margin-top: 12px;

    padding: 10px 16px;

    background: #43a047;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;

    font-size: 14px;
}


.view-btn:hover {

    background: #2e7d32;
}


/* Back button */

.back-btn {

    display: inline-block;

    margin-top: 25px;

    padding: 11px 23px;

    background: #757575;

    color: white;

    text-decoration: none;

    border-radius: 10px;

    font-weight: bold;

    transition: 0.3s;
}


.back-btn:hover {

    background: #555;
}


/* Responsive */

@media (max-width: 700px) {

    body {
        padding: 15px;
    }

    .container {
        padding: 22px;
    }

    .tree-grid {
        grid-template-columns: 1fr;
    }

}

</style>

</head>


<body>


<div class="container">


    <div class="icon">
        🖼️
    </div>


    <h1>
        Identify Campus Plant
    </h1>


    <p class="subtitle">
        Upload a plant image to identify its species
    </p>


    <form
        method="POST"
        enctype="multipart/form-data"
        id="uploadForm"
    >


        <label
            class="upload-box"
            for="plant_image"
        >

            <div class="upload-icon">
                📷
            </div>


            <div class="upload-text">
                Choose Plant Image
            </div>


            <div class="upload-info">
                Click here to select an image
                <br>
                JPG, PNG or WEBP • Maximum 5 MB
            </div>

        </label>


        <input
            type="file"
            id="plant_image"
            name="plant_image"
            accept="image/jpeg,image/png,image/webp"
            required
        >


        <img
            id="preview"
            alt="Plant Image Preview"
        >


        <div
            id="fileName"
            class="file-name"
        ></div>


        <button
            type="submit"
            class="identify-btn"
            id="identifyBtn"
            disabled
        >

            🔍 Identify Plant

        </button>


        <div id="loading">

            <span class="spinner"></span>

            Analyzing image... Please wait.

        </div>


    </form>


    <?php if ($resultMessage !== "") { ?>

        <div class="message <?php echo $resultType; ?>">

            <?php
                echo $resultMessage;
            ?>

        </div>

    <?php } ?>


    <?php if (count($matchingTrees) > 0) { ?>

        <div class="results-title">

            🌳 Matching Campus Trees

        </div>


        <div class="tree-grid">

            <?php foreach ($matchingTrees as $tree) { ?>

                <div class="tree-card">


                    <?php if (!empty($tree["image"])) { ?>

                        <img
                            src="<?php echo htmlspecialchars($tree["image"]); ?>"
                            alt="<?php echo htmlspecialchars($tree["plant_name"]); ?>"
                            class="tree-card-image"
                        >

                    <?php } else { ?>

                        <div
                            class="tree-card-image"
                            style="
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                font-size:55px;
                            "
                        >
                            🌳
                        </div>

                    <?php } ?>


                    <div class="tree-card-content">


                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $tree["plant_name"]
                            );
                            ?>

                        </h3>


                        <div class="scientific">

                            <?php

                            if (
                                !empty(
                                    $tree["scientific_name"]
                                )
                            ) {

                                echo htmlspecialchars(
                                    $tree["scientific_name"]
                                );

                            } else {

                                echo "Scientific name not available";

                            }

                            ?>

                        </div>


                        <div class="tree-info">

                            <strong>Tree Code:</strong>

                            <?php
                            echo htmlspecialchars(
                                $tree["tree_code"]
                            );
                            ?>

                        </div>


                        <div class="tree-info">

                            <strong>Location:</strong>

                            <?php
                            echo htmlspecialchars(
                                $tree["location"]
                            );
                            ?>

                        </div>


                        <div class="tree-info">

                            <strong>Type:</strong>

                            <?php
                            echo htmlspecialchars(
                                $tree["tree_type"]
                            );
                            ?>

                        </div>


                        <a
                            href="campus_tree.php?id=<?php echo intval($tree["id"]); ?>"
                            class="view-btn"
                        >

                            View Tree Details →

                        </a>


                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } ?>


    <a
        href="user_dahsboard.php"
        class="back-btn"
    >

        ← Back

    </a>


</div>


<script>

var imageInput =
    document.getElementById("plant_image");

var preview =
    document.getElementById("preview");

var fileName =
    document.getElementById("fileName");

var identifyBtn =
    document.getElementById("identifyBtn");

var uploadForm =
    document.getElementById("uploadForm");

var loading =
    document.getElementById("loading");


/* Image selection */

imageInput.addEventListener(
    "change",
    function () {

        var file = this.files[0];


        if (!file) {

            preview.style.display = "none";

            preview.src = "";

            fileName.innerHTML = "";

            identifyBtn.disabled = true;

            return;
        }


        fileName.innerHTML =
            "Selected: <strong>" +
            file.name +
            "</strong>";


        var reader =
            new FileReader();


        reader.onload =
            function (event) {

                preview.src =
                    event.target.result;

                preview.style.display =
                    "block";

            };


        reader.readAsDataURL(file);


        identifyBtn.disabled = false;

    }
);


/* Form submit */

uploadForm.addEventListener(
    "submit",
    function () {

        identifyBtn.disabled = true;

        identifyBtn.innerHTML =
            "🔍 Identifying...";

        loading.style.display =
            "block";

    }
);

</script>


</body>

</html>