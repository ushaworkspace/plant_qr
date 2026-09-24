<?php

require_once __DIR__ . "/db.php";

if (!isset($conn) || !$conn) {
    die("Database connection failed. Please check db.php");
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Plant ID not provided.");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM plants WHERE id = $id LIMIT 1";

$res = mysqli_query($conn, $sql);

if (!$res) {
    die("Database query failed: " . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($res);

if (!$row) {
    die("Plant details not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
<?php echo htmlspecialchars($row['name']); ?> - Plant Details
</title>

<style>

*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#e8f5e9;
    top:0 !important;
}

/* Header */

.header{
    background:#2e7d32;
    color:white;
    text-align:center;
    padding:22px;
}

.header h1{
    margin:0;
    font-size:30px;
}

/* Main Container */

.container{
    width:90%;
    max-width:950px;
    margin:35px auto;
    background:white;
    padding:30px;
    border-radius:15px;
    box-shadow:0 4px 15px rgba(0,0,0,0.15);
}

/* Language Dropdown */

.translate-box{
    text-align:right;
    margin-bottom:20px;
}

.language-control{
    display:inline-flex;
    align-items:center;
    gap:8px;
}

.language-control label{
    font-size:15px;
    font-weight:bold;
    color:#1b5e20;
}

#languageSelect{
    padding:10px 35px 10px 12px;
    border:1px solid #2e7d32;
    border-radius:7px;
    background:white;
    color:#1b5e20;
    font-size:15px;
    cursor:pointer;
    outline:none;
}

#languageSelect:hover{
    border-color:#1565c0;
}

#languageSelect:focus{
    border-color:#1565c0;
}


/* Plant Image */

.plant-image{
    display:block;
    margin:0 auto 25px auto;
    width:320px;
    height:320px;
    object-fit:cover;
    border-radius:12px;
    border:3px solid #2e7d32;
}


/* Table */

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

table td{
    border:1px solid #ddd;
    padding:14px;
    vertical-align:top;
    line-height:1.5;
}

table td:first-child{
    width:30%;
    font-weight:bold;
    background:#f1f8e9;
    color:#1b5e20;
}


/* Back Button */

.back{
    display:inline-block;
    margin-top:25px;
    padding:12px 25px;
    background:#2e7d32;
    color:white;
    text-decoration:none;
    border-radius:7px;
}

.back:hover{
    background:#1b5e20;
}


/* ================================= */
/* GOOGLE TRANSLATE BAR COMPLETELY HIDE */
/* ================================= */

.goog-te-banner-frame,
.goog-te-banner-frame.skiptranslate,
iframe.goog-te-banner-frame{
    display:none !important;
    visibility:hidden !important;
    height:0 !important;
    width:0 !important;
}

.goog-te-gadget{
    font-size:0 !important;
}

.goog-te-gadget-icon{
    display:none !important;
}

.goog-logo-link{
    display:none !important;
}

.goog-te-gadget span{
    display:none !important;
}

.goog-tooltip{
    display:none !important;
}

.goog-text-highlight{
    background:none !important;
    box-shadow:none !important;
    border:none !important;
}


/* Hide Google Translate top container */

body > .skiptranslate{
    display:none !important;
}

html{
    margin-top:0 !important;
}


/* Mobile */

@media(max-width:600px){

    .container{
        width:94%;
        padding:18px;
    }

    .header h1{
        font-size:24px;
    }

    .plant-image{
        width:100%;
        max-width:320px;
        height:auto;
        aspect-ratio:1 / 1;
    }

    .translate-box{
        text-align:center;
    }

    .language-control{
        justify-content:center;
    }

    table td{
        padding:10px;
        font-size:14px;
    }

    table td:first-child{
        width:35%;
    }

}

</style>

</head>

<body>


<!-- Header -->

<div class="header">

<h1>
🌳 <?php echo htmlspecialchars($row['name']); ?>
</h1>

</div>


<div class="container">


<!-- ================================= -->
<!-- LANGUAGE DROPDOWN -->
<!-- NOT TRANSLATED BY GOOGLE -->
<!-- ================================= -->

<div class="translate-box notranslate" translate="no">

    <div class="language-control">

        <label for="languageSelect">
            🌐 Language:
        </label>

        <select
            id="languageSelect"
            onchange="changeLanguage(this.value)"
            class="notranslate"
            translate="no">

            <option value="en">English</option>

            <option value="ta">Tamil</option>

        </select>

    </div>

</div>


<!-- Plant Image -->

<?php if (!empty($row['image'])) { ?>

<img
    src="images/<?php echo htmlspecialchars($row['image']); ?>"
    alt="<?php echo htmlspecialchars($row['name']); ?>"
    class="plant-image"
>

<?php } ?>


<!-- Plant Details -->

<table>

<tr>

<td>
Plant Name
</td>

<td>
<?php echo htmlspecialchars($row['name']); ?>
</td>

</tr>


<tr>

<td>
Scientific Name
</td>

<td>
<?php echo htmlspecialchars($row['scientific_name']); ?>
</td>

</tr>


<tr>

<td>
Family
</td>

<td>
<?php echo htmlspecialchars($row['family']); ?>
</td>

</tr>


<tr>

<td>
Type
</td>

<td>
<?php echo htmlspecialchars($row['type']); ?>
</td>

</tr>


<tr>

<td>
Description
</td>

<td>
<?php echo nl2br(htmlspecialchars($row['description'])); ?>
</td>

</tr>


<tr>

<td>
Medicinal Uses
</td>

<td>
<?php echo nl2br(htmlspecialchars($row['medicinal'])); ?>
</td>

</tr>


<tr>

<td>
Water Requirement
</td>

<td>
<?php echo htmlspecialchars($row['water']); ?>
</td>

</tr>


<tr>

<td>
Sunlight Requirement
</td>

<td>
<?php echo htmlspecialchars($row['sunlight']); ?>
</td>

</tr>


<tr>

<td>
Soil
</td>

<td>
<?php echo htmlspecialchars($row['soil']); ?>
</td>

</tr>


<tr>

<td>
Temperature
</td>

<td>
<?php echo htmlspecialchars($row['temperature']); ?>
</td>

</tr>


<tr>

<td>
Fertilizer
</td>

<td>
<?php echo htmlspecialchars($row['fertilizer']); ?>
</td>

</tr>


<tr>

<td>
Care Tips
</td>

<td>
<?php echo nl2br(htmlspecialchars($row['care'])); ?>
</td>

</tr>


<tr>

<td>
Diseases
</td>

<td>
<?php echo nl2br(htmlspecialchars($row['diseases'])); ?>
</td>

</tr>


<?php if (isset($row['funfact'])) { ?>

<tr>

<td>
Fun Fact
</td>

<td>
<?php echo nl2br(htmlspecialchars($row['funfact'])); ?>
</td>

</tr>

<?php } ?>

</table>


<!-- Back -->

<a href="index.php" class="back">
⬅ Back to Home
</a>


</div>


<!-- ================================= -->
<!-- HIDDEN GOOGLE TRANSLATE -->
<!-- ================================= -->

<div
    id="google_translate_element"
    style="
        position:absolute;
        left:-9999px;
        top:-9999px;
        width:1px;
        height:1px;
        overflow:hidden;
    ">
</div>


<script>

/* ================================= */
/* CHANGE LANGUAGE */
/* ================================= */

function changeLanguage(language){

    const googleSelect =
        document.querySelector(".goog-te-combo");

    if(!googleSelect){

        setTimeout(function(){

            changeLanguage(language);

        },500);

        return;
    }


    googleSelect.value = language;

    googleSelect.dispatchEvent(
        new Event("change")
    );

}


/* ================================= */
/* GOOGLE TRANSLATE INITIALIZATION */
/* ================================= */

function googleTranslateElementInit(){

    new google.translate.TranslateElement({

        pageLanguage:"en",

        includedLanguages:"en,ta",

        autoDisplay:false

    },"google_translate_element");

}


/* ================================= */
/* KEEP GOOGLE BAR HIDDEN */
/* ================================= */

function hideGoogleBar(){

    const elements = document.querySelectorAll(
        ".goog-te-banner-frame, iframe.goog-te-banner-frame"
    );

    elements.forEach(function(element){

        element.style.display = "none";
        element.style.visibility = "hidden";
        element.style.height = "0";
        element.style.width = "0";

    });


    document.body.style.top = "0px";

}


/* Check repeatedly */

setInterval(function(){

    hideGoogleBar();

},300);


</script>


<!-- Google Translate -->

<script
src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
</script>


</body>

</html>