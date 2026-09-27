<?php

if (getenv('MYSQLHOST')) {

    // Railway
    $host = getenv('MYSQLHOST');
    $port = getenv('MYSQLPORT') ?: 3306;
    $user = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');
    $database = getenv('MYSQLDATABASE');

} else {

    // Local XAMPP
    $host = "localhost";
    $port = 3306;
    $user = "root";
    $password = "";
    $database = "plant_db";

}


$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database,
    (int)$port
);


if (!$conn) {

    die(
        "Database connection failed: " .
        mysqli_connect_error()
    );

}


mysqli_set_charset(
    $conn,
    "utf8mb4"
);

?>