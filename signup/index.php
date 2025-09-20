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
    <title>Sign Up - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../login/login.css?v=<?= time(); ?>">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join omniSEO and start generating amazing content</p>
            </div>

            <form method="POST" class="signup-form">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-input" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-input" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-input" name="password" required>
                        <span class="toggle-password">
                            <!-- eye icon -->
                            <svg class="eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <!-- eye slash icon -->
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

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-input" name="confirm_password" required>
                        <span class="toggle-password">
                            <!-- eye icon -->
                            <svg class="eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <!-- eye slash icon -->
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

                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                <div class="auth-footer">
                    <p>By registering in our platform, you agree to our <strong>Privacy Policy</strong> and <strong>Terms of Services</strong> described in <a href="../legal">this page</a>.</p>
                </div>
                <button type="submit" class="btn btn-primary sign-up full-width">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="../login">Sign in here</a></p>
            </div>
        </div>
    </div>
    <script src="../assets/sweetalert/sweetalert.min.js"></script>
    <script src="../assets/js/main.js?v=<?= time(); ?>"></script>
</body>

</html>