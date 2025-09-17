<?php
ini_set("display_errors", 1);
require('includes/config.php');

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using PHPMailer with SMTP
 */
function sendMail($to, $subject, $body, $altBody = '', $attachments = [], $cc = [], $bcc = []) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']);
        $mail->addAddress($to);
        $mail->addReplyTo($_ENV['REPLY_TO_EMAIL'], $_ENV['REPLY_TO_NAME']);

        // Add CC
        foreach ($cc as $ccEmail) {
            $mail->addCC($ccEmail);
        }

        // Add BCC
        foreach ($bcc as $bccEmail) {
            $mail->addBCC($bccEmail);
        }

        // Add attachments
        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = !empty($altBody) ? $altBody : strip_tags($body);

        $mail->send();

        return [
            'status' => 'success',
            'message' => 'Message has been sent successfully'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
        ];
    }
}

/**
 * Generate a styled email template
 */
function generateEmailTemplate($title, $content, $buttonText = '', $buttonUrl = '') {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
        <style>
            body { font-family: Arial, sans-serif; background:#f9fafb; margin:0; padding:0; }
            .container { max-width:600px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; }
            .header { background:#164e63; color:#fff; padding:20px; text-align:center; font-size:24px; }
            .body { padding:30px; color:#334155; font-size:16px; }
            .button { display:inline-block; padding:12px 24px; background:#10b981; color:#fff; text-decoration:none; border-radius:6px; margin:20px 0; }
            .footer { background:#f1f5f9; padding:15px; text-align:center; font-size:13px; color:#64748b; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">OmniSEO</div>
            <div class="body">
                <h2>' . htmlspecialchars($title) . '</h2>
                ' . $content . '
                ' . (!empty($buttonText) && !empty($buttonUrl) ? '<div><a href="' . htmlspecialchars($buttonUrl) . '" class="button">' . htmlspecialchars($buttonText) . '</a></div>' : '') . '
            </div>
            <div class="footer">
                &copy; ' . date('Y') . ' OmniSEO. All rights reserved.
            </div>
        </div>
    </body>
    </html>';
}



// Example: Send password reset email
$userEmail = 'nazmussakibsyam2@gmail.com';
$resetToken = bin2hex(random_bytes(16)); // safe string token
$resetLink = 'https://omniseo.com/reset-password?token=' . urlencode($resetToken);

$emailTitle = 'Reset Your OmniSEO Password';
$emailContent = '
    <p>We received a request to reset your OmniSEO password.</p>
    <p>Click the button below to choose a new password:</p>
    <p>If you didn\'t request this change, please ignore this email. Your password will remain unchanged.</p>
    <p>This password reset link will expire in 1 hour for security reasons.</p>
';

$emailBody = generateEmailTemplate($emailTitle, $emailContent, 'Reset Password', $resetLink);

// Send the email
$result = sendMail($userEmail, $emailTitle, $emailBody);

if ($result['status'] === 'success') {
    echo "Password reset email sent successfully!";
} else {
    echo "Error: " . $result['message'];
}
