<?php
include 'db.php';
session_start();
$message = "";
if (isset($_POST['login'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $password = $_POST['password'];
    // Fetch user details and role from database
    $sql = "SELECT * FROM users WHERE student_id = '$student_id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    if ($user && password_verify($password, $user['password'])) {
        // Storing user details in session for access control
        $_SESSION['user_id'] = $user['student_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role']; 
        header("Location: index.php");
        exit();
    } else {
        $message = "<div class='alert error'>Invalid Student ID or Password!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ULAB Recovery Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; flex-direction: column; }
        .auth-container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 480px; position: relative; }
        h2 { text-align:center; color:#1a2a6c; margin-bottom:25px; font-size: 26px; }    
        /* Demo Accounts Styling */
        .demo-box { background: #f8f9fa; padding: 18px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #e0e0e0; }
        .demo-title { margin: 0 0 10px 0; font-size: 14px; color: #1a2a6c; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .demo-grid { display: flex; gap: 15px; }
        .demo-item { flex: 1; font-size: 13px; padding: 8px; border-radius: 8px; border-left: 4px solid; }
        .admin-demo { border-left-color: #e67e22; background: #fff; }
        .user-demo { border-left-color: #27ae60; background: #fff; }
        code { color: #d63031; font-weight: bold; font-family: 'Courier New', Courier, monospace; }
        input { width: 100%; padding: 14px; margin: 10px 0; border: 1px solid #ddd; border-radius: 12px; box-sizing: border-box; font-size: 16px; outline: none; transition: 0.3s; }
        input:focus { border-color: #27ae60; box-shadow: 0 0 8px rgba(39, 174, 96, 0.2); }
        .password-wrapper { position: relative; }
        #toggleLoginPass { position: absolute; right: 15px; top: 25px; cursor: pointer; color: #666; font-size: 18px; }
        .btn-auth { width: 100%; background: #27ae60; color: white; border: none; padding: 15px; border-radius: 12px; cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 15px; transition: 0.3s; }
        .btn-auth:hover { background: #219150; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3); } 
        .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .footer-text { text-align:center; margin-top:20px; color: #666; font-size: 14px; }
        .footer-text a { text-decoration:none; color:#007bff; font-weight: 600; }
        /* Copyright Section Styling */
        .copyright { text-align: center; margin-top: 30px; color: #888; font-size: 13px; }
        .copyright a { color: #1a2a6c; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .copyright a:hover { color: #27ae60; text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-container">
    <h2>User Login</h2>

    <div class="demo-box">
        <p class="demo-title"><i class="fa-solid fa-flask"></i> Public Demo Access:</p>
        <div class="demo-grid">
            <div class="demo-item admin-demo">
                <strong>Admin (Sagor)</strong><br>
                ID: <code>12345</code><br>
                Pass: <code>ngav ugno cjhf tety</code>
            </div>
            <div class="demo-item user-demo">
                <strong>User (Yusuf)</strong><br>
                ID: <code>242014176</code><br>
                Pass: <code>rotr dxiv pcuc jnvo</code>
            </div>
        </div>
    </div>
    
    <?php echo $message; ?>

    <form method="POST">
        <label style="font-size: 14px; color: #555; font-weight: 600;">Student ID</label>
        <input type="number" name="student_id" placeholder="Enter Student ID" required>
        
        <label style="font-size: 14px; color: #555; font-weight: 600;">Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="loginPass" placeholder="Enter Password" required>
            <i class="fa-solid fa-eye" id="toggleLoginPass"></i>
        </div>

        <button type="submit" name="login" class="btn-auth">Login to Portal</button>
    </form>

    <div class="footer-text">
        New here? <a href="signup.php">Create an Account</a>
    </div>
</div>

<div class="copyright">
    &copy; <?php echo date("Y"); ?> All Rights Reserved. <br>
    Developed by <a href="https://www.facebook.com/salauddin.sagor.16" target="_blank">Md. Salauddin Yusuf</a>
</div>

<script>
    const togglePassword = document.querySelector('#toggleLoginPass');
    const passwordField = document.querySelector('#loginPass');

    // Listener to toggle password visibility
    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>