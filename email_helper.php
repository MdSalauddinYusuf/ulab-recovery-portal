<?php
/**
 * PHPMailer Helper Script
 * Designed for ULAB Smart Campus Recovery Portal
 * Developed by: Md. Salauddin Yusuf (Student ID: 242014175)
 * * This script handles automated email notifications using the PHPMailer library
 * and Google's SMTP server via App Passwords.
 */
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load the core library files from the project directory
// Ensure these paths are correct relative to your email_helper.php file
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
/**
 * Function to send dynamic email notifications
 * * @param string $toEmail  Recipient's email address
 * @param string $subject  Subject line of the email
 * @param string $body     Main content of the email (HTML supported)
 * @return bool            Returns true if sent, false otherwise
 */
function sendNotification($toEmail, $subject, $body) {
    // Create a new PHPMailer instance; 'true' enables exceptions
    $mail = new PHPMailer(true);
    try {
        // --- SMTP Server Configuration ---
        // Uncomment the line below to see detailed debug logs if the mail fails
        // $mail->SMTPDebug = 2; 
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host       = 'smtp.gmail.com';                 // Specify Gmail's SMTP server
        $mail->SMTPAuth   = true;                             // Enable SMTP authentication
        /**
         * CREDENTIAL CONFIGURATION
         * Replace 'your-16-char-app-password' with the code from Google App Passwords.
         * Note: Do not use your regular Gmail password here.
         */
        $mail->Username   = 'Your Email'; // Your authorized Gmail address
        $mail->Password   = 'YOUR_APP_PASSWORD';     // 16-character Google App Password
        // Encryption and Port Settings
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption
        $mail->Port       = 587;                              // TCP port for STARTTLS (Standard)
        /**
         * SSL VERIFICATION WORKAROUND (Critical for Localhost/XAMPP)
         * This bypasses the SSL certificate check which often fails on local development servers.
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        // --- Email Header Configuration ---
        $mail->setFrom('salauddin.sagor.xxx@gmail.com', 'ULAB Recovery Portal');
        $mail->addAddress($toEmail);                          // Add recipient dynamically from database
        // --- Content Formatting ---
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        // Professional HTML Template for the email body
        $currentYear = date('Y');
        $fullBody = "
        <div style='max-width: 600px; margin: 20px auto; font-family: Segoe UI, Arial, sans-serif; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
            <div style='background: linear-gradient(135deg, #1a2a6c, #b21f1f); color: #ffffff; padding: 25px; text-align: center;'>
                <h1 style='margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 1px;'>ULAB Smart Campus Recovery</h1>
            </div>           
            <div style='padding: 30px; line-height: 1.7; color: #444; background-color: #ffffff;'>
                <div style='border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px;'>
                    <h2 style='color: #1a2a6c; margin: 0; font-size: 18px;'>Automated System Update</h2>
                </div>
                $body
                <div style='margin-top: 30px; padding: 15px; background-color: #f9f9f9; border-radius: 8px; font-size: 13px; color: #666;'>
                    <strong>Ethical Handover Policy:</strong> Please ensure you have your ULAB ID card ready for verification when collecting or returning items at the security desk.
                </div>
            </div>           
            <div style='background-color: #f1f3f5; color: #777; padding: 20px; text-align: center; font-size: 11px; border-top: 1px solid #eee;'>
                &copy; $currentYear ULAB Recovery Portal | Developed by <strong>Md. Salauddin Yusuf</strong><br>
                This is a system-generated email. Please do not reply to this address.
            </div>
        </div>";
        $mail->Body = $fullBody;
        // Final Execution: Attempt to send the email
        return $mail->send();
    } catch (Exception $e) {
        /**
         * ERROR HANDLING
         * If the notification fails, we log the specific error provided by PHPMailer.
         * You can use 'echo $mail->ErrorInfo;' here temporarily for live debugging.
         */
        return false; 
    }
}
?>