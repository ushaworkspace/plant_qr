<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* Total Trees */
$total_sql = "SELECT COUNT(*) AS total FROM campus_trees";
$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_trees = $total_row['total'];

/* Plant-wise Count */
$count_sql = "SELECT plant_name, COUNT(*) AS total
              FROM campus_trees
              GROUP BY plant_name
              ORDER BY plant_name";

$count_result = mysqli_query($conn, $count_sql);

/* All Trees */
$tree_sql = "SELECT * FROM campus_trees ORDER BY id DESC";
$tree_result = mysqli_query($conn, $tree_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>College Campus Plant Information System</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f8f3;
    color: #333;
}

/* ================= HEADER ================= */

.header {
    background: linear-gradient(135deg, #1b5e20, #43a047);
    color: white;
    padding: 25px 30px;
}

.header-content {
    max-width: 1200px;
    margin: auto;

    display: flex;
    flex-direction: column;

    justify-content: center;
    align-items: center;

    gap: 7px;

    position: relative;
    min-height: 85px;
}

.header h1 {
    margin: 0;
    font-size: 30px;
    font-weight: bold;
    text-align: center;
}

.header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.95;
    text-align: center;
}

.logout {
    position: absolute;

    right: 0;
    top: 50%;

    transform: translateY(-50%);

    background: white;
    color: #c62828;

    text-decoration: none;

    padding: 10px 18px;

    border-radius: 8px;

    font-weight: bold;
}

.logout:hover {
    background: #ffebee;
}

/* ================= MAIN ================= */

.container {
    max-width: 1200px;
    margin: auto;
    padding: 25px 15px;
}

/* ================= ADD BUTTON ================= */

.actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
}

.add-button {
    background: #2e7d32;
    color: white;

    text-decoration: none;

    padding: 12px 18px;

    border-radius: 8px;

    font-weight: bold;
}

.add-button:hover {
    background: #1b5e20;
}

/* ================= TOTAL CARD ================= */

.total-card {
    background: white;

    border-radius: 15px;

    padding: 25px;

    text-align: center;

    box-shadow: 0 5px 18px rgba(0,0,0,0.08);

    margin-bottom: 25px;
}

.total-number {
    font-size: 42px;

    font-weight: bold;

    color: #2e7d32;
}

.total-title {
    font-size: 18px;

    color: #555;
}

/* ================= SECTION ================= */

.section-title {
    color: #2e7d32;

    margin-bottom: 15px;
}

/* ================= PLANT COUNTS ================= */

.plant-counts {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(180px, 1fr));

    gap: 15px;

    margin-bottom: 30px;
}

.count-card {
    background: white;

    border-radius: 13px;

    padding: 20px;

    text-align: center;

    box-shadow:
        0 5px 15px rgba(0,0,0,0.07);
}

.count-card h3 {
    margin: 0 0 10px;

    color: #388e3c;
}

.count-number {
    font-size: 28px;

    font-weight: bold;

    color: #333;
}

/* ================= TABLE ================= */

.table-box {
    background: white;

    border-radius: 15px;

    padding: 20px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.08);

    overflow-x: auto;
}

table {
    width: 100%;

    border-collapse: collapse;

    min-width: 1100px;
}

th {
    background: #2e7d32;

    color: white;

    padding: 13px;

    text-align: left;
}

td {
    padding: 12px;

    border-bottom: 1px solid #ddd;

    vertical-align: middle;
}

tr:hover {
    background: #f7fbf7;
}

/* ================= IMAGE ================= */

.tree-thumb {
    width: 70px;

    height: 70px;

    object-fit: cover;

    border-radius: 10px;
}

/* ================= BUTTONS ================= */

.view-button,
.edit-button,
.delete-button,
.qr-button {

    display: inline-block;

    text-decoration: none;

    padding: 8px 10px;

    border-radius: 6px;

    color: white;

    font-size: 13px;

    font-weight: bold;

    margin: 2px;
}

/* View */

.view-button {
    background: #1976d2;
}

.view-button:hover {
    background: #0d47a1;
}

/* Edit */

.edit-button {
    background: #f57c00;
}

.edit-button:hover {
    background: #e65100;
}

/* Delete */

.delete-button {
    background: #d32f2f;
}

.delete-button:hover {
    background: #b71c1c;
}

/* QR */

.qr-button {
    background: #6a1b9a;
}

.qr-button:hover {
    background: #4a148c;
}

/* ================= FOOTER ================= */

.footer {
    text-align: center;

    padding: 25px;

    color: #777;
}

/* ================= MOBILE ================= */

@media (max-width: 700px) {

    .header {
        padding: 20px 15px;
    }

    .header-content {

        padding-bottom: 50px;

        min-height: 120px;
    }

    .header h1 {
        font-size: 23px;
    }

    .header p {
        font-size: 14px;
    }

    .logout {

        top: auto;

        bottom: 0;

        right: 50%;

        transform: translateX(50%);
    }

    .actions {
        justify-content: stretch;
    }

    .add-button {
        width: 100%;

        text-align: center;
    }

}

</style>

</head>

<body>


<!-- ================= HEADER ================= -->

<div class="header">

    <div class="header-content">

        <h1>
            🌳 College Campus Plant Information System
        </h1>

        <p>
            Campus Tree Management Dashboard
        </p>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</div>


<!-- ================= MAIN ================= -->

<div class="container">


<!-- ADD TREE -->

<div class="actions">

    <a href="campus_add.php" class="add-button">
        ➕ Add Campus Tree
    </a>

</div>


<!-- TOTAL TREES -->

<div class="total-card">

    <div class="total-number">
        <?php echo $total_trees; ?>
    </div>

    <div class="total-title">
        🌳 Total Campus Trees
    </div>

</div>


<!-- PLANT DISTRIBUTION -->

<h2 class="section-title">
    🌿 Plant / Tree Distribution
</h2>

<div class="plant-counts">

<?php if (mysqli_num_rows($count_result) > 0): ?>

    <?php while ($count = mysqli_fetch_assoc($count_result)): ?>

        <div class="count-card">

            <h3>
                🌱 <?php
                echo htmlspecialchars($count['plant_name']);
                ?>
            </h3>

            <div class="count-number">
                <?php echo $count['total']; ?>
            </div>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="count-card">

        <h3>
            No Trees Added
        </h3>

    </div>

<?php endif; ?>

</div>


<!-- CAMPUS TREE LIST -->

<h2 class="section-title">
    🌳 Campus Tree List
</h2>


<div class="table-box">

<table>

<thead>

<tr>

    <th>ID</th>

    <th>Image</th>

    <th>Tree Code</th>

    <th>Plant Name</th>

    <th>Scientific Name</th>

    <th>Type</th>

    <th>Location</th>

    <th>Actions</th>

</tr>

</thead>


<tbody>

<?php if (mysqli_num_rows($tree_result) > 0): ?>

    <?php while ($tree = mysqli_fetch_assoc($tree_result)): ?>

        <tr>

            <!-- ID -->

            <td>
                <?php echo $tree['id']; ?>
            </td>


            <!-- IMAGE -->

            <td>

            <?php if (!empty($tree['image'])): ?>

                <img
                    src="<?php
                    echo htmlspecialchars($tree['image']);
                    ?>"
                    class="tree-thumb"
                    alt="Tree Image"
                >

            <?php else: ?>

                No Image

            <?php endif; ?>

            </td>


            <!-- TREE CODE -->

            <td>

                <strong>
                    <?php
                    echo htmlspecialchars($tree['tree_code']);
                    ?>
                </strong>

            </td>


            <!-- PLANT NAME -->

            <td>

                <strong>
                    <?php
                    echo htmlspecialchars($tree['plant_name']);
                    ?>
                </strong>

            </td>


            <!-- SCIENTIFIC NAME -->

            <td>

                <i>
                    <?php
                    echo htmlspecialchars(
                        $tree['scientific_name']
                    );
                    ?>
                </i>

            </td>


            <!-- TYPE -->

            <td>

                <?php
                echo htmlspecialchars($tree['tree_type']);
                ?>

            </td>


            <!-- LOCATION -->

            <td>

                📍
                <?php
                echo htmlspecialchars($tree['location']);
                ?>

            </td>


            <!-- ACTIONS -->

            <td>

                <!-- VIEW -->

                <a
                    href="campus_tree.php?id=<?php
                    echo $tree['id'];
                    ?>"
                    class="view-button"
                >
                    👁 View
                </a>


                <!-- EDIT -->

                <a
                    href="campus_edit.php?id=<?php
                    echo $tree['id'];
                    ?>"
                    class="edit-button"
                >
                    ✏️ Edit
                </a>


                <!-- DELETE -->

                <a
                    href="campus_delete.php?id=<?php
                    echo $tree['id'];
                    ?>"
                    class="delete-button"
                    onclick="return confirm(
                        'Are you sure you want to delete this campus tree?'
                    );"
                >
                    🗑 Delete
                </a>


                <!-- QR -->

                <a
                    href="campus_qr.php?id=<?php
                    echo $tree['id'];
                    ?>"
                    class="qr-button"
                >
                    📱 QR
                </a>

            </td>

        </tr>

    <?php endwhile; ?>

<?php else: ?>

    <tr>

        <td
            colspan="8"
            style="text-align:center; padding:25px;"
        >
            No campus trees found.
        </td>

    </tr>

<?php endif; ?>

</tbody>

</table>

</div>


</div>


<!-- FOOTER -->

<div class="footer">

    College Campus Plant Information System

</div>


</body>

</html>