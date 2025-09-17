// article-generator.js

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

document.addEventListener("DOMContentLoaded", function () {
  // Tab navigation
  const tabButtons = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const tabId = button.getAttribute("data-tab");

      // Update active tab button
      tabButtons.forEach((btn) => btn.classList.remove("active"));
      button.classList.add("active");

      // Show active tab content
      tabContents.forEach((content) => {
        content.classList.remove("active");
        if (content.id === `${tabId}-tab`) {
          content.classList.add("active");
        }
      });
    });
  });

  // Keyword generation buttons
  const generateKeywordsBtn = document.createElement("button");
  generateKeywordsBtn.type = "button";
  generateKeywordsBtn.className = "btn btn-primary small-width";
  generateKeywordsBtn.innerHTML = "Generate Keywords";
  generateKeywordsBtn.style.marginTop = "0.5rem";

  // Add keyword generation button to single article form
  const singleKeywordsContainer =
    document.getElementById("single-keywords").parentNode;
  singleKeywordsContainer.appendChild(generateKeywordsBtn.cloneNode(true));

  // Add keyword generation button to bulk form
  const bulkKeywordsContainer =
    document.getElementById("bulk-keywords").parentNode;
  bulkKeywordsContainer.appendChild(generateKeywordsBtn.cloneNode(true));

  // Add event listeners to keyword generation buttons
  document.querySelectorAll(".btn-primary").forEach((btn) => {
    if (btn.textContent === "Generate Keywords") {
      btn.addEventListener("click", function () {
        const form = this.closest("form");
        let topicInput, keywordsInput;

        if (form.id === "single-article-form") {
          topicInput = document.getElementById("single-topic");
          keywordsInput = document.getElementById("single-keywords");
        } else {
          topicInput = document.getElementById("bulk-topic");
          keywordsInput = document.getElementById("bulk-keywords");
        }

        if (!topicInput.value.trim()) {
          showMessage("Please enter a topic first", "error");
          return;
        }

        generateKeywords(topicInput.value, keywordsInput, this);
      });
    }
  });

  // Single article form submission
  const singleArticleForm = document.getElementById("single-article-form");
  singleArticleForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Get form data
    const topic = document.getElementById("single-topic").value;
    const keywords = document.getElementById("single-keywords").value;
    const instructions = document.getElementById("single-instructions").value;
    const generateBtn = document.getElementById("generate-single-btn");

    // Call autopilot API
    const data = await callAutopilot(
      "generateArticle",
      {
        topic: topic,
        keywords: keywords,
        instructions: instructions,
        settings: getGenerationSettings(),
      },
      generateBtn,
      "single"
    );

    if (data.success) {
      // Show preview
      const previewSection = document.getElementById("single-preview-section");
      const previewTitle = document.getElementById("preview-title");
      const editBtn = document.getElementById("preview-edit-btn");
      const viewBtn = document.getElementById("preview-view-btn");

      previewTitle.textContent = data.title || topic;
      editBtn.href = `../article/?id=${data.article_id}&edit=true`;
      viewBtn.href = `../article/?id=${data.article_id}`;

      previewSection.style.display = "block";
      showMessage("Article generated successfully!", "success");
    } else {
      showMessage(data.message || "Failed to generate article", "error");
    }
  });

  // Title generation form submission
  const titleGenerationForm = document.getElementById("title-generation-form");
  titleGenerationForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Get form data
    const topic = document.getElementById("bulk-topic").value;
    const keywords = document.getElementById("bulk-keywords").value;
    const count = document.getElementById("title-count").value;
    const generateBtn = document.getElementById("generate-titles-btn");

    // Call autopilot API
    const data = await callAutopilot(
      "generateTitles",
      {
        topic: topic,
        keywords: keywords,
        count: count,
      },
      generateBtn,
      "titles"
    );

    if (data.success) {
      displayGeneratedTitles(data.titles);
      document.getElementById("step-2").style.display = "block";
      showMessage(
        `${data.titles.length} titles generated successfully!`,
        "success"
      );
    } else {
      showMessage(data.message || "Failed to generate titles", "error");
    }
  });

  // Bulk articles generation
  const generateBulkBtn = document.getElementById("generate-bulk-articles-btn");
  generateBulkBtn.addEventListener("click", async function () {
    // Get all titles
    const titleInputs = document.querySelectorAll(".title-input");
    const titles = Array.from(titleInputs).map((input) => input);

    if (titles.length === 0) {
      showMessage("Please generate titles first", "error");
      return;
    }

    const articlesList = document.getElementById("bulk-articles-list");
    articlesList.innerHTML = "";
    const previewSection = document.getElementById("bulk-preview-section");

    let successCount = 0;

    for (let i = 0; i < titles.length; i++) {
      const title = titles[i];
      addSpinner(title, true);
      title.disabled = true; // Disable input during generation
      title.nextElementSibling.remove();
      document
        .querySelectorAll(".btn-primary")
        .forEach((btn) => (btn.disabled = true)); // Disable all buttons during generation

      try {
        // Call autopilot API
        const data = await callAutopilot(
          "generateArticle",
          {
            topic: title.value,
            settings: getGenerationSettings(),
          },
          this,
          "bulk"
        );

        addSpinner(title, false);
        addTick(title, true);
        document
          .querySelectorAll(".btn-primary")
          .forEach((btn) => (btn.disabled = false)); // Re-enable buttons after generation

        if (data.success) {
          previewSection.style.display = "block";
          successCount++;

          // Add to preview
          const articleDiv = document.createElement("div");
          articleDiv.className = "article-item";
          articleDiv.innerHTML = `
          <h4>${data.title}</h4>
          <div class="article-actions">
            <a href="../article/?id=${data.article_id}&edit=true" class="btn btn-secondary btn-sm">Edit</a>
            <a href="../article/?id=${data.article_id}" class="btn btn-primary btn-sm">View</a>
          </div>
        `;
          articlesList.appendChild(articleDiv);
        } else {
          console.warn(
            `Failed to generate article for "${title}": ${data.message}`
          );
        }

        // Optional: show progress to user
        Toast.fire({
          icon: "info",
          title: `Generated ${i + 1}/${titles.length} articles...`,
        });
      } catch (err) {
        console.error("Error generating article:", err);
      }
    }

    Toast.fire({
      icon: "success",
      title: `${successCount}/${titles.length} articles generated successfully!`,
    });
  });

  // Update cost when image generation is toggled
  const includeImages = document.getElementById("include-images");
  const articleCost = document.getElementById("article-cost");

  includeImages.addEventListener("change", function () {
    articleCost.textContent = this.checked ? "15" : "10";
  });
});

// Unified function to call autopilot API
async function callAutopilot(action, data, btn, type = "single") {
  btnLoader(btn, true);

  try {
    const response = await fetch("../api/autowriter.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        type: type,
        action: action,
        ...data,
      }),
    });

    return await response.json();
    // console.log(await response.text())
  } catch (error) {
    console.error(`Error with ${action}:`, error);
    showMessage(`An error occurred while processing your request.`, "error");
    return { success: false, message: "Network error" };
  } finally {
    btnLoader(btn, false);
  }
}

// Generate keywords function
async function generateKeywords(topic, keywordsInput, btn) {
  const data = await callAutopilot(
    "generateKeywords",
    { topic: topic },
    btn,
    "keywords"
  );
  if (data.success && data.keywords) {
    keywordsInput.value = data.keywords;
  } else {
    showMessage(data.message || "Failed to generate keywords", "error");
  }
}

// Display generated titles in bulk generation
function displayGeneratedTitles(titles) {
  const titlesContainer = document.getElementById("generated-titles");
  titlesContainer.innerHTML = "";

  titles.forEach((title, index) => {
    const titleDiv = document.createElement("div");
    titleDiv.className = "title-item";
    titleDiv.innerHTML = `
      <input type="text" class="title-input" value="${title}" data-id="${index}">
      <button class="btn btn-danger btn-sm delete-title">Delete</button>
    `;
    titlesContainer.appendChild(titleDiv);
  });

  // Add delete functionality
  const deleteButtons = titlesContainer.querySelectorAll(".delete-title");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      this.parentElement.remove();
    });
  });
}

// Get generation settings
function getGenerationSettings() {
  return {
    language: document.getElementById("output-language").value,
    tone: document.getElementById("writing-tone").value,
    length: document.getElementById("article-length").value,
    projectId: getProjectId(),
    pointOfView: document.getElementById("point-of-view").value,
    boldItalic: document.getElementById("bold-italic").value,
    faq: document.getElementById("faq").value,
    keyTakeaways: document.getElementById("key-takeaways").value,
    externalLinks: document.getElementById("external-links").value,
    includeImages: document.getElementById("include-images").checked,
    publishToWordpress: document.getElementById("publish-to-wordpress").value,
    publishStatus: document.getElementById("publish-status").value,
  };
}

// Show message function
function showMessage(message, type) {
  Swal.fire({
    title: type == "error" ? "Error" : "Success",
    text: message,
    icon: type,
  });
}

// Simple approach for getting a specific parameter
function getProjectId() {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  return urlParams.get("project_id");
}

function addSpinner(element, show) {
  const el = document.createElement("span");
  el.className = "loading";
  if (show) {
    element.parentNode.insertBefore(el, element);
  } else {
    const spinner = element.parentNode.querySelector(".loading");
    if (spinner) {
      spinner.remove();
    }
  }
}

function addTick(element, show) {
  const el = document.createElement("span");
  el.className = "tick";
  if (show) {
    element.parentNode.insertBefore(el, element);
  } else {
    const tick = element.parentNode.querySelector(".tick");
    if (tick) {
      tick.remove();
    }
  }
}
