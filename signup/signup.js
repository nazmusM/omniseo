const signUpBtn = document.querySelector(".sign-up");
const form = document.querySelector(".auth-form");

signUpBtn.addEventListener("click", function (e) {
  e.preventDefault();

  const name = document.querySelector("input[name='name']");
  const email = document.querySelector("input[name='email']");
  const password = document.querySelector("input[name='password']");
  const confirmPassword = document.querySelector("input[name='confirm_password']");
  
  // Validate inputs
  if(name.value.trim() === "" || email.value.trim() === "" || 
     password.value === "" || confirmPassword.value === "") {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'All fields are required!',
      confirmButtonColor: '#4361ee'
    });
    return;
  }
  
  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email.value)) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid Email',
      text: 'Please enter a valid email address!',
      confirmButtonColor: '#4361ee'
    });
    return;
  }
  
  // Validate password length
  if (password.value.length < 8) {
    Swal.fire({
      icon: 'error',
      title: 'Weak Password',
      text: 'Password must be at least 8 characters long!',
      confirmButtonColor: '#4361ee'
    });
    return;
  }
  
  // Validate password match
  if (password.value !== confirmPassword.value) {
    Swal.fire({
      icon: 'error',
      title: 'Password Mismatch',
      text: 'Passwords do not match!',
      confirmButtonColor: '#4361ee'
    });
    return;
  }
  
  // Show loading indicator
  btnLoader(this, true);
  
  // Create form data
  const formData = new FormData(form);
  formData.append('action', 'register');
  
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
      // Registration successful
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: data.message || 'Your account has been created successfully!',
        confirmButtonColor: '#4361ee'
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirect to login page or dashboard
          window.location.href = data.redirect || '../login';
        }
      });
    } else {
      // Registration failed
      Swal.fire({
        icon: 'error',
        title: 'Registration Failed',
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