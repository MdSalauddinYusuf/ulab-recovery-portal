<?php
include 'db.php';
session_start();
// 1. Authentication Check: Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// 2. Data Fetching: Retrieve dynamic categories for the filter dropdown
$cat_list_query = "SELECT * FROM categories ORDER BY category_name ASC";
$categories_list = mysqli_query($conn, $cat_list_query);
// 3. Search and Filter Logic: Sanitize URL parameters for SQL query
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
// 4. Main Query: Fetch reported items and check current user's claim status
$sql = "SELECT items.*, 
        (SELECT status FROM claims WHERE claims.item_id = items.id AND claims.claimant_id = '{$_SESSION['user_id']}' LIMIT 1) as my_claim_status 
        FROM items WHERE 1=1";
if ($search != '') { $sql .= " AND (item_name LIKE '%$search%' OR description LIKE '%$search%')"; }
if ($category != '') { $sql .= " AND category = '$category'"; }
if ($status != '') { $sql .= " AND status = '$status'"; }
$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ULAB Lost and Found Portal</title>
    <style>
        /* Professional Layout Styles */
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; min-height: 100vh; }
        .container { width: 100%; max-width: 1150px; flex: 1; }
        /* Header Section Styles */
        .header-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { color: #1a2a6c; margin: 0; border-left: 5px solid #007bff; padding-left: 15px; }
        .nav-links { display: flex; gap: 10px; align-items: center; }       
        /* Interactive Buttons */
        .btn-add { background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; transition: 0.3s; }
        .btn-add:hover { background-color: #0056b3; }
        .btn-admin { background-color: #e67e22; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .btn-logout { background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; }
        /* Filter Section Styles */
        .search-section { background: white; padding: 20px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .search-form { display: flex; gap: 10px; flex-wrap: wrap; }
        .search-form input, .search-form select { padding: 12px; border: 1px solid #ddd; border-radius: 8px; flex: 1; min-width: 150px; outline: none; }
        .btn-filter { background: #27ae60; color: white; border: none; padding: 0 25px; border-radius: 8px; cursor: pointer; font-weight: bold; }
        /* Data Visualization Table */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); margin-bottom: 30px; }
        th { background-color: #f1f3f5; padding: 18px; text-align: left; font-size: 13px; text-transform: uppercase; color: #495057; border-bottom: 2px solid #eee; }
        td { padding: 16px 18px; border-bottom: 1px solid #f1f3f5; vertical-align: middle; }
        .item-img { width: 70px; height: 70px; object-fit: cover; border-radius: 10px; margin-right: 15px; border: 1px solid #eee; }
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .status-lost { background: #fff0f0; color: #d63031; }
        .status-found { background: #f0fff4; color: #27ae60; }       
        /* Claim Action Logic Styles */
        .claim-btn { display: inline-block; margin-top: 8px; color: #007bff; text-decoration: none; font-size: 12px; font-weight: bold; border: 1.5px solid #007bff; padding: 5px 12px; border-radius: 6px; }
        .claim-status { font-size: 11px; margin-top: 5px; font-weight: bold; display: block; color: #666; }
        /* Branding Footer */
        footer { width: 100%; text-align: center; padding: 25px 0; color: #888; font-size: 14px; border-top: 1px solid #eee; background: #fff; margin-top: 20px; }
        footer a { color: #1a2a6c; text-decoration: none; font-weight: bold; transition: 0.3s; }
        footer a:hover { color: #27ae60; text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; width: 100%; margin-bottom: 10px;">
        <span style="color: #555;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> (USER)</span>
        <span style="color: #888;">Student ID: <?php echo $_SESSION['user_id']; ?></span>
    </div>   
    <div class="header-container">
        <h2>ULAB Smart Campus Recovery</h2>
        <span style="color: #555;">
    Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> 
    | <a href="user_profile.php" style="text-decoration:none; color:#007bff;">Edit Profile</a>
</span>
        <div class="nav-links">
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <a href="admin_dashboard.php" class="btn-admin">Admin Panel</a>
            <?php endif; ?>
            <a href="add_item.php" class="btn-add">+ Report Item</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    <div class="search-section">
        <form class="search-form" method="GET">
            <input type="text" name="search" placeholder="Search item, color, or location..." value="<?php echo htmlspecialchars($search); ?>">
           
            <select name="category">
                <option value="">All Categories</option>
                <?php while($cat_row = mysqli_fetch_assoc($categories_list)) { ?>
                    <option value="<?php echo htmlspecialchars($cat_row['category_name']); ?>" <?php if($category == $cat_row['category_name']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat_row['category_name']); ?>
                    </option>
                <?php } ?>
            </select>
            <select name="status">
                <option value="">All Status</option>
                <option value="Lost" <?php if($status == 'Lost') echo 'selected'; ?>>Lost Items</option>
                <option value="Found" <?php if($status == 'Found') echo 'selected'; ?>>Found Items</option>
            </select>
            <button type="submit" class="btn-filter">Search</button>
            <a href="index.php" style="text-decoration:none; color:#999; font-size:13px; align-self:center; margin-left:10px;">Reset</a>
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>Item Details</th>
                <th>Category</th>
                <th>Location</th>
                <th>Status & Actions</th>
                <th>Reported By</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td style="display: flex; align-items: center;">
                        <?php if(!empty($row['image_path'])): ?>
                            <img src="<?php echo $row['image_path']; ?>" class="item-img">
                        <?php else: ?>
                            <div style="background:#eee; width:70px; height:70px; border-radius:10px; margin-right:15px; display:flex; align-items:center; justify-content:center; font-size: 28px;">📦</div>
                        <?php endif; ?>
                        <div>
                            <strong><?php echo htmlspecialchars($row['item_name']); ?></strong><br>
                            <small style="color: #666;"><?php echo htmlspecialchars($row['description']); ?></small>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td>
                        <span class="badge <?php echo ($row['status'] == 'Lost') ? 'status-lost' : 'status-found'; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                        
                        <?php if($row['status'] == 'Found' && $row['reported_by'] != $_SESSION['user_id']): ?>
                            <?php if($row['my_claim_status']): ?>
                                <span class="claim-status">Claimed: <span style="color: #e67e22;"><?php echo $row['my_claim_status']; ?></span></span>
                            <?php else: ?>
                                <br><a href="claim_item.php?id=<?php echo $row['id']; ?>" class="claim-btn">Claim Item</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td><strong>ID: <?php echo $row['reported_by']; ?></strong></td>
                </tr>
            <?php } 
            } else { ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 50px; color: #999;">No items found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> All Rights Reserved. <br>
    Developed by <a href="https://www.facebook.com/salauddin.sagor.16" target="_blank">Md. Salauddin Yusuf</a>
</footer>

</body>
</html>