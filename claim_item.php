<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$item_id = $_GET['id'];
if (isset($_POST['submit_claim'])) {
    $message = mysqli_real_escape_string($conn, $_POST['claim_message']);
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO claims (item_id, claimant_id, message) VALUES ('$item_id', '$user_id', '$message')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Claim request sent successfully!'); window.location='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Claim Item</title></head>
<body style="font-family: sans-serif; display: flex; justify-content: center; padding: 50px;">
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 400px;">
        <h2>Claim Item</h2>
        <form method="POST">
            <label>Why is this item yours? (Provide proof/details):</label><br>
            <textarea name="claim_message" required style="width: 100%; height: 100px; margin-top: 10px; border-radius: 8px; padding: 10px;"></textarea><br>
            <button type="submit" name="submit_claim" style="background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin-top: 15px;">Send Request</button>
        </form>
    </div>
</body>
</html>