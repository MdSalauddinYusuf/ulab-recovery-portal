<?php
include 'db.php';
session_start();

if ($_SESSION['user_role'] != 'admin') die("Access Denied");

// Add Category
if (isset($_POST['add_cat'])) {
    $name = mysqli_real_escape_string($conn, $_POST['new_cat']);
    mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$name')");
    header("Location: admin_dashboard.php");
}

// Delete Category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    header("Location: admin_dashboard.php");
}
?>