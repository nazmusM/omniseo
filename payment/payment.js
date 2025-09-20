// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Stripe card element
    card.mount('#card-element');
    
    // Set up event listeners
    setupEventListeners();
    
    // Calculate initial custom amount price
    updateCustomAmountPrice();
});

// Set up event listeners
function setupEventListeners() {
    // Plan selection buttons
    document.querySelectorAll('.btn-select-plan').forEach(button => {
        button.addEventListener('click', handlePlanSelection);
    });
    
    // Custom amount input
    document.getElementById('custom-amount').addEventListener('input', updateCustomAmountPrice);
    
    // Custom purchase button
    document.getElementById('btn-custom-purchase').addEventListener('click', handleCustomPurchase);
    
    // Payment form submission
    document.getElementById('submit-payment').addEventListener('click', handlePaymentSubmission);
    
    // Card element validation
    card.addEventListener('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
}

// Handle plan selection
function handlePlanSelection(event) {
    const button = event.currentTarget;
    selectedPackage = {
        id: button.dataset.packageId,
        name: button.closest('.pricing-card').querySelector('.package-name').textContent,
        credits: parseInt(button.dataset.credits),
        price: parseInt(button.dataset.price) / 100 // Convert from cents to dollars
    };
    
    openPaymentModal();
}

// Handle custom purchase
function handleCustomPurchase() {
    const customAmount = parseInt(document.getElementById('custom-amount').value);
    
    if (isNaN(customAmount) || customAmount < 1000) {
        showNotification('Please enter a valid amount (minimum 1000 credits)', 'error');
        return;
    }
    
    // Calculate price (example: $0.004 per credit)
    const price = (customAmount * 0.004).toFixed(2);
    
    selectedPackage = {
        id: 'custom_package',
        name: 'Custom Package',
        credits: customAmount,
        price: parseFloat(price)
    };
    
    openPaymentModal();
}

// Update custom amount price preview
function updateCustomAmountPrice() {
    const amountInput = document.getElementById('custom-amount');
    const previewAmount = document.querySelector('.preview-amount');
    
    const credits = parseInt(amountInput.value) || 0;
    const price = (credits * 0.004).toFixed(2);
    
    previewAmount.textContent = `$${price}`;
}

// Open payment modal
function openPaymentModal() {
    if (!selectedPackage) return;
    
    // Update modal content
    document.getElementById('summary-package').textContent = selectedPackage.name;
    document.getElementById('summary-credits').textContent = selectedPackage.credits.toLocaleString();
    document.getElementById('summary-total').textContent = `$${selectedPackage.price.toFixed(2)}`;
    
    // Show modal
    document.getElementById('paymentModal').style.display = 'flex';
}

// Close payment modal
function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    document.getElementById('payment-form').reset();
    document.getElementById('card-errors').textContent = '';
    selectedPackage = null;
}

// Handle payment submission
async function handlePaymentSubmission() {
    const submitBtn = document.getElementById('submit-payment');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    const cardholderName = document.getElementById('cardholder-name').value;
    
    // Validate inputs
    if (!cardholderName) {
        showNotification('Please enter cardholder name', 'error');
        return;
    }
    
    // Show loading state
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline-flex';
    submitBtn.disabled = true;
    
    try {
        // Create payment method
        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: card,
            billing_details: {
                name: cardholderName,
            },
        });
        
        if (error) {
            throw new Error(error.message);
        }
        
        // Send payment to server
        const response = await fetch('ajax/payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                payment_method_id: paymentMethod.id,
                package: selectedPackage,
            }),
        });
        
        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }
        
        if (result.requires_action) {
            // Handle 3D Secure authentication
            const { error } = await stripe.handleCardAction(
                result.payment_intent_client_secret
            );
            
            if (error) {
                throw new Error(error.message);
            }
            
            // Confirm payment after authentication
            const confirmResponse = await fetch('ajax/payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_intent_id: result.payment_intent_id,
                }),
            });
            
            const confirmResult = await confirmResponse.json();
            
            if (confirmResult.error) {
                throw new Error(confirmResult.error);
            }
            
            // Payment successful
            showPaymentSuccess();
        } else {
            // Payment successful without authentication
            showPaymentSuccess();
        }
    } catch (error) {
        showNotification(error.message, 'error');
        console.error('Payment error:', error);
    } finally {
        // Restore button state
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
        submitBtn.disabled = false;
    }
}

// Show payment success
function showPaymentSuccess() {
    const modalBody = document.querySelector('#paymentModal .modal-body');
    modalBody.innerHTML = `
        <div class="payment-success">
            <i class="icon-check-circle"></i>
            <h3>Payment Successful!</h3>
            <p>${selectedPackage.credits.toLocaleString()} credits have been added to your account.</p>
            <button class="btn btn-primary" onclick="closePaymentModal(); window.location.reload();">
                Continue
            </button>
        </div>
    `;
    
    document.querySelector('#paymentModal .modal-footer').style.display = 'none';
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="icon-${type === 'error' ? 'alert' : type}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Remove after delay
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target === modal) {
        closePaymentModal();
    }
};