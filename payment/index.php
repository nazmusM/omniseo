<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

$auth->requireLogin();
$user = $auth->getCurrentUser();

// Fetch user's current plan and credits
$stmt = $db->prepare("SELECT credits, plan FROM users WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// Define credit packages
$creditPackages = [
    [
        'id' => 'package_1',
        'name' => 'Starter Pack',
        'credits' => 5000,
        'price' => 19.99,
        'popular' => false,
        'features' => ['5,000 SEO Credits', 'Basic Support', '5 WordPress Sites']
    ],
    [
        'id' => 'package_2',
        'name' => 'Professional',
        'credits' => 15000,
        'price' => 49.99,
        'popular' => true,
        'features' => ['15,000 SEO Credits', 'Priority Support', '15 WordPress Sites', 'Advanced Analytics']
    ],
    [
        'id' => 'package_3',
        'name' => 'Enterprise',
        'credits' => 50000,
        'price' => 149.99,
        'popular' => false,
        'features' => ['50,000 SEO Credits', '24/7 Support', 'Unlimited Sites', 'Advanced Analytics', 'API Access']
    ]
];

// Handle successful payment callback (this would typically be handled by a webhook)
if (isset($_GET['success']) && $_GET['success'] === 'true') {
    $session_id = $_GET['session_id'] ?? '';
    $message = 'Payment completed successfully! Your credits have been added to your account.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Purchase Credits - OmniSEO</title>
  <link rel="stylesheet" href="payment.css">
  <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="main-container">
    <?php include 'sidebar.php'; ?>

    <div class="content">
      <div class="page-header">
        <h1><i class="icon-credit-card"></i> Purchase Credits</h1>
        <div class="current-balance">
          <span class="balance-label">Current Balance:</span>
          <span class="balance-amount"><?= number_format($userData['credits']) ?> credits</span>
        </div>
      </div>

      <?php if (isset($message)): ?>
        <div class="alert alert-success">
          <i class="icon-check"></i> <?= $message ?>
        </div>
      <?php endif; ?>

      <div class="pricing-container">
        <div class="pricing-header">
          <h2>Choose Your Plan</h2>
          <p>Select the credit package that best fits your SEO needs</p>
        </div>

        <div class="pricing-grid">
          <?php foreach ($creditPackages as $package): ?>
            <div class="pricing-card <?= $package['popular'] ? 'popular' : '' ?>">
              <?php if ($package['popular']): ?>
                <div class="popular-badge">Most Popular</div>
              <?php endif; ?>
              
              <div class="package-name"><?= $package['name'] ?></div>
              <div class="package-credits"><?= number_format($package['credits']) ?> Credits</div>
              
              <div class="package-price">
                <span class="price-currency">$</span>
                <span class="price-amount"><?= number_format($package['price'], 2) ?></span>
              </div>
              
              <ul class="package-features">
                <?php foreach ($package['features'] as $feature): ?>
                  <li><i class="icon-check"></i> <?= $feature ?></li>
                <?php endforeach; ?>
              </ul>
              
              <button class="btn btn-primary btn-select-plan" 
                      data-package-id="<?= $package['id'] ?>"
                      data-price="<?= $package['price'] * 100 ?>"
                      data-credits="<?= $package['credits'] ?>">
                Select Plan
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Custom Amount Section -->
      <div class="custom-amount-section">
        <div class="section-header">
          <h3>Custom Amount</h3>
          <p>Need a different amount? Enter custom credit value</p>
        </div>
        
        <div class="custom-amount-form">
          <div class="form-group">
            <label for="custom-amount">Number of Credits</label>
            <input type="number" id="custom-amount" min="1000" step="1000" value="5000" 
                   placeholder="Enter credits amount">
            <div class="amount-preview">
              <span class="preview-label">Estimated cost:</span>
              <span class="preview-amount">$19.99</span>
            </div>
          </div>
          <button class="btn btn-primary" id="btn-custom-purchase">Purchase Custom Credits</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment Modal -->
  <div id="paymentModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Complete Payment</h2>
        <span class="modal-close" onclick="closePaymentModal()">&times;</span>
      </div>
      
      <div class="modal-body">
        <div class="payment-summary">
          <div class="summary-item">
            <span>Package:</span>
            <span id="summary-package">Professional</span>
          </div>
          <div class="summary-item">
            <span>Credits:</span>
            <span id="summary-credits">15,000</span>
          </div>
          <div class="summary-item total">
            <span>Total:</span>
            <span id="summary-total">$49.99</span>
          </div>
        </div>

        <form id="payment-form">
          <div class="form-group">
            <label for="cardholder-name">Cardholder Name</label>
            <input type="text" id="cardholder-name" placeholder="Enter full name" required>
          </div>
          
          <div class="form-group">
            <label for="card-element">Credit or Debit Card</label>
            <div id="card-element" class="card-element">
              <!-- Stripe Elements will create form elements here -->
            </div>
            <div id="card-errors" class="card-errors"></div>
          </div>

          <div class="payment-security">
            <i class="icon-lock"></i>
            <span>Your payment information is secure and encrypted</span>
          </div>
        </form>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
        <button type="button" class="btn btn-primary" id="submit-payment">
          <span class="btn-loading" style="display: none;">
            <i class="icon-spinner"></i>
          </span>
          <span class="btn-text">Pay Now</span>
        </button>
      </div>
    </div>
  </div>

  <script>
    // Stripe configuration
    const stripe = Stripe('<?= $_ENV['STRIPE_PUBLISHABLE_KEY'] ?>');
    const elements = stripe.elements();
    
    // Store selected package info
    let selectedPackage = null;
    
    // Initialize Stripe card element
    const card = elements.create('card', {
      style: {
        base: {
          fontSize: '16px',
          color: '#2d3748',
          '::placeholder': {
            color: '#a0aec0',
          },
        },
      },
    });
  </script>
  <script src="payment.js"></script>
</body>
</html>