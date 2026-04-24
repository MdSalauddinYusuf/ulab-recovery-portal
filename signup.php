<?php
include 'db.php';
$message = "";

if (isset($_POST['signup'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Try-Catch block to handle Duplicate Entry error 
    try {
        $sql = "INSERT INTO users (student_id, full_name, email, password) VALUES ('$student_id', '$full_name', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert success'>Account created! <a href='login.php'>Login here</a></div>";
        } else {
            throw new Exception(mysqli_error($conn));
        }
    } catch (Exception $e) {
        // Checking if the error is due to duplicate Student ID or Email 
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $message = "<div class='alert error'>Error: Student ID or Email already exists!</div>";
        } else {
            $message = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup - ULAB Recovery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .password-wrapper { position: relative; }
        .btn-auth { width: 100%; background: #007bff; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 10px; text-align: center; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        #toggleSignupPass { position: absolute; right: 15px; top: 25px; cursor: pointer; color: #666; }
    </style>
</head>
<body>
<div class="auth-container">
    <h2 style="text-align:center; color:#1a2a6c;">Create Account</h2>
    <?php echo $message; ?>
    <form method="POST">
        <input type="number" name="student_id" placeholder="Student ID" required>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="ULAB Email" required>
        <div class="password-wrapper">
            <input type="password" name="password" id="signupPass" placeholder="Password" required>
            <i class="fa-solid fa-eye" id="toggleSignupPass"></i>
        </div>
        <button type="submit" name="signup" class="btn-auth">Sign Up</button>
    </form>
    <p style="text-align:center;">Already have an account? <a href="login.php">Login</a></p>
</div>

<script>
    document.getElementById('toggleSignupPass').addEventListener('click', function() {
        const passInput = document.getElementById('signupPass');
        const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passInput.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>