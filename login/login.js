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