<?php
include 'db.php';
session_start();

// Security: Verify administrative privileges
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h1 style='color:red;'>Access Denied!</h1>
            <p>Please login as an admin to access this panel.</p>
            <a href='login.php'>Go to Login</a>
         </div>");
}

// Handle Adding New Category
if (isset($_POST['add_cat'])) {
    $new_cat = mysqli_real_escape_string($conn, $_POST['cat_name']);
    mysqli_query($conn, "INSERT IGNORE INTO categories (category_name) VALUES ('$new_cat')");
}

// Handle Deleting Category
if (isset($_GET['del_cat'])) {
    $id = (int)$_GET['del_cat'];
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    header("Location: admin_dashboard.php");
}

// Fetch Real-time Stats
$total_items = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM items"))['total'];
$total_claims = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM claims"))['total'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];

// Fetch All Required Data
$items = mysqli_query($conn, "SELECT * FROM items ORDER BY id DESC");
$claims = mysqli_query($conn, "SELECT claims.*, items.item_name, users.full_name FROM claims JOIN items ON claims.item_id = items.id JOIN users ON claims.claimant_id = users.student_id ORDER BY claims.claim_id DESC");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Admin Panel | ULAB Smart Recovery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e3799;
            --secondary: #eb2f06;
            --success: #079992;
            --dark: #2f3542;
            --light: #f1f2f6;
            --white: #ffffff;
            --shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f8f9fa; display: flex; color: var(--dark); }

        /* Sidebar Navigation */
        .sidebar { width: 280px; height: 100vh; background: var(--dark); color: white; position: fixed; transition: 0.3s; z-index: 1000; }
        .sidebar-header { padding: 30px; text-align: center; background: rgba(0,0,0,0.2); }
        .sidebar-header h2 { font-size: 22px; letter-spacing: 1px; color: #fff; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a { padding: 15px 30px; display: flex; align-items: center; color: #ced4da; text-decoration: none; transition: 0.3s; font-size: 15px; }
        .sidebar-menu a i { margin-right: 15px; width: 20px; font-size: 18px; }
        .sidebar-menu a:hover { background: rgba(255,255,255,0.05); color: #fff; padding-left: 35px; }
        .sidebar-menu a.active { background: var(--primary); color: white; border-left: 5px solid #fff; }

        /* Main Dashboard Content */
        .main-wrapper { margin-left: 280px; width: calc(100% - 280px); padding: 40px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid var(--primary); }

        /* Statistics Cards */
        .stats-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 16px; display: flex; align-items: center; box-shadow: var(--shadow); border-bottom: 4px solid transparent; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); border-bottom-color: var(--primary); }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-right: 20px; }
        .stat-card.blue .stat-icon { background: #eef2ff; color: #4361ee; }
        .stat-card.green .stat-icon { background: #e6fffa; color: #00b894; }
        .stat-card.orange .stat-icon { background: #fff7ed; color: #f39c12; }
        .stat-info h3 { font-size: 28px; margin-bottom: 5px; }
        .stat-info p { color: #888; font-size: 14px; }

        /* Layout Grid */
        .dashboard-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }

        /* Common Card Component */
        .data-card { background: var(--white); padding: 30px; border-radius: 20px; box-shadow: var(--shadow); margin-bottom: 30px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .card-header h3 { font-size: 18px; color: var(--dark); display: flex; align-items: center; gap: 10px; }

        /* Forms */
        .category-input-group { display: flex; gap: 10px; margin-bottom: 20px; }
        .category-input-group input { flex: 1; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; outline: none; }
        .btn-action { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; transition: 0.3s; }
        .btn-action:hover { background: #152a7a; }

        /* Professional Tables */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #a0a0a0; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #f0f0f0; }
        td { padding: 15px; font-size: 14px; border-bottom: 1px solid #f8f8f8; }
        .status-pill { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .status-lost { background: #fff0f0; color: #e74c3c; }
        .status-found { background: #e6fffa; color: #00b894; }

        /* Icons & Actions */
        .action-link { text-decoration: none; margin-right: 12px; font-size: 16px; }
        .edit-link { color: #4361ee; }
        .delete-link { color: #e74c3c; }
        .verify-link { color: #00b894; font-weight: 600; }

        footer { text-align: center; padding: 30px; color: #adb5bd; font-size: 13px; }
        footer a { color: var(--primary); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-shield-alt"></i> ULAB ADMIN</h2>
        </div>
        <div class="sidebar-menu">
            <a href="index.php"><i class="fas fa-external-link-alt"></i> Back to Home</a>
            <a href="#" class="active"><i class="fas fa-th-large"></i> Dashboard Overview</a>
            <a href="admin_profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
            <a href="admin_dashboard.php"><i class="fas fa-tags"></i> Categories</a>
            <a href="#"><i class="fas fa-users"></i> User Management</a>
            <a href="logout.php" style="margin-top: 50px; color: #ff7675;"><i class="fas fa-power-off"></i> Sign Out</a>

        </div>
    </div>

    <div class="main-wrapper">
        <div class="top-bar">
            <h1>Dashboard Overview</h1>
            <div class="user-info">
                <div style="text-align: right;">
                    <p style="font-weight: bold;">Salauddin Yusuf</p>
                    <p style="font-size: 12px; color: #888;">System Administrator</p>
                </div>
                <img src="https://ui-avatars.com/api/?name=Salauddin+Yusuf&background=1e3799&color=fff" alt="Admin">
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-info"><h3><?php echo $total_items; ?></h3><p>Total Items Reported</p></div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="stat-info"><h3><?php echo $total_claims; ?></h3><p>Active Claim Requests</p></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-info"><h3><?php echo $total_users; ?></h3><p>Registered Students</p></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="left-col">
                <div class="data-card">
                    <div class="card-header">
                        <h3><i class="fas fa-tag"></i> Categories</h3>
                    </div>
                    <form method="POST" class="category-input-group">
                        <input type="text" name="cat_name" placeholder="Enter Category Name" required>
                        <button type="submit" name="add_cat" class="btn-action">Add</button>
                    </form>
                    <table>
                        <thead><tr><th>Name</th><th style="text-align:right;">Action</th></tr></thead>
                        <tbody>
                            <?php while($c = mysqli_fetch_assoc($categories)) { ?>
                            <tr>
                                <td><strong><?php echo $c['category_name']; ?></strong></td>
                                <td style="text-align:right;"><a href="?del_cat=<?php echo $c['id']; ?>" class="delete-link" onclick="return confirm('Delete category?')"><i class="fas fa-trash-alt"></i></a></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="right-col">
                <div class="data-card">
                    <div class="card-header">
                        <h3><i class="fas fa-bell"></i> Pending Claim Requests</h3>
                    </div>
                    <table>
                        <thead><tr><th>Item</th><th>Claimant</th><th style="text-align:center;">Decision</th></tr></thead>
                        <tbody>
                            <?php while($cl = mysqli_fetch_assoc($claims)) { ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cl['item_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cl['full_name']); ?></td>
                                <td style="text-align:center;">
                                    <a href="verify_claim.php?id=<?php echo $cl['claim_id']; ?>&status=Verified" class="verify-link"><i class="fas fa-check"></i> Verify</a>
                                    <a href="verify_claim.php?id=<?php echo $cl['claim_id']; ?>&status=Rejected" class="delete-link" style="margin-left:15px;"><i class="fas fa-times"></i> Reject</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="data-card">
            <div class="card-header">
                <h3><i class="fas fa-list-ul"></i> Manage All Reported Items</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($items)) { ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><span class="status-pill <?php echo ($row['status']=='Lost') ? 'status-lost' : 'status-found'; ?>"><?php echo $row['status']; ?></span></td>
                        <td><i class="fas fa-map-marker-alt" style="font-size:11px; color:#aaa;"></i> <?php echo htmlspecialchars($row['location']); ?></td>
                        <td style="text-align: center;">
                            <a href="edit_item.php?id=<?php echo $row['id']; ?>" class="action-link edit-link"><i class="fas fa-edit"></i></a>
                            <a href="delete_item.php?id=<?php echo $row['id']; ?>" class="action-link delete-link" onclick="return confirm('Delete this record permanently?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <footer>
            &copy; 2026 Developed by <a href="https://www.facebook.com/salauddin.sagor.16" target="_blank">Md. Salauddin Yusuf</a> | ULAB Smart Campus Portfolio
        </footer>
    </div>

</body>
</html>