<?php
include 'db.php';
session_start();

// ইউজার লগইন আছে কি না চেক করা
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// ইউজারের বর্তমান ডাটা নিয়ে আসা
$query = mysqli_query($conn, "SELECT * FROM users WHERE student_id = '$user_id'");
$user_data = mysqli_fetch_assoc($query);

if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_pass = $_POST['password'];

    // পাসওয়ার্ড ফিল্ড খালি থাকলে শুধু নাম ও ইমেইল আপডেট হবে
    if (!empty($new_pass)) {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET full_name='$full_name', email='$email', password='$hashed_pass' WHERE student_id='$user_id'";
    } else {
        $sql = "UPDATE users SET full_name='$full_name', email='$email' WHERE student_id='$user_id'";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $full_name; // সেশনে নাম আপডেট করা
        $message = "<div class='alert success'>Profile updated successfully!</div>";
        // আপডেট হওয়ার পর নতুন ডাটা লোড করা
        $query = mysqli_query($conn, "SELECT * FROM users WHERE student_id = '$user_id'");
        $user_data = mysqli_fetch_assoc($query);
    } else {
        $message = "<div class='alert error'>Update failed: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit My Profile - ULAB Recovery</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 40px; display: flex; justify-content: center; }
        .profile-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #1a2a6c; margin-bottom: 20px; }
        label { font-weight: bold; display: block; margin-top: 15px; font-size: 14px; }
        input { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-update { background: #27ae60; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 20px; font-size: 16px; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="profile-card">
    <h2>Edit Profile</h2>
    <?php echo $message; ?>
    <form method="POST">
        <label>Student ID (Permanent)</label>
        <input type="text" value="<?php echo $user_data['student_id']; ?>" disabled style="background:#eee;">
        
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
        
        <label>Email Address</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
        
        <label>Change Password (Leave blank to keep current)</label>
        <input type="password" name="password" placeholder="Enter new password">
        
        <button type="submit" name="update_profile" class="btn-update">Update Profile</button>
    </form>
    <a href="index.php" style="display:block; text-align:center; margin-top:15px; text-decoration:none; color:#666; font-size: 13px;">← Back to Home</a>
</div>
</body>
</html>