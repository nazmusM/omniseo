<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit();
}

// Get user info
$stmt = $db->prepare("SELECT name, email, credits, plan FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Subscription plans
$subscription_plans = [
    [
        'id' => 'free',
        'name' => 'Free',
        'price' => 0,
        'billing' => 'Forever',
        'credits_monthly' => 50,
        'features' => [
            '50 credits per month',
            'Basic article generation',
            'Standard support',
            'Basic templates'
        ],
        'popular' => false,
        'current' => ($user['subscription_plan'] ?? 'free') === 'free'
    ],
    [
        'id' => 'starter',
        'name' => 'Starter',
        'price' => 19,
        'billing' => 'per month',
        'credits_monthly' => 200,
        'features' => [
            '200 credits per month',
            'Advanced AI models',
            'Priority support',
            'All templates',
            'WordPress integration',
            'SEO optimization'
        ],
        'popular' => false,
        'current' => ($user['subscription_plan'] ?? 'free') === 'starter'
    ],
    [
        'id' => 'professional',
        'name' => 'Professional',
        'price' => 49,
        'billing' => 'per month',
        'credits_monthly' => 600,
        'features' => [
            '600 credits per month',
            'Premium AI models',
            'Priority support',
            'All templates',
            'WordPress integration',
            'Advanced SEO tools',
            'Bulk generation',
            'Custom templates',
            'API access'
        ],
        'popular' => true,
        'current' => ($user['subscription_plan'] ?? 'free') === 'professional'
    ],
    [
        'id' => 'enterprise',
        'name' => 'Enterprise',
        'price' => 99,
        'billing' => 'per month',
        'credits_monthly' => 1500,
        'features' => [
            '1500 credits per month',
            'All AI models',
            'Dedicated support',
            'All templates',
            'WordPress integration',
            'Advanced SEO tools',
            'Bulk generation',
            'Custom templates',
            'Full API access',
            'White-label options',
            'Team collaboration',
            'Custom integrations'
        ],
        'popular' => false,
        'current' => ($user['subscription_plan'] ?? 'free') === 'enterprise'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plans - omniSEO</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="plans.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Subscription Plans</h1>
                <p>Choose the perfect plan for your content creation needs</p>
            </div>

            <div class="current-plan-info">
                <div class="current-plan-card">
                    <div class="plan-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="plan-info">
                        <h3>Current Plan</h3>
                        <div class="plan-name">
                            <?php 
                            $current_plan = array_filter($subscription_plans, function($plan) use ($user) {
                                return $plan['current'];
                            });
                            $current_plan = reset($current_plan);
                            echo $current_plan['name'];
                            ?>
                        </div>
                        <p>
                            <?php if ($current_plan['price'] > 0): ?>
                                $<?php echo $current_plan['price']; ?>/month • <?php echo $current_plan['credits_monthly']; ?> credits monthly
                            <?php else: ?>
                                Free forever • <?php echo $current_plan['credits_monthly']; ?> credits monthly
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($current_plan['id'] !== 'free'): ?>
                    <button class="manage-subscription-btn" onclick="manageSubscription()">
                        Manage Subscription
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="billing-toggle">
                <div class="toggle-container">
                    <span class="toggle-label">Monthly</span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="billingToggle">
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Yearly <span class="discount-badge">Save 20%</span></span>
                </div>
            </div>

            <div class="plans-grid">
                <?php foreach ($subscription_plans as $plan): ?>
                <div class="plan-card <?php echo $plan['popular'] ? 'popular' : ''; ?> <?php echo $plan['current'] ? 'current' : ''; ?>">
                    <?php if ($plan['popular']): ?>
                    <div class="popular-badge">Most Popular</div>
                    <?php endif; ?>
                    
                    <?php if ($plan['current']): ?>
                    <div class="current-badge">Current Plan</div>
                    <?php endif; ?>
                    
                    <div class="plan-header">
                        <h3><?php echo $plan['name']; ?></h3>
                        <div class="plan-price">
                            <?php if ($plan['price'] > 0): ?>
                            <span class="price monthly-price">$<?php echo $plan['price']; ?></span>
                            <span class="price yearly-price" style="display: none;">$<?php echo floor($plan['price'] * 0.8); ?></span>
                            <span class="billing-period monthly-billing">per month</span>
                            <span class="billing-period yearly-billing" style="display: none;">per month, billed yearly</span>
                            <?php else: ?>
                            <span class="price">Free</span>
                            <span class="billing-period">Forever</span>
                            <?php endif; ?>
                        </div>
                        <div class="credits-info">
                            <?php echo $plan['credits_monthly']; ?> credits per month
                        </div>
                    </div>
                    
                    <div class="plan-features">
                        <?php foreach ($plan['features'] as $feature): ?>
                        <div class="feature">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <?php echo $feature; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="plan-action">
                        <?php if ($plan['current']): ?>
                        <button class="plan-btn current-btn" disabled>Current Plan</button>
                        <?php elseif ($plan['id'] === 'free'): ?>
                        <button class="plan-btn downgrade-btn" onclick="changePlan('<?php echo $plan['id']; ?>')">
                            Downgrade to Free
                        </button>
                        <?php else: ?>
                        <button class="plan-btn upgrade-btn" onclick="changePlan('<?php echo $plan['id']; ?>')">
                            <?php echo $current_plan['price'] < $plan['price'] ? 'Upgrade' : 'Change'; ?> to <?php echo $plan['name']; ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="faq-section">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-grid">
                    <div class="faq-item">
                        <h4>Can I change my plan anytime?</h4>
                        <p>Yes, you can upgrade or downgrade your plan at any time. Changes take effect immediately.</p>
                    </div>
                    <div class="faq-item">
                        <h4>What happens to unused credits?</h4>
                        <p>Unused credits roll over to the next month, up to a maximum of 2x your monthly allowance.</p>
                    </div>
                    <div class="faq-item">
                        <h4>Do you offer refunds?</h4>
                        <p>We offer a 30-day money-back guarantee for all paid plans. No questions asked.</p>
                    </div>
                    <div class="faq-item">
                        <h4>Can I cancel anytime?</h4>
                        <p>Yes, you can cancel your subscription at any time. You'll continue to have access until the end of your billing period.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Plan Change Modal -->
    <div id="planChangeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change Subscription Plan</h3>
                <button class="close-btn" onclick="closePlanModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="plan-change-summary">
                    <h4>Plan Change Summary</h4>
                    <div class="summary-item">
                        <span>Current Plan:</span>
                        <span id="currentPlanName"><?php echo $current_plan['name']; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>New Plan:</span>
                        <span id="newPlanName"></span>
                    </div>
                    <div class="summary-item">
                        <span>New Monthly Credits:</span>
                        <span id="newPlanCredits"></span>
                    </div>
                    <div class="summary-item total">
                        <span>Monthly Cost:</span>
                        <span id="newPlanPrice"></span>
                    </div>
                </div>
                
                <div class="plan-change-form">
                    <p class="demo-notice">This is a demo. No actual payment will be processed.</p>
                    <button class="confirm-change-btn" onclick="confirmPlanChange()">
                        Confirm Plan Change (Demo)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="plans.js?v=<?=time();?>"></script>
</body>
</html>
