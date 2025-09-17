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

