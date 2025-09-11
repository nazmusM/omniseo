// Main JavaScript functionality for omniSEO

class OmniSEO {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.initializeComponents();
  }

  bindEvents() {
    // Auth form submissions
    const loginForm = document.getElementById("loginForm");
    const signupForm = document.getElementById("signupForm");
    const generateForm = document.getElementById("generateForm");

    if (loginForm) {
      loginForm.addEventListener("submit", this.handleLogin.bind(this));
    }

    if (signupForm) {
      signupForm.addEventListener("submit", this.handleSignup.bind(this));
    }

    if (generateForm) {
      generateForm.addEventListener("submit", this.handleGenerate.bind(this));
    }

    // Copy buttons
    document.addEventListener("click", (e) => {
      if (e.target.classList.contains("copy-btn")) {
        this.copyToClipboard(e.target.dataset.content);
      }

      if (e.target.classList.contains("delete-btn")) {
        this.deleteGeneration(e.target.dataset.id);
      }
    });
  }

  initializeComponents() {
    // Initialize any components that need setup
    this.updateCreditsDisplay();
  }

  async handleLogin(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const submitBtn = e.target.querySelector('button[type="submit"]');

    this.setLoading(submitBtn, true);

    try {
      const response = await fetch("../includes/auth.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        window.location.href = "dashboard.php";
      } else {
        this.showAlert(result.message, "error");
      }
    } catch (error) {
      this.showAlert("Login failed. Please try again.", "error");
    } finally {
      this.setLoading(submitBtn, false);
    }
  }

  async handleSignup(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const password = formData.get("password");
    const confirmPassword = formData.get("confirm_password");

    if (password !== confirmPassword) {
      this.showAlert("Passwords do not match", "error");
      return;
    }

    const submitBtn = e.target.querySelector('button[type="submit"]');
    this.setLoading(submitBtn, true);

    try {
      const response = await fetch("../includes/auth.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        this.showAlert(
          "Account created successfully! Please login.",
          "success"
        );
        setTimeout(() => {
          window.location.href = "login.php";
        }, 2000);
      } else {
        this.showAlert(result.message, "error");
      }
    } catch (error) {
      this.showAlert("Registration failed. Please try again.", "error");
    } finally {
      this.setLoading(submitBtn, false);
    }
  }

  async handleGenerate(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const outputContent = document.getElementById("outputContent");

    this.setLoading(submitBtn, true);
    outputContent.innerHTML =
      '<div class="loading"></div> Generating your content...';

    try {
      const response = await fetch("../includes/generate.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        outputContent.innerHTML = result.content;
        this.updateCreditsDisplay(result.remaining_credits);
        this.showOutputActions(result.generation_id);
      } else {
        outputContent.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
      }
    } catch (error) {
      outputContent.innerHTML =
        '<div class="alert alert-error">Generation failed. Please try again.</div>';
    } finally {
      this.setLoading(submitBtn, false);
    }
  }

  showOutputActions(generationId) {
    const actionsContainer = document.getElementById("outputActions");
    if (actionsContainer) {
      actionsContainer.innerHTML = `
                <button type="button" class="btn btn-secondary copy-btn" data-content="${this.escapeHtml(
                  document.getElementById("outputContent").textContent
                )}">
                    📋 Copy Text
                </button>
                <button type="button" class="btn btn-secondary" onclick="omniSEO.downloadText(${generationId})">
                    💾 Download
                </button>
                <button type="button" class="btn btn-primary" onclick="omniSEO.saveToHistory(${generationId})">
                    📚 Save to History
                </button>
            `;
    }
  }

  async copyToClipboard(text) {
    try {
      await navigator.clipboard.writeText(text);
      this.showAlert("Copied to clipboard!", "success");
    } catch (error) {
      // Fallback for older browsers
      const textArea = document.createElement("textarea");
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand("copy");
      document.body.removeChild(textArea);
      this.showAlert("Copied to clipboard!", "success");
    }
  }

  async downloadText(generationId) {
    try {
      const response = await fetch(
        `../includes/download.php?id=${generationId}`
      );
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = `omniseo-content-${generationId}.txt`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    } catch (error) {
      this.showAlert("Download failed", "error");
    }
  }

  async deleteGeneration(id) {
    if (!confirm("Are you sure you want to delete this generation?")) {
      return;
    }

    try {
      const response = await fetch("../includes/delete.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id: id }),
      });

      const result = await response.json();

      if (result.success) {
        document.querySelector(`[data-id="${id}"]`).closest("tr").remove();
        this.showAlert("Generation deleted successfully", "success");
      } else {
        this.showAlert("Delete failed", "error");
      }
    } catch (error) {
      this.showAlert("Delete failed", "error");
    }
  }

  updateCreditsDisplay(credits = null) {
    const creditsDisplay = document.getElementById("creditsDisplay");
    if (creditsDisplay && credits !== null) {
      creditsDisplay.textContent = `${credits} Credits`;
    }
  }

  setLoading(button, loading) {
    if (loading) {
      button.disabled = true;
      button.innerHTML = '<span class="loading"></span> Processing...';
    } else {
      button.disabled = false;
      button.innerHTML = button.dataset.originalText || "Submit";
    }
  }

  showAlert(message, type = "info") {
    const alertContainer =
      document.getElementById("alertContainer") || this.createAlertContainer();
    const alert = document.createElement("div");
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    alertContainer.appendChild(alert);

    setTimeout(() => {
      alert.remove();
    }, 5000);
  }

  createAlertContainer() {
    const container = document.createElement("div");
    container.id = "alertContainer";
    container.style.position = "fixed";
    container.style.top = "20px";
    container.style.right = "20px";
    container.style.zIndex = "9999";
    document.body.appendChild(container);
    return container;
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
  window.omniSEO = new OmniSEO();
});

// Utility functions
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString() + " " + date.toLocaleTimeString();
}

function truncateText(text, maxLength = 100) {
  if (text.length <= maxLength) return text;
  return text.substr(0, maxLength) + "...";
}

// Button loader toggle
function btnLoader(btn, state = true) {
  if (state) {
    if (!btn.dataset.text) {
      btn.dataset.text = btn.innerHTML;
    }
    btn.classList.add("loading-btn");
    btn.innerHTML = "<span class='loading'></span>";
    btn.disabled = true;
  } else {
    btn.classList.remove("loading-btn");
    btn.innerHTML = btn.dataset.text;
    btn.disabled = false;
  }
}

