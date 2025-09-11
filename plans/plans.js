const currentPlanData = null
let selectedPlanData = null

// Billing toggle functionality
document.addEventListener("DOMContentLoaded", () => {
  const billingToggle = document.getElementById("billingToggle")
  const monthlyPrices = document.querySelectorAll(".monthly-price")
  const yearlyPrices = document.querySelectorAll(".yearly-price")
  const monthlyBilling = document.querySelectorAll(".monthly-billing")
  const yearlyBilling = document.querySelectorAll(".yearly-billing")

  billingToggle.addEventListener("change", function () {
    const isYearly = this.checked

    monthlyPrices.forEach((price) => {
      price.style.display = isYearly ? "none" : "inline"
    })

    yearlyPrices.forEach((price) => {
      price.style.display = isYearly ? "inline" : "none"
    })

    monthlyBilling.forEach((billing) => {
      billing.style.display = isYearly ? "none" : "inline"
    })

    yearlyBilling.forEach((billing) => {
      billing.style.display = isYearly ? "inline" : "none"
    })
  })

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

  // Observe all plan cards
  document.querySelectorAll(".plan-card").forEach((card) => {
    card.style.opacity = "0"
    card.style.transform = "translateY(20px)"
    card.style.transition = "opacity 0.6s ease, transform 0.6s ease"
    observer.observe(card)
  })
})

function changePlan(planId) {
  // Plan data
  const plans = {
    free: { name: "Free", price: 0, credits: 50 },
    starter: { name: "Starter", price: 19, credits: 200 },
    professional: { name: "Professional", price: 49, credits: 600 },
    enterprise: { name: "Enterprise", price: 99, credits: 1500 },
  }

  selectedPlanData = plans[planId]

  // Update modal content
  document.getElementById("newPlanName").textContent = selectedPlanData.name
  document.getElementById("newPlanCredits").textContent = selectedPlanData.credits + " credits"
  document.getElementById("newPlanPrice").textContent =
    selectedPlanData.price > 0 ? "$" + selectedPlanData.price + "/month" : "Free"

  // Show modal
  document.getElementById("planChangeModal").style.display = "block"
}

function closePlanModal() {
  document.getElementById("planChangeModal").style.display = "none"
  selectedPlanData = null
}

function confirmPlanChange() {
  if (!selectedPlanData) return

  // Simulate plan change process
  const confirmBtn = document.querySelector(".confirm-change-btn")
  const originalText = confirmBtn.textContent

  confirmBtn.textContent = "Processing..."
  confirmBtn.disabled = true

  setTimeout(() => {
    // Simulate successful plan change
    alert(
      `Successfully changed to ${selectedPlanData.name} plan!\n\nNote: This is a demo. No actual subscription change was processed.`,
    )

    // Reset button
    confirmBtn.textContent = originalText
    confirmBtn.disabled = false

    // Close modal
    closePlanModal()

    // In a real application, you would:
    // 1. Process the subscription change through a payment gateway
    // 2. Update the user's subscription in the database
    // 3. Send confirmation email
    // 4. Refresh the page to show updated plan
  }, 2000)
}

function manageSubscription() {
  // In a real application, this would redirect to a subscription management portal
  alert(
    "This would redirect to your subscription management portal where you can:\n\n• Update payment method\n• View billing history\n• Cancel subscription\n• Download invoices\n\nNote: This is a demo feature.",
  )
}

// Close modal when clicking outside
window.onclick = (event) => {
  const modal = document.getElementById("planChangeModal")
  if (event.target === modal) {
    closePlanModal()
  }
}
