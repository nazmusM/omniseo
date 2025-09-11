document.addEventListener("DOMContentLoaded", () => {
  // Copy article functionality
  window.copyArticle = async (articleId) => {
    try {
      const response = await fetch(`/api/get-article.php?id=${articleId}`)
      const data = await response.json()

      if (data.success) {
        await navigator.clipboard.writeText(data.content)
        showMessage("Article copied to clipboard!", "success")
      } else {
        showMessage("Failed to copy article.", "error")
      }
    } catch (error) {
      console.error("[v0] Error copying article:", error)
      showMessage("Failed to copy article.", "error")
    }
  }

  // Delete article functionality
  window.deleteArticle = async (articleId) => {
    if (!confirm("Are you sure you want to delete this article? This action cannot be undone.")) {
      return
    }

    try {
      const response = await fetch("/api/delete-article.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id: articleId }),
      })

      const data = await response.json()

      if (data.success) {
        showMessage("Article deleted successfully!", "success")
        // Remove the article card from the DOM
        const articleCard = document.querySelector(`[onclick="deleteArticle(${articleId})"]`).closest(".article-card")
        articleCard.remove()

        // Check if no articles left
        const remainingCards = document.querySelectorAll(".article-card")
        if (remainingCards.length === 0) {
          location.reload() // Reload to show empty state
        }
      } else {
        showMessage(data.message || "Failed to delete article.", "error")
      }
    } catch (error) {
      console.error("[v0] Error deleting article:", error)
      showMessage("Failed to delete article.", "error")
    }
  }

  function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll(".message")
    existingMessages.forEach((msg) => msg.remove())

    // Create new message
    const messageDiv = document.createElement("div")
    messageDiv.className = `message ${type}`
    messageDiv.textContent = message

    // Insert at the top of main content
    const mainContent = document.querySelector(".main-content")
    mainContent.insertBefore(messageDiv, mainContent.firstChild)

    // Auto-remove after 5 seconds
    setTimeout(() => {
      messageDiv.remove()
    }, 5000)
  }
})
