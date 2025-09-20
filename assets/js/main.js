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

  return Toast.fire({
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

async function postRequest(
  url,
  formData,
  btn = null,
  reload = false,
  redirect = null
) {
  console.log("Fetch function called with URL:", formData);
  if (btn !== null) btnLoader(btn, true);
  try {
    const response = await fetch(url, {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      showAlert("error", "Failed to connect. Please try again.");
      btnLoader(btn, false);
      return;
    }

    const data = await response.json();
    btnLoader(btn, false);
    if (data.success) {
      showAlert("success", data.message).then((result) => {
        if (result.isConfirmed) {
          if (reload) location.reload();
          if (redirect !== null) {
            window.location.href = redirect;
          }
        }
      });
    } else {
      showAlert("error", data.message);
    }
  } catch (error) {
    showAlert("error", "An error occurred. Please try again.");
  } finally {
    btnLoader(btn, false);
  }
}

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

  //Projects page functions
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
      const formData = new FormData(projectForm);

      await postRequest("../api/project.php", formData, submitBtn, true);
    });
  }

  // Function to delete a project
  async function deleteProject(id) {
    showWarning("You are going to delete this project").then(async (result) => {
      if (result.isConfirmed) {
        const formData = new FormData();
        formData.append("action", "deleteProject");
        formData.append("id", id);
        await postRequest("../api/delete.php", formData, null, true);
      }
    });
  }

  // Function to delete a post

  async function deleteArticle(id) {
    showWarning("You are about to delete this article.").then(
      async (result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append("action", "deleteArticle");
          formData.append("id", id);
          await postRequest("../api/delete.php", formData, null, true);
        }
      }
    );
  }

  //My websites page functions
  document
    .getElementById("addWebsiteForm")
    ?.addEventListener("submit", async function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      await postRequest(
        "../api/website.php?action=add",
        formData,
        submitBtn,
        true
      );
    });

  //Sync websites
  async function syncWebsite(websiteId) {
    const syncBtn = document.querySelector(
      `.website-card[data-id="${websiteId}"] .btn-icon`
    );
    syncBtn.classList.add("loading");

    const formData = new FormData();
    formData.append("website_id", websiteId);

    await postRequest("../api/website.php?action=sync", formData, null);
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

    syncBtn.classList.remove("loading");
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

        postRequest("../api/website.php?action=delete", formData, null);
        // Remove the card from UI
        websiteCard.remove();

        // Check if no websites left
        const remainingCards = document.querySelectorAll(".website-card");
        if (remainingCards.length === 0) {
          window.location.reload();
        }
        websiteCard.style.opacity = "1";
        websiteCard.style.pointerEvents = "auto";
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
    updateProfile?.addEventListener("click", async (e) => {
      e.preventDefault();
      if (nameField.value.trim() == "" || emailField.value.trim() == "") {
        showAlert("error", "All fields must be filled");
        return;
      }

      // Create form data
      const formData = new FormData(profileForm);
      formData.append("action", "profile");
      formData.append("subaction", "profile_info");

      // Send request to auth.php
      await postRequest("../api/auth.php", formData, updateProfile, true);
    });

    //Function to update password
    updatePassword?.addEventListener("click", (e) => {
      e.preventDefault();
      if (
        currentPassword.value.trim() == "" ||
        newPassword.value.trim() == "" ||
        confirmPassword.value.trim() == ""
      ) {
        showAlert("error", "All fields must be filled");
        return;
      }

      if (newPassword.value !== confirmPassword.value) {
        showAlert("error", "Passwords do not match");
        return;
      }

      // Create form data
      const formData = new FormData(passwordForm);
      formData.append("action", "profile");
      formData.append("subaction", "password");

      // Send request to auth.php
      postRequest("../api/auth.php", formData, updatePassword);
      passwordForm.reset();
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
  const loginform = document.querySelector(".login-form");

  loginBtn?.addEventListener("click", async function (e) {
    e.preventDefault();

    const email = document.querySelector("input[name='email']");
    const password = document.querySelector("input[name='password']");

    // Validate inputs
    if (email.value.trim() === "" || password.value === "") {
      showAlert("error", "Please fill in all fields.");
      return;
    }

    // Create form data
    const formData = new FormData(loginform);
    formData.append("action", "login");

    // Send request to auth.php
    await postRequest("../api/auth.php", formData, this, "../dashboard");
  });
});

// Signup page functions
const signUpBtn = document.querySelector(".sign-up");
const form = document.querySelector(".signup-form");

signUpBtn?.addEventListener("click", async function (e) {
  e.preventDefault();

  const name = document.querySelector("input[name='name']");
  const email = document.querySelector("input[name='email']");
  const password = document.querySelector("input[name='password']");
  const confirmPassword = document.querySelector(
    "input[name='confirm_password']"
  );

  // Validate inputs
  if (
    name.value.trim() === "" ||
    email.value.trim() === "" ||
    password.value === "" ||
    confirmPassword.value === ""
  ) {
    showAlert("error", "Please fill in all fields!");
    return;
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email.value)) {
    showAlert("error", "Please enter a valid email address!");
    return;
  }

  // Validate password length
  if (password.value.length < 8) {
    showAlert("error", "Password must be at least 8 characters long!");
    return;
  }

  // Validate password match
  if (password.value !== confirmPassword.value) {
    showAlert("error", "Passwords do not match!");
    return;
  }

  // Create form data
  const formData = new FormData(form);
  formData.append("action", "register");

  // Send request to auth.php
  await postRequest("../api/auth.php", formData, this);
});
