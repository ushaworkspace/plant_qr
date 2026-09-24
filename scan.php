<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Scan Campus Tree QR Code</title>

    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f5e9, #f1f8e9);

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;

            background: white;

            padding: 30px;

            border-radius: 20px;

            text-align: center;

            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .icon {
            font-size: 50px;
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

        #reader {
            width: 100%;
            max-width: 400px;
            margin: auto;
        }

        .message {
            margin-top: 15px;
            color: #2e7d32;
            font-weight: bold;
            display: none;
        }

        .back-btn {
            display: inline-block;

            margin-top: 25px;

            padding: 12px 25px;

            background: #43a047;

            color: white;

            text-decoration: none;

            border-radius: 10px;

            font-weight: bold;
        }

        .back-btn:hover {
            background: #2e7d32;
        }

        .error-message {
            color: #c62828;
            padding: 20px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="icon">📷</div>

    <h1>Scan Campus Tree QR Code</h1>

    <p class="subtitle">
        Place the QR code in front of your camera
    </p>

    <!-- Camera Scanner -->
    <div id="reader"></div>

    <p id="message" class="message">
        QR Code detected! Opening tree details...
    </p>

    <a href="user_dahsboard.php" class="back-btn">
        ← Back
    </a>

</div>

<script>

let scannerStarted = false;

function startScanner() {

    if (scannerStarted) {
        return;
    }

    scannerStarted = true;

    const scanner = new Html5Qrcode("reader");

    const config = {
        fps: 10,

        qrbox: {
            width: 250,
            height: 250
        }
    };

    scanner.start(
        {
            facingMode: "environment"
        },

        config,

        function(decodedText) {

            document.getElementById("message").style.display = "block";

            scanner.stop().then(function() {

                /*
                 * Campus QR contains:
                 *
                 * http://localhost/plant_qr/campus_tree.php?id=1
                 *
                 * Open the exact campus tree details page.
                 */

                window.location.href = decodedText;

            }).catch(function(error) {

                window.location.href = decodedText;

            });

        },

        function(errorMessage) {

            // Ignore continuous scanning errors

        }

    ).catch(function(error) {

        console.log("Camera error:", error);

        scannerStarted = false;

        document.getElementById("reader").innerHTML =
            "<p class='error-message'>" +
            "Camera could not be started.<br>" +
            "Please allow camera permission." +
            "</p>";

    });

}


/* Start camera automatically */
window.onload = function() {

    startScanner();

};

</script>

</body>
</html>