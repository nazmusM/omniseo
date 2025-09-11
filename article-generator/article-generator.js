document.addEventListener("DOMContentLoaded", () => {
  // Tab functionality
  const tabBtns = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");

  tabBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const tabId = this.dataset.tab;

      // Remove active class from all tabs and contents
      tabBtns.forEach((b) => b.classList.remove("active"));
      tabContents.forEach((c) => c.classList.remove("active"));

      // Add active class to clicked tab and corresponding content
      this.classList.add("active");
      document.getElementById(tabId + "-tab").classList.add("active");
    });
  });

  // Single article form
  const singleForm = document.getElementById("single-article-form");
  const generateSingleBtn = document.getElementById("generate-single-btn");

  singleForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const topic = document.getElementById("single-topic").value;
    const keywords = document.getElementById("single-keywords").value;
    const instructions = document.getElementById("single-instructions").value;

    if (!topic.trim()) {
      showMessage("Please enter a topic for your article.", "error");
      return;
    }

    await generateSingleArticle(topic, keywords, instructions);
  });



  // Title generation form
  const titleForm = document.getElementById("title-generation-form");
  const generateTitlesBtn = document.getElementById("generate-titles-btn");

  titleForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const topic = document.getElementById("bulk-topic").value;
    const keywords = document.getElementById("bulk-keywords").value;
    const count = document.getElementById("title-count").value;

    if (!topic.trim()) {
      showMessage("Please enter a topic for title generation.", "error");
      return;
    }

    await generateTitles(topic, keywords, count);
  });

  // Bulk article generation
  const generateBulkBtn = document.getElementById("generate-bulk-articles-btn");
  generateBulkBtn.addEventListener("click", async () => {
    const selectedTitles = getSelectedTitles();

    if (selectedTitles.length === 0) {
      showMessage(
        "Please select at least one title to generate articles.",
        "error"
      );
      return;
    }

    await generateBulkArticles(selectedTitles);
  });

  // Functions
  async function generateSingleArticle(topic, keywords, instructions) {
    setButtonLoading(generateSingleBtn, true);

    try {
      const settings = getGenerationSettings();
      const response = await fetch("../api/generate-article.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          type: "single",
          topic: topic,
          keywords: keywords,
          instructions: instructions,
          settings: settings,
        }),
      });

      const data = await response.json();

      if (data.success) {
        showPreview(data.article, data.title);
        showMessage("Article generated successfully!", "success");
      } else {
        showMessage(data.message || "Failed to generate article.", "error");
      }
    } catch (error) {
      console.error("[v0] Error generating article:", error);
      showMessage("An error occurred while generating the article.", "error");
    } finally {
      setButtonLoading(generateSingleBtn, false);
    }
  }

  async function generateTitles(topic, keywords, count) {
    setButtonLoading(generateTitlesBtn, true);

    try {
      const response = await fetch("../api/generate-titles.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          topic: topic,
          keywords: keywords,
          count: Number.parseInt(count),
        }),
      });

      const data = await response.json();

      if (data.success) {
        displayGeneratedTitles(data.titles);
        document.getElementById("step-2").style.display = "block";
        showMessage(
          `${data.titles.length} titles generated successfully!`,
          "success"
        );
      } else {
        showMessage(data.message || "Failed to generate titles.", "error");
      }
    } catch (error) {
      console.error("[v0] Error generating titles:", error);
      showMessage("An error occurred while generating titles.", "error");
    } finally {
      setButtonLoading(generateTitlesBtn, false);
    }
  }

  async function generateBulkArticles(titles) {
    setButtonLoading(generateBulkBtn, true);

    try {
      const settings = getGenerationSettings();
      const response = await fetch("../api/generate-bulk-articles.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          titles: titles,
          settings: settings,
        }),
      });

      const data = await response.json();

      if (data.success) {
        showMessage(
          `${data.generated} articles generated successfully!`,
          "success"
        );
        // Redirect to history page to view generated articles
        setTimeout(() => {
          window.location.href = "../history";
        }, 2000);
      } else {
        showMessage(data.message || "Failed to generate articles.", "error");
      }
    } catch (error) {
      console.error("[v0] Error generating bulk articles:", error);
      showMessage("An error occurred while generating articles.", "error");
    } finally {
      setButtonLoading(generateBulkBtn, false);
    }
  }

  function displayGeneratedTitles(titles) {
    const container = document.getElementById("generated-titles");
    container.innerHTML = "";

    titles.forEach((title, index) => {
      const titleItem = document.createElement("div");
      titleItem.className = "title-item";
      titleItem.innerHTML = `
                <input type="checkbox" id="title-${index}" value="${title}" checked>
                <label for="title-${index}" class="title-text">${title}</label>
            `;
      container.appendChild(titleItem);
    });

    document.getElementById("generate-bulk-articles-btn").style.display =
      "block";

    // Add event listeners to checkboxes
    const checkboxes = container.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", updateBulkButtonState);
    });

    updateBulkButtonState();
  }

  function getSelectedTitles() {
    const checkboxes = document.querySelectorAll(
      '#generated-titles input[type="checkbox"]:checked'
    );
    return Array.from(checkboxes).map((cb) => cb.value);
  }

  function updateBulkButtonState() {
    const selectedCount = getSelectedTitles().length;
    const bulkBtn = document.getElementById("generate-bulk-articles-btn");
    const btnText = bulkBtn.querySelector(".btn-text");

    if (selectedCount > 0) {
      btnText.textContent = `Generate ${selectedCount} Article${
        selectedCount > 1 ? "s" : ""
      }`;
      bulkBtn.disabled = false;
    } else {
      btnText.textContent = "Generate Selected Articles";
      bulkBtn.disabled = true;
    }
  }

  function getGenerationSettings() {
    return {
      language: document.getElementById("output-language").value,
      tone: document.getElementById("writing-tone").value,
      length: document.getElementById("article-length").value,
      includeIntro: document.getElementById("include-intro").checked,
      includeConclusion: document.getElementById("include-conclusion").checked,
      includeHeadings: document.getElementById("include-headings").checked,
      includeMeta: document.getElementById("include-meta").checked,
      autoPublish: document.getElementById("auto-publish").checked,
    };
  }

  function showPreview(content, title) {
    document.getElementById("preview-content").innerHTML = `
            <h1>${title}</h1>
            ${content}
        `;
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  function closeModal() {
    modal.classList.remove("active");
    document.body.style.overflow = "auto";
  }

  async function saveCurrentArticle() {
    const content = document.getElementById("preview-content").innerHTML;
    const title = document.querySelector("#preview-content h1").textContent;

    try {
      const response = await fetch("../api/save-article.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          title: title,
          content: content,
        }),
      });

      const data = await response.json();

      if (data.success) {
        showMessage("Article saved successfully!", "success");
        closeModal();
      } else {
        showMessage(data.message || "Failed to save article.", "error");
      }
    } catch (error) {
      console.error("[v0] Error saving article:", error);
      showMessage("An error occurred while saving the article.", "error");
    }
  }

  function setButtonLoading(button, loading) {
    const btnText = button.querySelector(".btn-text");
    const loadingSpinner = button.querySelector(".loading");

    if (loading) {
      btnText.style.display = "none";
      loadingSpinner.style.display = "inline-block";
      button.disabled = true;
    } else {
      btnText.style.display = "inline";
      loadingSpinner.style.display = "none";
      button.disabled = false;
    }
  }

  function showMessage(message, type) {
    Swal.fire({
      title: type == "error" ? "Error" : "Success",
      text: message,
      icon: type,
    });
  }
});
