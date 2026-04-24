<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = "";

// অ্যাডমিনের বর্তমান ডাটা নিয়ে আসা
$query = mysqli_query($conn, "SELECT * FROM users WHERE student_id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($query);

if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_pass = $_POST['password'];

    if (!empty($new_pass)) {
        // পাসওয়ার্ড আপডেট করলে সেটি হ্যাশ করে নেওয়া (Security Best Practice)
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET full_name='$full_name', email='$email', password='$hashed_pass' WHERE student_id='$admin_id'";
    } else {
        $sql = "UPDATE users SET full_name='$full_name', email='$email' WHERE student_id='$admin_id'";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $full_name; // সেশন নাম আপডেট
        $message = "<div class='alert success'>Profile updated successfully!</div>";
        // ডাটা রিফ্রেশ করা
        $query = mysqli_query($conn, "SELECT * FROM users WHERE student_id = '$admin_id'");
        $admin_data = mysqli_fetch_assoc($query);
    } else {
        $message = "<div class='alert error'>Update failed: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile Settings</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 50px; display: flex; justify-content: center; }
        .profile-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 400px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-update { background: #1e3799; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
<div class="profile-card">
    <h2 style="text-align: center; color: #1e3799;">Admin Profile</h2>
    <?php echo $message; ?>
    <form method="POST">
        <label>Student ID (Locked)</label>
        <input type="text" value="<?php echo $admin_data['student_id']; ?>" disabled>
        
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($admin_data['full_name']); ?>" required>
        
        <label>Email Address</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
        
        <label>New Password (Leave blank to keep current)</label>
        <input type="password" name="password" placeholder="Enter new password">
        
        <button type="submit" name="update_profile" class="btn-update">Update Info</button>
    </form>
    <a href="admin_dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#666; text-decoration:none;">← Back to Dashboard</a>
</div>
</body>
</html>