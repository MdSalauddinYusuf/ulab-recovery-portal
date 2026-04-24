<?php
include 'db.php';
session_start();

// Security: Only Admin can delete
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id'])) {
    $item_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Delete the item from database
    $sql = "DELETE FROM items WHERE id = '$item_id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Post removed successfully'); window.location='admin_dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>