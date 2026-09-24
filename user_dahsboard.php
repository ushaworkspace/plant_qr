<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Campus Plant Information System</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #dff5e1, #a8ddb5);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 550px;
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        h1 {
            color: #176b35;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #555;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 30px;
        }

        .option {
            display: block;
            width: 100%;
            padding: 18px;
            margin: 16px 0;
            border-radius: 12px;
            text-decoration: none;
            font-size: 17px;
            font-weight: bold;
            transition: 0.3s;
        }

        .scan {
            background: #198754;
            color: white;
        }

        .scan:hover {
            background: #146c43;
            transform: translateY(-2px);
        }

        .upload {
            background: white;
            color: #198754;
            border: 2px solid #198754;
        }

        .upload:hover {
            background: #eaf7ee;
            transform: translateY(-2px);
        }

        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #777;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="icon">🌳</div>

    <h1>College Campus Plant Information System</h1>

    <p class="subtitle">
        Explore and identify plants available on the college campus.
    </p>

    <a href="scan.php" class="option scan">
        📷 Scan Campus Tree QR Code
    </a>

    <a href="upload.php" class="option upload">
        🖼️ Identify Plant from Image
    </a>

    <div class="footer">
        College Campus Plant Management System
    </div>

</div>

</body>
</html>