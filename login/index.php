<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - omniSEO</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="login.css?v=<?= time(); ?>">
    <script src="../assets/sweetalert/sweetalert.min.js"></script>
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your omniSEO account</p>
            </div>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-input" name="email" id="email" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-input" name="password" id="password" required>
                        <span class="toggle-password">
                            <svg class="eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg class="eye-slash" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z" />
                                <circle cx="12" cy="12" r="3" />
                                <line x1="4" y1="4" x2="20" y2="20" />
                            </svg>
                        </span>
                    </div>
                </div>

                    <a href="../reset-password" style="display: inline-block; text-decoration: none; color: var(--grey-500); margin-bottom: 20px;">Reset Password</a>

                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                <button type="submit" class="btn btn-primary login-btn full-width">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="../signup">Sign up here</a></p>
                <p>Test account:</p>
                <strong>email: nazmussakibsyam2@gmail.com</strong><br>
                <strong>pass: nazmuss111*M</strong>
            </div>
        </div>
    </div>
    <script src="login.js?v=<?= time(); ?>"></script>
    <script src="../assets/js/main.js?v=<?= time(); ?>"></script>
</body>

</html>