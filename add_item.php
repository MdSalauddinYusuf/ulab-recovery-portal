<?php
include 'db.php';
include 'email_helper.php'; // Include PHPMailer helper
session_start();

// Security: Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Fetch existing categories for the dropdown
$cat_query = "SELECT * FROM categories WHERE category_name != 'Others' ORDER BY category_name ASC";
$categories_result = mysqli_query($conn, $cat_query);

if (isset($_POST['submit'])) {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $status = $_POST['status'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $student_id = $_SESSION['user_id'];
    $reporter_name = $_SESSION['user_name'];

    // Category Logic: Handle "Others" or existing selection
    if ($_POST['category'] == 'Others' && !empty($_POST['custom_category'])) {
        $category = mysqli_real_escape_string($conn, $_POST['custom_category']);
        // Auto-add new category to the global list for future users
        mysqli_query($conn, "INSERT IGNORE INTO categories (category_name) VALUES ('$category')");
    } else {
        $category = mysqli_real_escape_string($conn, $_POST['category']);
    }

    // Image Upload Handling
    $image_path = NULL;
    if (!empty($_FILES['item_image']['name'])) {
        $target_dir = "uploads/";
        $file_name = time() . "_" . basename($_FILES["item_image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Insert item into database
    $sql = "INSERT INTO items (item_name, category, location, status, description, reported_by, image_path) 
            VALUES ('$item_name', '$category', '$location', '$status', '$description', '$student_id', '$image_path')";

    if (mysqli_query($conn, $sql)) {
        // --- Email Notification to Admin [Task 4] ---
        $admin_email = "your-admin-email@gmail.com"; // Replace with your admin email
        $subject = "New Item Report: $item_name";
        $email_body = "<h3>New Item Reported on Campus</h3>
                       <p><strong>Item:</strong> $item_name</p>
                       <p><strong>Category:</strong> $category</p>
                       <p><strong>Location:</strong> $location</p>
                       <p><strong>Reported By:</strong> $reporter_name (ID: $student_id)</p>
                       <p>Please check the admin dashboard to verify this report.</p>";
        
        sendNotification($admin_email, $subject, $email_body);
        
        $message = "<div class='alert success'>Item reported successfully! Notification sent to Admin. <a href='index.php'>View List</a></div>";
    } else {
        $message = "<div class='alert error'>Error: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Item - ULAB Recovery</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding: 40px; }
        .form-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-submit { background: #007bff; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        #custom_cat_div { display: none; margin-top: 10px; }
    </style>
</head>
<body>
<div class="form-card">
    <h2 style="text-align:center; color: #1a2a6c;">Post New Report</h2>
    <p style="text-align:center; font-size: 13px; color: #666;">Reporting as: <strong><?php echo $_SESSION['user_name']; ?></strong></p>
    
    <?php echo $message; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Item Name</label>
            <input type="text" name="item_name" placeholder="e.g. Wallet, Key, Phone" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category" id="category_select" onchange="checkCategory(this.value)">
                <?php while($row = mysqli_fetch_assoc($categories_result)) { ?>
                    <option value="<?php echo $row['category_name']; ?>"><?php echo $row['category_name']; ?></option>
                <?php } ?>
                <option value="Others" style="font-weight:bold; color:blue;">+ Add New Category</option>
            </select>
            <div id="custom_cat_div">
                <input type="text" name="custom_category" placeholder="Enter new category name">
            </div>
        </div>
        <div class="form-group">
            <label>Location on Campus</label>
            <input type="text" name="location" placeholder="e.g. Cafeteria, Lab 402" required>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="Lost">Lost (You lost it)</option>
                <option value="Found">Found (You found it)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Detailed Description</label>
            <textarea name="description" placeholder="Color, brand, or any special markings..."></textarea>
        </div>
        <div class="form-group">
            <label>Upload Photo (Optional)</label>
            <input type="file" name="item_image">
        </div>
        <button type="submit" name="submit" class="btn-submit">Submit Report</button>
    </form>
    <a href="index.php" style="display:block; text-align:center; margin-top:15px; text-decoration:none; color:#666; font-size: 13px;">← Cancel and Go Back</a>
</div>

<script>
function checkCategory(val) {
    var element = document.getElementById('custom_cat_div');
    element.style.display = (val === 'Others') ? 'block' : 'none';
}
</script>
</body>
</html>