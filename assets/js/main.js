// Common functions
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

//Show alert using SweetAlert2
function showAlert(type, message) {
  return Swal.fire({
    title: type === "success" ? "Success" : "Error",
    text: message,
    icon: type,
    confirmButtonText: "OK",
  });
}

//showWarning
function showWarning(message) {
  return Swal.fire({
    title: "Are you sure?",
    text: message,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  });
}

//showToast
function showToast(type = "info", message) {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    },
  });

return  Toast.fire({
    icon: type,
    title: message,
  });
}

//Modal functions

const customModal = document.getElementById("modal");
const closeModalBtn = document.getElementById("modalCloseBtn");
const cancelBtn = document.getElementById("cancelBtn");

function openModal() {
  customModal.style.display = "flex";
  document.body.style.overflow = "hidden"; // Prevent scrolling
}

// Function to close modal
function closeModal() {
  customModal.style.display = "none";
  document.body.style.overflow = "auto"; // Allow scrolling
}

// Close modal when clicking outside
window.onclick = function (event) {
  if (event.target === customModal) {
    closeModal();
  }
};

//Dashboard functions
document.addEventListener("DOMContentLoaded", () => {
  // Smooth animations for stat cards
  const statCards = document.querySelectorAll(".stat-card");
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  });

  statCards.forEach((card) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(20px)";
    card.style.transition = "opacity 0.6s ease, transform 0.6s ease";
    observer.observe(card);
  });
});

//Projects page functions
document.addEventListener("DOMContentLoaded", function () {
  // Get modal elements
  const createProjectBtn = document.getElementById("createProjectBtn");
  const createProjectBtnEmpty = document.getElementById(
    "createProjectBtnEmpty"
  );

  // Event listeners for opening modal
  if (createProjectBtn) {
    createProjectBtn.addEventListener("click", openModal);
  }

  if (createProjectBtnEmpty) {
    createProjectBtnEmpty.addEventListener("click", openModal);
  }

  // Event listeners for closing modal
  if (closeModalBtn) {
    cancelBtn.addEventListener("click", closeModal);
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", closeModal);
  }

  // Form validation
  const projectForm = document.querySelector(".modal-form");
  if (projectForm) {
    projectForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const projectName = document.getElementById("project_name").value.trim();
      const wpUrl = document.getElementById("wp_url").value.trim();
      const submitBtn = document.querySelector(".create-btn");

      if (!projectName) {
        showAlert("error", "Please enter a project name");
        return;
      }

      if (!wpUrl) {
        showAlert("error", "Please enter a WordPress URL");
        return;
      }

      // Basic URL validation
      try {
        new URL(wpUrl);
      } catch (e) {
        showAlert("error", "Please enter a valid WordPress URL");
        return;
      }

      btnLoader(submitBtn, true);

      try {
        const response = await fetch("../api/autowriter.php", {
          method: "POST",
          body: JSON.stringify({
            action: "createProject",
            project_name: projectName,
            wp_url: wpUrl,
          }),
        });

        if (!response.ok) {
          showAlert("error", "Failed to create project. Please try again.");
          btnLoader(submitBtn, false);
          return;
        }

        const data = await response.json();
        btnLoader(submitBtn, false);
        if (data.success) {
          showAlert("success", "Project created successfully!").then(
            (result) => {
              if (result.isConfirmed) {
                closeModal();
                location.reload(); // Reload the page to show the new project
              }
            }
          );
        } else {
          showAlert(
            "error",
            data.message || "Failed to create project. Please try again."
          );
        }
      } catch (error) {
        showAlert(
          "error",
          "An error occurred while creating the project. Please try again."
        );
      } finally {
        btnLoader(submitBtn, false);
      }
    });
  }
});

// Function to delete a project
async function deleteProject(id) {
  showWarning("You are going to delete this project").then(async (result) => {
    if (result.isConfirmed) {
      try {
        const formData = new FormData();
        formData.append("action", "deleteProject");
        formData.append("id", id);

        const response = await fetch("../api/delete.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          showAlert("success", "Project deleted successfully!").then(
            (result) => {
              if (result.isConfirmed) {
                location.reload();
              }
            }
          );
        } else {
          showAlert(
            "error",
            result.message || "Failed to delete project. Please try again."
          );
          return;
        }
      } catch (error) {
        console.error("Error:", error);
        showAlert(
          "error",
          "An error occurred while deleting the project. Please try again."
        );
      }
    }
  });
}

// Function to delete a post

async function deleteArticle(id) {
  showWarning("You are about to delete this article.").then(async (result) => {
    if (result.isConfirmed) {
      try {
        const formData = new FormData();
        formData.append("action", "deleteArticle");
        formData.append("id", id);

        const response = await fetch("../api/delete.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          showAlert("success", "Article deleted successfully!").then(
            (result) => {
              if (result.isConfirmed) {
                location.reload();
              }
            }
          );
        } else {
          showAlert(
            "error",
            result.message || "Failed to delete article. Please try again."
          );
        }
      } catch (error) {
        console.error("Error:", error);
        alert("An error occurred while deleting the item");
      }
    }
  });
}

//My websites page functions
document
  .getElementById("addWebsiteForm")
  ?.addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');

    // Show loading state
    btnLoader(submitBtn, true);

    try {
      const response = await fetch("../api/website.php?action=add", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();
      if (result.success) {
        showAlert("success", "Website added successfully!").then((result) => {
          if (result.isConfirmed) {
            window.location.reload();
          }
        });
      } else {
        showAlert("error", result.error || "Failed to add website");
      }
    } catch (error) {
      showAlert("error", "Network error. Please try again.");
    } finally {
      // Restore button state
      btnLoader(submitBtn, false);
    }
  });

//Sync websites
async function syncWebsite(websiteId) {
  const syncBtn = document.querySelector(
    `.website-card[data-id="${websiteId}"] .btn-icon`
  );
  syncBtn.classList.add("loading");

  const formData = new FormData();
  formData.append("website_id", websiteId);

  try {
    const response = await fetch("../api/website.php?action=sync", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      showToast("success", "Website synced successfully!");
      // Update the UI with new sync time
      const lastSyncEl = document.querySelector(
        `.website-card[data-id="${websiteId}"] .last-sync`
      );
      const syncStatus = document.querySelector(
        `.website-card[data-id="${websiteId}"] .status-badge`
      );
      if (lastSyncEl && syncStatus) {
        lastSyncEl.textContent = "Last sync: Just now";
        syncStatus.textContent = "Connected";
        syncStatus.classList.remove("status-pending");
        syncStatus.classList.add("status-connected");
      }
    } else {
      showToast("error", result.error || "Failed to sync website");
    }
  } catch (error) {
    showToast("error", "Network error. Please try again.");
  } finally {
    // Restore button state
    syncBtn.classList.remove("loading");
  }
}

//Delete website function
async function deleteWebsite(websiteId) {
  showWarning(
    "You are about to delete this website. This action cannot be undone."
  ).then(async (result) => {
    if (result.isConfirmed) {
      // Disable the card to prevent multiple clicks

      const websiteCard = document.querySelector(
        `.website-card[data-id="${websiteId}"]`
      );
      websiteCard.style.opacity = "0.5";
      websiteCard.style.pointerEvents = "none";

      const formData = new FormData();
      formData.append("website_id", websiteId);

      try {
        const response = await fetch("../api/website.php?action=delete", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          showAlert("Website deleted successfully!", "success");
          // Remove the card from UI
          websiteCard.remove();

          // Check if no websites left
          const remainingCards = document.querySelectorAll(".website-card");
          if (remainingCards.length === 0) {
            window.location.reload();
          }
        } else {
          showAlert(result.error || "Failed to delete website", "error");
          websiteCard.style.opacity = "1";
          websiteCard.style.pointerEvents = "auto";
        }
      } catch (error) {
        showAlert("Network error. Please try again.", "error");
        console.error("Error:", error);
        websiteCard.style.opacity = "1";
        websiteCard.style.pointerEvents = "auto";
      }
    }
  });
}

//Account page functions
document.addEventListener("DOMContentLoaded", () => {
  // Update profile
  const updateProfile = document.querySelector(".update-profile");
  const profileForm = document.querySelector(".profile-form");
  const nameField = document.querySelector("input[name='name']");
  const emailField = document.querySelector("input[name='email']");

  // Update password
  const updatePassword = document.querySelector(".update-password");
  const passwordForm = document.querySelector(".password-form");
  const currentPassword = document.querySelector(
    "input[name='current_password']"
  );
  const newPassword = document.querySelector("input[name='new_password']");
  const confirmPassword = document.querySelector(
    "input[name='confirm_password']"
  );

  //Funtion to update profile
  updateProfile?.addEventListener("click", (e) => {
    e.preventDefault();
    if (nameField.value.trim() == "" || emailField.value.trim() == "") {
      showAlert("error", "All fields must be filled");
      return;
    }

    // Show loading indicator
    btnLoader(updateProfile, true);

    // Create form data
    const formData = new FormData(profileForm);
    formData.append("action", "profile");
    formData.append("subaction", "profile_info");

    // Send request to auth.php
    fetch("../api/auth.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        // Close loading indicator
        btnLoader(updateProfile, false);
        if (data.success) {
          showAlert("success", data.message).then((result) => {
            if (result.isConfirmed) {
              window.location.reload();
            }
          });
        } else {
          showAlert("error", data.message || "Failed to update profile");
        }
      })
      .catch((error) => {
        // Close loading indicator
        btnLoader(updateProfile, false);

        // Show error message
        showAlert("error", "An error occurred: " + error.message);
        console.error("Error:", error);
      });
  });

  //Function to update password
  updatePassword?.addEventListener("click", (e) => {
    e.preventDefault();
    if (
      currentPassword.value.trim() == "" ||
      newPassword.value.trim() == "" ||
      confirmPassword.value.trim() == ""
    ) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "All fields must be filled",
        confirmButtonColor: "#4361ee",
      });
      return;
    }

    if (newPassword.value !== confirmPassword.value) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Passwords does not match",
        confirmButtonColor: "#4361ee",
      });
      return;
    }

    // Show loading indicator
    btnLoader(updatePassword, true);

    // Create form data
    const formData = new FormData(passwordForm);
    formData.append("action", "profile");
    formData.append("subaction", "password");

    // Send request to auth.php
    fetch("../api/auth.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        // Close loading indicator
        btnLoader(updatePassword, false);
        if (data.success) {
          // Registration successful
          showAlert(
            "success",
            data.message || "Password changed successfully!"
          ).then((result) => {
            if (result.isConfirmed) {
              passwordForm.reset(); // Reset the form
            }
          });
        } else {
          // Failed to change password
          showAlert("error", "Failed to change password");
        }
      })
      .catch((error) => {
        // Close loading indicator
        btnLoader(updatePassword, false);

        // Show error message
        showAlert("error", "An error occurred: " + error.message);
      });
  });
});

document.querySelectorAll(".toggle-password").forEach((toggle) => {
  toggle.addEventListener("click", () => {
    const input = toggle.previousElementSibling;
    const eye = toggle.querySelector(".eye");
    const eyeSlash = toggle.querySelector(".eye-slash");

    if (input.type === "password") {
      input.type = "text";
      eye.style.display = "none";
      eyeSlash.style.display = "inline";
    } else {
      input.type = "password";
      eye.style.display = "inline";
      eyeSlash.style.display = "none";
    }
  });
});

//Login page functions
const loginBtn = document.querySelector(".login-btn");
const loginform = document.querySelector(".auth-form");

loginBtn?.addEventListener("click", function (e) {
  e.preventDefault();

  const email = document.querySelector("input[name='email']");
  const password = document.querySelector("input[name='password']");

  // Validate inputs
  if (email.value.trim() === "" || password.value === "") {
    showAlert("error", "Please fill in all fields.");
    return;
  }

  // Show loading indicator
  btnLoader(this, true);

  // Create form data
  const formData = new FormData(loginform);
  formData.append("action", "login");

  // Send request to auth.php
  fetch("../api/auth.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => {
      // Close loading indicator
      btnLoader(this, false);
      if (data.success) {
        showToast("success", "Logged in successfully").then(() => {
          window.location.href = data.redirect || "../dashboard";
        });
      } else {
        // Login failed
        showAlert("error", data.message || "Failed to login");
      }
    })
    .catch((error) => {
      // Close loading indicator
      btnLoader(this, false);

      // Show error message
      showAlert("error", "An error occurred: " + error.message);
    });
});
