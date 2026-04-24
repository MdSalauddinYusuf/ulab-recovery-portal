<?php
include 'db.php';
include 'email_helper.php'; // Includes PHPMailer configuration and sendNotification function
session_start();

// Security check: Only allow Admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    die("Access Denied: Administrative privileges required.");
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $claim_id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    // Retrieve claimant details before updating
    // Note: Ensuring the JOIN matches your database schema shown in phpMyAdmin
    $user_info_query = "SELECT users.email, users.full_name, items.item_name 
                        FROM claims 
                        JOIN users ON claims.claimant_id = users.student_id 
                        JOIN items ON claims.item_id = items.id 
                        WHERE claims.claim_id = '$claim_id'";
    
    $user_info_result = mysqli_query($conn, $user_info_query);
    $user_data = mysqli_fetch_assoc($user_info_result);

    // Update the claim status in the database
    $sql = "UPDATE claims SET status = '$status' WHERE claim_id = '$claim_id'";

    if (mysqli_query($conn, $sql)) {
        
        $email_sent = false;
        // Email Notification Logic [Task 4: Ethical & Security Standard]
        if ($user_data && !empty($user_data['email'])) {
            $toEmail = $user_data['email'];
            $subject = "Claim Update: " . $user_data['item_name'];
            
            $message = "<h3>Hello " . htmlspecialchars($user_data['full_name']) . ",</h3>
                        <p>Your claim request for the item <strong>'" . htmlspecialchars($user_data['item_name']) . "'</strong> 
                        has been <strong>" . $status . "</strong> by the ULAB Admin Team.</p>
                        <p>Status: <span style='color:" . ($status == 'Verified' ? 'green' : 'red') . "; font-weight:bold;'>$status</span></p>";
            
            // Sending the email and capturing success status
            $email_sent = sendNotification($toEmail, $subject, $message);
        }

        // Return to admin dashboard with detailed status alert
        $alert_msg = "Claim status updated to $status.";
        if ($email_sent) {
            $alert_msg .= " Notification email sent to " . $user_data['email'];
        } else {
            $alert_msg .= " BUT email notification failed. Check your App Password or Internet.";
        }

        echo "<script>
                alert('" . addslashes($alert_msg) . "'); 
                window.location='admin_dashboard.php';
              </script>";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>