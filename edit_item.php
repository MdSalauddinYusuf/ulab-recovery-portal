<?php
include 'db.php';
session_start();

// Security: Administrative access check 
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    die("Unauthorized access.");
}

// Fetch existing data 
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $item_query = mysqli_query($conn, "SELECT * FROM items WHERE id = $id");
    $item = mysqli_fetch_assoc($item_query);
    
    // Fetch all available categories for the dropdown [cite: 15]
    $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");
}

// Update Logic 
if (isset($_POST['update'])) {
    $item_id = (int)$_POST['id']; 
    $name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $cat = mysqli_real_escape_string($conn, $_POST['category']); // Crucial: Captures selected category
    $loc = mysqli_real_escape_string($conn, $_POST['location']);
    $stat = $_POST['status'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "UPDATE items SET 
            item_name = '$name', 
            category = '$cat', 
            location = '$loc', 
            status = '$stat', 
            description = '$desc' 
            WHERE id = $item_id";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Record Updated Successfully!'); window.location='admin_dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item - ULAB Smart Campus Recovery</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding: 40px; }
        .edit-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); width: 400px; }
        label { font-size: 13px; font-weight: bold; color: #555; display: block; margin-top: 15px; }
        input, select, textarea { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-update { background: #1e3799; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="edit-card">
        <h2 style="text-align:center; color:#1e3799;">Update Item</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">

            <label>Item Description</label>
            <input type="text" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
            
            <label>Category</label>
            <select name="category" required>
                <option value="">-- Choose Category --</option>
                <?php 
                while($c = mysqli_fetch_assoc($categories)) { 
                    $selected = ($item['category'] == $c['category_name']) ? 'selected' : '';
                    echo "<option value='".htmlspecialchars($c['category_name'])."' $selected>".htmlspecialchars($c['category_name'])."</option>";
                } ?>
            </select>
            
            <label>Location</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($item['location']); ?>" required>
            
            <label>Status</label>
            <select name="status">
                <option value="Lost" <?php if($item['status'] == 'Lost') echo 'selected'; ?>>Lost</option>
                <option value="Found" <?php if($item['status'] == 'Found') echo 'selected'; ?>>Found</option>
            </select>
            
            <label>Details</label>
            <textarea name="description" rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
            
            <button type="submit" name="update" class="btn-update">Save Changes</button>
        </form>
    </div>
</body>
</html>