let currentPackage = null

function purchaseCredits(packageId, price, credits) {
  // Find the package details
  const packages = {
    starter: { name: "Starter Pack", credits: 100, price: 9.99 },
    professional: { name: "Professional", credits: 500, price: 39.99 },
    business: { name: "Business", credits: 1000, price: 69.99 },
    enterprise: { name: "Enterprise", credits: 2500, price: 149.99 },
  }

  currentPackage = packages[packageId]

  // Update modal content
  document.getElementById("packageName").textContent = currentPackage.name
  document.getElementById("packageCredits").textContent = currentPackage.credits + " Credits"
  document.getElementById("packagePrice").textContent = "$" + currentPackage.price

  // Show modal
  document.getElementById("purchaseModal").style.display = "block"
}

function closePurchaseModal() {
  document.getElementById("purchaseModal").style.display = "none"
  currentPackage = null
}

function confirmPurchase() {
  if (!currentPackage) return

  // Simulate purchase process
  const confirmBtn = document.querySelector(".confirm-purchase-btn")
  const originalText = confirmBtn.textContent

  confirmBtn.textContent = "Processing..."
  confirmBtn.disabled = true

  setTimeout(() => {
    // Simulate successful purchase
    alert(
      `Successfully purchased ${currentPackage.credits} credits for $${currentPackage.price}!\n\nNote: This is a demo. No actual payment was processed.`,
    )

    // Reset button
    confirmBtn.textContent = originalText
    confirmBtn.disabled = false

    // Close modal
    closePurchaseModal()

    // In a real application, you would:
    // 1. Process the payment through a payment gateway
    // 2. Update the user's credit balance in the database
    // 3. Send confirmation email
    // 4. Refresh the page to show updated credits
  }, 2000)
}

// Close modal when clicking outside
window.onclick = (event) => {
  const modal = document.getElementById("purchaseModal")
  if (event.target === modal) {
    closePurchaseModal()
  }
}

// Add smooth scroll animations
document.addEventListener("DOMContentLoaded", () => {
  // Animate cards on scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1"
        entry.target.style.transform = "translateY(0)"
      }
    })
  }, observerOptions)

  // Observe all package cards
  document.querySelectorAll(".package-card").forEach((card) => {
    card.style.opacity = "0"
    card.style.transform = "translateY(20px)"
    card.style.transition = "opacity 0.6s ease, transform 0.6s ease"
    observer.observe(card)
  })
})
