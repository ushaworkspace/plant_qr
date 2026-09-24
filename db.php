<?php

$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$user = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');
$database = getenv('MYSQLDATABASE');

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database,
    (int)$port
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>