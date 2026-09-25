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

/* Get tree details */

$sql = "SELECT * FROM campus_trees WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Campus tree not found.");
}

$tree = mysqli_fetch_assoc($result);


/*
    QR will open this tree's public details page.
    
    For localhost testing:
    http://localhost/plant_qr/campus_tree.php?id=...
*/

$link = "https://plantqr-production.up.railway.app/campus_tree.php?id=" . $id;

$qr_url =
    "https://api.qrserver.com/v1/create-qr-code/" .
    "?size=500x500&data=" .
    urlencode($link);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    QR - <?php echo htmlspecialchars($tree['plant_name']); ?>
</title>

<style>

* {
    box-sizing: border-box;
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

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 20px;
}

.container {

    width: 500px;

    max-width: 100%;

    background: white;

    padding: 30px;

    border-radius: 18px;

    text-align: center;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.15);
}

.icon {

    font-size: 50px;

    margin-bottom: 5px;
}

h1 {

    margin: 5px 0;

    color: #2e7d32;

    font-size: 28px;
}

.scientific {

    color: #666;

    font-style: italic;

    margin-bottom: 8px;
}

.tree-code {

    color: #777;

    margin-bottom: 20px;
}

.qr-box {

    background: #f5f5f5;

    padding: 20px;

    border-radius: 15px;

    display: inline-block;

    margin-bottom: 20px;
}

.qr-box img {

    width: 350px;

    max-width: 100%;

    height: auto;

    display: block;
}

.info {

    background: #f1f8f2;

    padding: 12px;

    border-radius: 10px;

    margin-bottom: 20px;

    color: #555;

    line-height: 1.5;
}

.download-button {

    display: inline-block;

    background: #2e7d32;

    color: white;

    text-decoration: none;

    padding: 13px 20px;

    border-radius: 8px;

    font-weight: bold;

    margin: 5px;
}

.download-button:hover {

    background: #1b5e20;
}

.back-button {

    display: inline-block;

    background: #757575;

    color: white;

    text-decoration: none;

    padding: 13px 20px;

    border-radius: 8px;

    font-weight: bold;

    margin: 5px;
}

.back-button:hover {

    background: #424242;
}

.note {

    margin-top: 20px;

    font-size: 13px;

    color: #777;

    line-height: 1.5;
}

@media (max-width: 600px) {

    .container {

        padding: 20px;
    }

    h1 {

        font-size: 24px;
    }

    .qr-box img {

        width: 280px;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="icon">
        📱
    </div>

    <h1>
        <?php
        echo htmlspecialchars($tree['plant_name']);
        ?>
    </h1>

    <div class="scientific">

        <?php
        echo htmlspecialchars(
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


    <div class="qr-box">

        <img
            src="<?php echo htmlspecialchars($qr_url); ?>"
            alt="QR Code"
        >

    </div>


    <div class="info">

        📍

        <?php
        echo htmlspecialchars(
            $tree['location']
        );
        ?>

        <br>

        Scan this QR code to view the
        complete tree information.

    </div>


    <!-- DOWNLOAD -->

    <a
        href="<?php echo htmlspecialchars($qr_url); ?>"
        download="<?php
        echo htmlspecialchars(
            $tree['tree_code']
        );
        ?>_QR.png"
        class="download-button"
    >
        ⬇️ Download QR
    </a>


    <!-- BACK -->

    <a
        href="index.php"
        class="back-button"
    >
        ⬅ Back to Dashboard
    </a>


    <div class="note">

        Each campus tree has a unique Tree Code
        and QR code.

    </div>

</div>

</body>

</html>