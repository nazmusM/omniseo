<?php
session_start();
require_once '../includes/db.php';

// Check if token is provided in the URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Validate token
if (!empty($token)) {
    $stmt = $db->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        $invalidToken = true;
    }
} else {
    $invalidToken = true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['token'];
    
    // Validate passwords
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Update password in database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        $stmt->bind_param('ss', $hashed_password, $token);
        
        if ($stmt->execute()) {
            $success = "Password has been reset successfully. You can now login with your new password.";
        } else {
            $error = "An error occurred. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Content Generator</title>
    <link rel="stylesheet" href="reset-password.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-password-card">
            <div class="reset-password-header">
                <h1>Reset Password</h1>
                <p>Enter your new password below</p>
            </div>
            
            <?php if (isset($invalidToken) && $invalidToken): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Invalid or expired reset token. Please request a new password reset.</p>
                </div>
                <div class="back-to-login">
                    <a href="login.php">Back to Login</a>
                </div>
            <?php elseif (isset($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <p><?php echo $success; ?></p>
                </div>
                <div class="back-to-login">
                    <a href="login.php">Back to Login</a>
                </div>
            <?php else: ?>
                <form id="resetPasswordForm" class="reset-password-form" method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <div class="password-input-container">
                            <input type="password" id="password" name="password" class="form-input" required minlength="8">
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="password-input-container">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required minlength="8">
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p><?php echo $error; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="reset_password" class="reset-password-btn">
                        Reset Password
                    </button>
                </form>
                
                <div class="back-to-login">
                    <a href="login.php">Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="reset-password.js"></script>
</body>
</html>