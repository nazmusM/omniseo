const loginBtn = document.querySelector(".login-btn");
const form = document.querySelector(".auth-form");

loginBtn.addEventListener("click", function (e) {
  e.preventDefault();

  const email = document.querySelector("input[name='email']");
  const password = document.querySelector("input[name='password']");
  
  // Validate inputs
  if(email.value.trim() === "" || 
     password.value === "") {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'All fields are required!',
      confirmButtonColor: '#4361ee'
    });
    return;
  }
  
  // Show loading indicator
  btnLoader(this, true);
  
  // Create form data
  const formData = new FormData(form);
  formData.append('action', 'login');
  
  // Send request to auth.php
  fetch('../api/auth.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(data => {
    // Close loading indicator
    btnLoader(this, false)
    if (data.success) {
      // Login successful
      const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  }
});
Toast.fire({
  icon: "success",
  title: "Signed in successfully"
}).then(()=>{
    window.location.href = data.redirect || '../dashboard';
});
    } else {
      // Registration failed
      Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: data.message || 'Something went wrong. Please try again.',
        confirmButtonColor: '#4361ee'
      });
    }
  })
  .catch(error => {
    // Close loading indicator
    btnLoader(this, false);
    
    // Show error message
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'An error occurred: ' + error.message,
      confirmButtonColor: '#4361ee'
    });
    console.error('Error:', error);
  });
});

document.querySelectorAll(".toggle-password").forEach(toggle => {
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