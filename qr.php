```php
<?php
include "db.php";

$id = $_GET['id'];

$res = mysqli_query($conn, "SELECT * FROM plants WHERE id='$id'");
$row = mysqli_fetch_assoc($res);

$link = "http://192.168.111.53/plant_qr/plant.php?id=" . $id;

$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($link);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>QR Code</title>

<style>
body {
    text-align: center;
    font-family: Arial;
    background: #e8f5e9;
    margin: 0;
    padding: 30px;
}

.container {
    background: white;
    max-width: 500px;
    margin: auto;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

h1 {
    color: #2e7d32;
}

.qr-box {
    display: inline-block;
    padding: 15px;
    background: white;
    border: 2px dashed #2e7d32;
    border-radius: 10px;
}

.qr-box img {
    width: 300px;
    height: 300px;
    display: block;
}

.button {
    display: inline-block;
    padding: 11px 20px;
    margin: 8px 5px;
    border: none;
    border-radius: 6px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    text-decoration: none;
}

.download {
    background: #2e7d32;
}

.download:hover {
    background: #1b5e20;
}

.print {
    background: #1565c0;
}

.print:hover {
    background: #0d47a1;
}

.back {
    color: #333;
    display: inline-block;
    margin-top: 15px;
    text-decoration: none;
}
</style>

</head>

<body>

<div class="container">

<h1>
📱 QR Code for <?php echo htmlspecialchars($row['name']); ?>
</h1>

<div class="qr-box">
    <img
        id="qrImage"
        src="<?php echo htmlspecialchars($qrUrl); ?>"
        alt="Plant QR Code">
</div>

<p>Scan this QR to open plant details</p>

<!-- Download Button -->
<a
    href="<?php echo htmlspecialchars($qrUrl); ?>"
    download="<?php echo htmlspecialchars($row['name']); ?>_QR.png"
    target="_blank"
    class="button download">
    ⬇️ Download QR
</a>

<!-- Print Button -->
<button
    onclick="window.print()"
    class="button print">
    🖨️ Print QR
</button>

<br>

<a href="index.php" class="back">
    ⬅ Back
</a>

</div>

</body>
</html>
```
