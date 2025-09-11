<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}

// Get user info
$stmt = $db->prepare("SELECT name, email, credits FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Credit packages
$credit_packages = [
    [
        'id' => 'starter',
        'name' => 'Starter Pack',
        'credits' => 100,
        'price' => 9.99,
        'popular' => false,
        'description' => 'Perfect for getting started'
    ],
    [
        'id' => 'professional',
        'name' => 'Professional',
        'credits' => 500,
        'price' => 39.99,
        'popular' => true,
        'description' => 'Most popular choice'
    ],
    [
        'id' => 'business',
        'name' => 'Business',
        'credits' => 1000,
        'price' => 69.99,
        'popular' => false,
        'description' => 'For heavy users'
    ],
    [
        'id' => 'enterprise',
        'name' => 'Enterprise',
        'credits' => 2500,
        'price' => 149.99,
        'popular' => false,
        'description' => 'Maximum value'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits - omniSEO</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="credits.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Credits</h1>
                <p>Purchase credits to generate more amazing content</p>
            </div>

            <div class="credits-overview">
                <div class="current-credits-card">
                    <div class="credits-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <div class="credits-info">
                        <h3>Current Balance</h3>
                        <div class="credits-amount"><?php echo $user['credits']; ?> Credits</div>
                        <p>Each article generation costs 10 credits</p>
                    </div>
                </div>

                <div class="usage-stats">
                    <h4>Usage Statistics</h4>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">This Month</span>
                            <span class="stat-value">47 Articles</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Credits Used</span>
                            <span class="stat-value">470 Credits</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Average Daily</span>
                            <span class="stat-value">15.6 Credits</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="credit-packages">
                <h2>Buy Credits</h2>
                <p>Choose the perfect credit package for your needs</p>
                
                <div class="packages-grid">
                    <?php foreach ($credit_packages as $package): ?>
                    <div class="package-card <?php echo $package['popular'] ? 'popular' : ''; ?>">
                        <?php if ($package['popular']): ?>
                        <div class="popular-badge">Most Popular</div>
                        <?php endif; ?>
                        
                        <div class="package-header">
                            <h3><?php echo $package['name']; ?></h3>
                            <p><?php echo $package['description']; ?></p>
                        </div>
                        
                        <div class="package-price">
                            <span class="price">$<?php echo $package['price']; ?></span>
                            <span class="credits"><?php echo $package['credits']; ?> Credits</span>
                        </div>
                        
                        <div class="package-features">
                            <div class="feature">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20,6 9,17 4,12"/>
                                </svg>
                                <?php echo $package['credits']; ?> AI-generated articles
                            </div>
                            <div class="feature">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20,6 9,17 4,12"/>
                                </svg>
                                Multiple languages support
                            </div>
                            <div class="feature">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20,6 9,17 4,12"/>
                                </svg>
                                WordPress integration
                            </div>
                            <div class="feature">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20,6 9,17 4,12"/>
                                </svg>
                                Priority support
                            </div>
                        </div>
                        
                        <button class="buy-btn" onclick="purchaseCredits('<?php echo $package['id']; ?>', <?php echo $package['price']; ?>, <?php echo $package['credits']; ?>)">
                            Purchase Now
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="payment-info">
                <h3>Payment Information</h3>
                <div class="payment-methods">
                    <div class="payment-method">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        <span>Credit Card</span>
                    </div>
                    <div class="payment-method">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/>
                            <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/>
                            <path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"/>
                        </svg>
                        <span>PayPal</span>
                    </div>
                    <div class="payment-method">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12l2 2 4-4"/>
                        </svg>
                        <span>Secure Payment</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Purchase Modal -->
    <div id="purchaseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Complete Purchase</h3>
                <button class="close-btn" onclick="closePurchaseModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="purchase-summary">
                    <h4>Purchase Summary</h4>
                    <div class="summary-item">
                        <span>Package:</span>
                        <span id="packageName"></span>
                    </div>
                    <div class="summary-item">
                        <span>Credits:</span>
                        <span id="packageCredits"></span>
                    </div>
                    <div class="summary-item total">
                        <span>Total:</span>
                        <span id="packagePrice"></span>
                    </div>
                </div>
                
                <div class="payment-form">
                    <h4>Payment Details</h4>
                    <p class="demo-notice">This is a demo. No actual payment will be processed.</p>
                    <button class="confirm-purchase-btn" onclick="confirmPurchase()">
                        Confirm Purchase (Demo)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="credits.js?v=<?=time();?>"></script>
</body>
</html>
