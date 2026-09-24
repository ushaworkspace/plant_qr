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
$sql = "SELECT image FROM campus_trees WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Campus tree not found.");
}

$tree = mysqli_fetch_assoc($result);

/* Delete image */
if (!empty($tree['image']) && file_exists($tree['image'])) {
    unlink($tree['image']);
}

/* Delete tree */
$delete_sql = "DELETE FROM campus_trees WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_sql);

mysqli_stmt_bind_param($delete_stmt, "i", $id);

if (mysqli_stmt_execute($delete_stmt)) {
    header("Location: index.php?deleted=1");
    exit();
} else {
    die("Unable to delete campus tree.");
}
?>