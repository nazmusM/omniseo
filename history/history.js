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
})
