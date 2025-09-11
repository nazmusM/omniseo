document.addEventListener("DOMContentLoaded", () => {
  // Update profile
  const updateProfile = document.querySelector(".update-profile");
  const profileForm = document.querySelector(".profile-form");
const nameField = document.querySelector("input[name='name']");
const emailField = document.querySelector("input[name='email']");

// Update password
const updatePassword = document.querySelector(".update-password");
  const passwordForm = document.querySelector(".password-form");
const currentPassword = document.querySelector("input[name='current_password']");
const newPassword = document.querySelector("input[name='new_password']");
const confirmPassword = document.querySelector("input[name='confirm_password']");

//Funtion to update profile
updateProfile.addEventListener("click", (e)=>{
  e.preventDefault();
  if(nameField.value.trim() == "" || emailField.value.trim() == ""){
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'All fields must be filled',
      confirmButtonColor: '#4361ee'
    });
    return;
  }

  // Show loading indicator
  btnLoader(updateProfile, true);
  
  // Create form data
  const formData = new FormData(profileForm);
  formData.append('action', 'profile');
  formData.append('subaction', 'profile_info');
  
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
    btnLoader(updateProfile, false)
    if (data.success) {
      // Registration successful
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: data.message || 'Profile updated successfully!',
        confirmButtonColor: '#4361ee'
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirect to login page or dashboard
          window.location.reload();
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
    btnLoader(updateProfile, false);
    
    // Show error message
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'An error occurred: ' + error.message,
      confirmButtonColor: '#4361ee'
    });
    console.error('Error:', error);
  });
})

//Function to update password
updatePassword.addEventListener("click", (e)=>{
  e.preventDefault();
  if(currentPassword.value.trim() == "" || newPassword.value.trim() == "" || confirmPassword.value.trim() == ""){
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'All fields must be filled',
      confirmButtonColor: '#4361ee'
    });
    return;
  }

  if(newPassword.value !== confirmPassword.value){
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Passwords does not match',
      confirmButtonColor: '#4361ee'
    });
    return;
  }

  // Show loading indicator
  btnLoader(updatePassword, true);
  
  // Create form data
  const formData = new FormData(passwordForm);
  formData.append('action', 'profile');
  formData.append('subaction', 'password');
  
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
    btnLoader(updatePassword, false)
    if (data.success) {
      // Registration successful
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: data.message || 'Password changed successfully!',
        confirmButtonColor: '#4361ee'
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirect to login page or dashboard
          passwordForm.reset();
        }
      });
    } else {
      // Failed to change password
      Swal.fire({
        icon: 'error',
        title: 'Password change failed',
        text: data.message || 'Failed to change password. Please try again later.',
        confirmButtonColor: '#4361ee'
      });
    }
  })
  .catch(error => {
    // Close loading indicator
    btnLoader(updatePassword, false);
    
    // Show error message
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'An error occurred: ' + error.message,
      confirmButtonColor: '#4361ee'
    });
    console.error('Error:', error);
  });
})

})


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
