<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit();
}

$title = 'Account Settings';
$stylesheet = 'account.css';
?>
<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Account Settings</h1>
        <p class="page-subtitle">Manage your account information and preferences</p>
    </div>

    <div class="settings-grid">
        <!-- Profile Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
            </div>
            <div class="card-content">
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-input" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Member Since</label>
                        <input type="text" class="form-input" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly>
                    </div>

                    <button type="button" name="update_profile" class="btn btn-primary small-width update-profile">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Change Password</h3>
            </div>
            <div class="card-content">
                <form method="POST" class="password-form">
                    <div class="form-group password-wrapper">
                        <label class="form-label">Current Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-input" name="current_password" required>
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

                    <div class="form-group password-wrapper">
                        <label class="form-label">New Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-input" name="new_password" required>
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

                    <div class="form-group password-wrapper">
                        <label class="form-label">Confirm New Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-input" name="confirm_password" required>
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

                    <button type="button" name="change_password" class="btn btn-primary small-width update-password">Change Password</button>
                </form>

            </div>
        </div>

        <!-- Account Statistics -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Account Statistics</h3>
            </div>
            <div class="card-content">
                <div class="stats-list">
                    <div class="stat-item">
                        <span class="stat-label">Available Credits</span>
                        <span class="stat-value"><?php echo $user['credits']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Current Plan</span>
                        <span class="stat-value">Pro</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Account Status</span>
                        <span class="stat-value status-active">Active</span>
                    </div>
                </div>

                <div class="account-actions">
                    <a href="../plans" class="btn btn-primary">Upgrade Plan</a>
                    <a href="../credits" class="btn btn-secondary">Buy Credits</a>
                </div>
            </div>
        </div>

        <!-- WordPress Integration -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">WordPress Integration</h3>
            </div>
            <div class="card-content">
                <p class="integration-description">
                    Connect your WordPress site to automatically publish generated articles.
                </p>
                <a href="../websites" class="btn btn-primary">Add Websites</a>
            </div>
        </div>
    </div>
</main>
</div>

<script src="account.js?v=<?= time(); ?>"></script>
</body>

</html>