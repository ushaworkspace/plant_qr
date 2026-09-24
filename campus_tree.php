<?php

include "db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid tree ID.");
}

$sql = "SELECT * FROM campus_trees WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Campus tree not found.");
}

$tree = mysqli_fetch_assoc($result);


/* Display helper */

function showValue($value)
{
    if (trim((string)$value) == "") {
        return "Not available";
    }

    return nl2br(htmlspecialchars($value));
}


/* Map coordinates */

$latitude = $tree['latitude'] ?? null;
$longitude = $tree['longitude'] ?? null;

$has_map =
    $latitude !== null &&
    $longitude !== null &&
    $latitude !== '' &&
    $longitude !== '' &&
    is_numeric($latitude) &&
    is_numeric($longitude);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
<?php echo htmlspecialchars($tree['plant_name']); ?> - Campus Tree
</title>


<!-- Leaflet CSS -->

<?php if ($has_map): ?>

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<?php endif; ?>


<!-- Google Translate -->

<script type="text/javascript">

function googleTranslateElementInit() {

    new google.translate.TranslateElement(
        {
            pageLanguage: 'en',
            includedLanguages: 'en,ta',
            autoDisplay: false
        },
        'google_translate_hidden'
    );

}

</script>


<script
    type="text/javascript"
    src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
</script>


<style>

/* ---------------------------------------
   General
---------------------------------------- */

* {
    box-sizing: border-box;
}

html {
    margin: 0;
    padding: 0;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background:
        linear-gradient(
            135deg,
            #e8f5e9,
            #c8e6c9
        );

    color: #333;

    padding: 25px 15px;
}


/* ---------------------------------------
   Hide Google Translation Bar
---------------------------------------- */

.goog-te-banner-frame.skiptranslate {

    display: none !important;
}

body {

    top: 0 !important;
}

.goog-te-balloon-frame {

    display: none !important;
}

#goog-gt-tt {

    display: none !important;
}

.goog-tooltip {

    display: none !important;
}

.goog-text-highlight {

    background: none !important;
    box-shadow: none !important;
}

#google_translate_hidden {

    display: none !important;
}


/* ---------------------------------------
   Main Container
---------------------------------------- */

.container {

    max-width: 1000px;

    margin: auto;

    position: relative;
}


/* ---------------------------------------
   Language Button
---------------------------------------- */

.language-box {

    position: absolute;

    top: 18px;

    right: 18px;

    z-index: 9999;
}

.language-select {

    border: 1px solid #81c784;

    background: white;

    color: #2e7d32;

    padding: 10px 14px;

    border-radius: 25px;

    font-size: 14px;

    font-weight: bold;

    cursor: pointer;

    outline: none;

    box-shadow:
        0 4px 12px rgba(0,0,0,0.10);

    transition: 0.3s;
}

.language-select:hover {

    border-color: #43a047;

    box-shadow:
        0 5px 15px rgba(0,0,0,0.15);
}


/* ---------------------------------------
   Header
---------------------------------------- */

.header {

    background: white;

    border-radius: 18px;

    padding: 30px;

    text-align: center;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.12);

    margin-bottom: 20px;

    position: relative;
}

.header h1 {

    margin: 8px 0;

    color: #2e7d32;

    font-size: 34px;
}

.scientific {

    font-style: italic;

    color: #666;

    margin-bottom: 8px;
}

.tree-code {

    color: #777;

    font-size: 15px;
}


/* ---------------------------------------
   Tree Image
---------------------------------------- */

.tree-image-box {

    margin-top: 20px;

    background: #f1f8f2;

    border-radius: 15px;

    padding: 10px;
}

.tree-image {

    width: 100%;

    max-height: 430px;

    object-fit: contain;

    border-radius: 12px;

    display: block;
}


/* ---------------------------------------
   Details Grid
---------------------------------------- */

.details-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;
}


/* ---------------------------------------
   Cards
---------------------------------------- */

.card {

    background: white;

    border-radius: 14px;

    padding: 20px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.08);
}

.card.full {

    grid-column: 1 / -1;
}

.card h2 {

    margin: 0 0 10px;

    color: #2e7d32;

    font-size: 19px;
}

.card p {

    margin: 0;

    line-height: 1.65;

    color: #444;
}


/* ---------------------------------------
   Location
---------------------------------------- */

.location {

    border-left:
        5px solid #2e7d32;

    background: #f1f8f2;
}


/* ---------------------------------------
   Map
---------------------------------------- */

.map-card {

    background: white;

    border-radius: 14px;

    padding: 20px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.08);

    grid-column: 1 / -1;
}

.map-card h2 {

    margin: 0 0 15px;

    color: #2e7d32;

    font-size: 19px;
}

#map {

    width: 100%;

    height: 400px;

    border-radius: 12px;

    overflow: hidden;
}

.coordinates {

    margin-top: 10px;

    font-size: 13px;

    color: #777;

    text-align: center;
}


/* ---------------------------------------
   No Map
---------------------------------------- */

.no-map {

    background: #fff8e1;

    border-left: 5px solid #f9a825;

    color: #795548;

    padding: 15px;

    border-radius: 8px;

    font-size: 14px;

    line-height: 1.5;
}


/* ---------------------------------------
   Footer
---------------------------------------- */

.footer {

    text-align: center;

    margin-top: 25px;

    color: #666;

    font-size: 13px;
}


/* ---------------------------------------
   Mobile
---------------------------------------- */

@media (max-width: 700px) {

    body {

        padding: 15px 10px;

    }

    .header {

        padding: 70px 20px 20px;

    }

    .header h1 {

        font-size: 28px;

    }

    .details-grid {

        grid-template-columns: 1fr;

    }

    .card.full,
    .map-card {

        grid-column: auto;

    }

    .tree-image {

        max-height: 350px;

    }

    #map {

        height: 320px;

    }

    .language-box {

        top: 15px;

        right: 15px;

    }

}

</style>

</head>


<body>


<div class="container">


<!-- --------------------------------
     Language Selector
--------------------------------- -->

<div class="language-box">

<select
    id="languageSelect"
    class="language-select"
>

<option value="en">
    🌐 English
</option>

<option value="ta">
    🌐 தமிழ்
</option>

</select>

</div>


<!-- Hidden Google Translate -->

<div
    id="google_translate_hidden"
    style="display:none;"
></div>


<!-- --------------------------------
     Header
--------------------------------- -->

<div class="header">

<div style="font-size:50px;">
    🌳
</div>

<h1>

<?php

echo htmlspecialchars(
    $tree['plant_name']
);

?>

</h1>

<div class="scientific">

<?php

echo showValue(
    $tree['scientific_name']
);

?>

</div>

<div class="tree-code">

Tree Code:

<strong>

<?php

echo htmlspecialchars(
    $tree['tree_code']
);

?>

</strong>

</div>


<?php if (!empty($tree['image'])): ?>

<div class="tree-image-box">

<img
    src="<?php
    echo htmlspecialchars(
        $tree['image']
    );
    ?>"
    alt="<?php
    echo htmlspecialchars(
        $tree['plant_name']
    );
    ?>"
    class="tree-image"
>

</div>

<?php endif; ?>

</div>


<!-- --------------------------------
     Details
--------------------------------- -->

<div class="details-grid">


<div class="card">

<h2>
    👨‍👩‍👧 Family
</h2>

<p>

<?php

echo showValue(
    $tree['family']
);

?>

</p>

</div>


<div class="card">

<h2>
    🌱 Tree Type
</h2>

<p>

<?php

echo showValue(
    $tree['tree_type']
);

?>

</p>

</div>


<div class="card full location">

<h2>
    📍 Campus Location
</h2>

<p>

<?php

echo showValue(
    $tree['location']
);

?>

</p>

</div>


<!-- --------------------------------
     MAP
--------------------------------- -->

<div class="map-card">

<h2>
    🗺️ Tree Location on Map
</h2>


<?php if ($has_map): ?>

<div id="map"></div>

<div class="coordinates">

Latitude:
<strong>
<?php echo htmlspecialchars($latitude); ?>
</strong>

&nbsp; | &nbsp;

Longitude:
<strong>
<?php echo htmlspecialchars($longitude); ?>
</strong>

</div>

<?php else: ?>

<div class="no-map">

📍 Map location is not available for this tree.

Please add the latitude and longitude from the
<strong>Edit Campus Tree</strong> page.

</div>

<?php endif; ?>

</div>


<div class="card full">

<h2>
    📖 Description
</h2>

<p>

<?php

echo showValue(
    $tree['description']
);

?>

</p>

</div>


<div class="card full">

<h2>
    💊 Medicinal / Common Uses
</h2>

<p>

<?php

echo showValue(
    $tree['medicinal']
);

?>

</p>

</div>


<div class="card">

<h2>
    💧 Water Requirement
</h2>

<p>

<?php

echo showValue(
    $tree['water']
);

?>

</p>

</div>


<div class="card">

<h2>
    ☀️ Sunlight
</h2>

<p>

<?php

echo showValue(
    $tree['sunlight']
);

?>

</p>

</div>


<div class="card">

<h2>
    🌍 Soil
</h2>

<p>

<?php

echo showValue(
    $tree['soil']
);

?>

</p>

</div>


<div class="card">

<h2>
    🌡️ Temperature
</h2>

<p>

<?php

echo showValue(
    $tree['temperature']
);

?>

</p>

</div>


<div class="card full">

<h2>
    🌾 Fertilizer
</h2>

<p>

<?php

echo showValue(
    $tree['fertilizer']
);

?>

</p>

</div>


<div class="card full">

<h2>
    🪴 Care / Maintenance
</h2>

<p>

<?php

echo showValue(
    $tree['care']
);

?>

</p>

</div>


<div class="card full">

<h2>
    🦠 Common Diseases
</h2>

<p>

<?php

echo showValue(
    $tree['diseases']
);

?>

</p>

</div>


</div>


<div class="footer">

College Campus Plant Information System

</div>


</div>


<!-- --------------------------------
     Leaflet JS
--------------------------------- -->

<?php if ($has_map): ?>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

<script>

const latitude = <?php echo (float)$latitude; ?>;
const longitude = <?php echo (float)$longitude; ?>;

const map = L.map('map').setView(
    [latitude, longitude],
    18
);


/* OpenStreetMap */

L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }
).addTo(map);


/* Tree marker */

const marker = L.marker(
    [latitude, longitude]
).addTo(map);


/* Popup */

marker.bindPopup(
    "<strong>" +
    <?php echo json_encode($tree['plant_name']); ?> +
    "</strong><br>" +
    "Tree Code: " +
    <?php echo json_encode($tree['tree_code']); ?> +
    "<br>" +
    <?php echo json_encode($tree['location']); ?>
).openPopup();

</script>

<?php endif; ?>


<!-- --------------------------------
     Language JavaScript
--------------------------------- -->

<script>

document
    .getElementById("languageSelect")
    .addEventListener(
        "change",
        function () {

            var language = this.value;

            var googleSelect =
                document.querySelector(
                    ".goog-te-combo"
                );

            if (!googleSelect) {

                return;

            }

            googleSelect.value =
                language;

            googleSelect.dispatchEvent(
                new Event("change")
            );

        }
    );


/* Remove Google top bar */

function removeGoogleBar() {

    var banner =
        document.querySelector(
            ".goog-te-banner-frame"
        );

    if (banner) {

        banner.style.display = "none";

    }

    document.body.style.top = "0px";

}

setInterval(
    removeGoogleBar,
    500
);

</script>


</body>

</html>