document.addEventListener("DOMContentLoaded", function () {
  // Get modal elements
  const modal = document.getElementById("createProjectModal");
  const createProjectBtn = document.getElementById("createProjectBtn");
  const createProjectBtnEmpty = document.getElementById(
    "createProjectBtnEmpty"
  );
  const closeModalBtn = document.querySelector(".modal-close");
  const cancelProjectBtn = document.getElementById("cancelProject");

  // Function to open modal
  function openModal() {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden"; // Prevent scrolling
  }

  // Function to close modal
  function closeModal() {
    modal.style.display = "none";
    document.body.style.overflow = "auto"; // Allow scrolling
  }

  // Event listeners for opening modal
  if (createProjectBtn) {
    createProjectBtn.addEventListener("click", openModal);
  }

  if (createProjectBtnEmpty) {
    createProjectBtnEmpty.addEventListener("click", openModal);
  }

  // Event listeners for closing modal
  if (closeModalBtn) {
    closeModalBtn.addEventListener("click", closeModal);
  }

  if (cancelProjectBtn) {
    cancelProjectBtn.addEventListener("click", closeModal);
  }

  // Close modal when clicking outside of it
  window.addEventListener("click", function (event) {
    if (event.target === modal) {
      closeModal();
    }
  });

  // Close modal with Escape key
  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modal.style.display === "flex") {
      closeModal();
    }
  });

  // Form validation
  const projectForm = document.querySelector(".modal-form");
  if (projectForm) {
    projectForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const projectName = document.getElementById("project_name").value.trim();
      const wpUrl = document.getElementById("wp_url").value.trim();
      const submitBtn = document.querySelector('.create-btn');

      if (!projectName) {
        Swal.fire({
          title: "error",
          text: "Please enter a project name",
          icon: "error",
        });
        return;
      }

      if (!wpUrl) {
        Swal.fire({
          title: "error",
          text: "Please select a WordPress URL",
          icon: "error",
        });
        return;
      }

      // Basic URL validation
      try {
        new URL(wpUrl);
      } catch (e) {
        Swal.fire({
          title: "error",
          text: "Please enter a valid URL",
          icon: "error",
        });
        return;
      }

      btnLoader(submitBtn, true)

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
          Swal.fire({
            title: "error",
            text: "Network error",
            icon: "error",
          });
        }

        const data = await response.json();
        btnLoader(submitBtn, false)
        if (data.success) {
          Swal.fire({
            title: "success",
            text: data.message,
            icon: "success",
          }).then(window.location.reload());
        } else {
          Swal.fire({
            title: "error",
            text: data.message,
            icon: "error",
          });
        }
      } catch (error) {
        Swal.fire({
          title: "error",
          text: error,
          icon: "error",
        });
      }finally{
        btnLoader(submitBtn, false)
      }
    });
  }
})