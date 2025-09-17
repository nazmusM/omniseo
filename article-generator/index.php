<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

if (!isset($_GET['project_id'])) {
    header('Location: ../projects');
    exit;
}

// Get and validate project_id
$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);

if (!$project_id || $project_id <= 0) {
    http_response_code(400);
    echo 'Invalid project ID.';
    exit;
}

// Prepare and execute query
$projectStmt = $db->prepare("SELECT id FROM projects WHERE id = ?");

$projectStmt->bind_param('i', $project_id);
if (!$projectStmt->execute()) {
    http_response_code(500);
    echo 'Execution failed: ' . $projectStmt->error;
    exit;
}

$result = $projectStmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo 'No project found with that ID.';
    exit;
}

// Fetch project data
// $project = $result->fetch_assoc();

// Don't forget to close statements
$projectStmt->close();
$result->close();

$user_id = $_SESSION['user_id'];

// Get user credits
$stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_credits = $stmt->get_result()->fetch_assoc()['credits'];
$stmt->close();

$articleStmt = $db->prepare("SELECT id, title FROM articles WHERE project_id = ?");
$articleStmt->bind_param('i', $project_id);
$articleStmt->execute();
$result = $articleStmt->get_result();
$articles = [];
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}


$title = 'Article Generator';
$stylesheet = 'article-generator.css';
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div class="header-content">
                <h1 class="page-title">Article Generator</h1>
                <p class="page-subtitle">Generate high-quality, SEO-optimized articles with AI</p>
            </div>
            <div class="header-btn">
                <a href="#article-view" class="btn btn-primary">All Articles</a>
            </div>
        </div>
    </div>

    <div class="generator-layout">
        <div class="generator-panel">
            <div class="tab-navigation">
                <button class="tab-btn active" data-tab="single">Single Article</button>
                <button class="tab-btn" data-tab="bulk">Bulk Generator</button>
            </div>

            <!-- Single Article Tab -->
            <div class="tab-content active" id="single-tab">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Single Article Generator</h3>
                    </div>
                    <div class="card-content">
                        <form id="single-article-form">
                            <div class="form-group">
                                <label class="form-label">Article Topic/Title <span class="red">*</span></label>
                                <input type="text" class="form-input" id="single-topic" placeholder="Enter your article topic or title" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Keywords (comma-separated) <span class="red">*</span></label>
                                <div class="keyword-input">
                                    <textarea type="text" class="form-input" id="single-keywords" placeholder="SEO, content marketing, digital strategy"></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Additional Instructions (optional)</label>
                                <textarea class="form-input form-textarea" id="single-instructions" placeholder="Any specific requirements or instructions for the article..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary small-width" id="generate-single-btn">
                                Generate Article
                            </button>
                        </form>

                        <!-- Single Article Preview Section -->
                        <div id="single-preview-section" class="preview-section" style="display: none;">
                            <h3>Generated Article</h3>
                            <div class="preview-content">
                                <h4 id="preview-title"></h4>
                                <div class="preview-actions">
                                    <a href="#" id="preview-edit-btn" class="btn btn-secondary">Edit</a>
                                    <a href="#" id="preview-view-btn" class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Generator Tab -->
            <div class="tab-content" id="bulk-tab">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bulk Article Generator</h3>
                    </div>
                    <div class="card-content">
                        <div class="bulk-step" id="step-1">
                            <h4>Step 1: Generate Titles</h4>
                            <form id="title-generation-form">
                                <div class="form-group">
                                    <label class="form-label">Main Topic/Niche <span class="red">*</span></label>
                                    <input class="form-input" id="bulk-topic" placeholder="e.g., Digital Marketing, Health & Fitness, Technology" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Keywords (comma-separated)</label>
                                    <div class="kayword-input">
                                        <textarea type="text" class="form-input" id="bulk-keywords" placeholder="Enter keywords separated by commas"></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Number of Titles</label>
                                    <select class="form-input form-select" id="title-count">
                                        <option value="2">2 titles</option>
                                        <option value="5">5 titles</option>
                                        <option value="10">10 titles</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary small-width" id="generate-titles-btn">
                                    Generate Titles
                                </button>
                            </form>
                        </div>

                        <div class="bulk-step" id="step-2" style="display: none;">
                            <h4>Step 2: Review & Edit Titles</h4>
                            <div id="generated-titles" class="titles-container"></div>
                            <button class="btn btn-primary small-width" id="generate-bulk-articles-btn">
                                Generate All Articles
                            </button>
                        </div>

                        <!-- Bulk Articles Preview Section -->
                        <div id="bulk-preview-section" class="preview-section" style="display: none;">
                            <h3>Generated Articles</h3>
                            <div id="bulk-articles-list" class="articles-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-panel">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Generation Settings</h3>
                </div>
                <div class="card-content grid">
                    <div class="form-group">
                        <label class="form-label">Output Language</label>
                        <select class="form-input form-select" id="output-language">
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="it">Italian</option>
                            <option value="pt">Portuguese</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Writing Tone</label>
                        <select class="form-input form-select" id="writing-tone">
                            <option value="professional">Informative</option>
                            <option value="professional">Educational</option>
                            <option value="professional">Witty</option>
                            <option value="professional">Friendly</option>
                            <option value="professional">Urban Style</option>
                            <option value="professional">Poetry</option>
                            <option value="professional">Creative</option>
                            <option value="casual">Casual</option>
                            <option value="authoritative">Authoritative</option>
                            <option value="conversational">Conversational</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Point of view</label>
                        <select class="form-input form-select" id="point-of-view">
                            <option value="First Person">First Person</option>
                            <option value="Second Person">Second Person</option>
                            <option value="Third Person">Third Person</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Add <b>Bold</b>/<i>Italic</i></label>
                        <select class="form-input form-select" id="bold-italic">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Add FAQ</label>
                        <select class="form-input form-select" id="faq">
                            <option value="3 Answers">3 Answers</option>
                            <option value="5 Answers">5 Answers</option>
                            <option value="7 Answers">7 Answers</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Add Key Takeaways</label>
                        <select class="form-input form-select" id="key-takeaways">
                            <option value="3 Items">3 Items</option>
                            <option value="5 Items">5 Items</option>
                            <option value="7 Items">7 Items</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Article Length</label>
                        <select class="form-input form-select" id="article-length">
                            <option value="1500">Short (1000-1500 words)</option>
                            <option value="2000">Medium (1500-2000 words)</option>
                            <option value="2500">Long (2000-3000 words)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Add external links to related websites</label>
                        <select class="form-input form-select" id="external-links">
                            <option value="1 links">1 Links</option>
                            <option value="2 Links">2 Links</option>
                            <option value="3 Links">3 Links</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Image Generation</label>
                        <div class="form-group">
                            <label class="form-label">Generate Images?</label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="include-images" disabled>
                                    <span class="checkmark"></span>
                                   AI-generated images
                                </label>
                            </div>
                            <small class="form-help">Adds relevant images to your article</small>
                        </div>

                    </div>



                    <div class="form-group">
                        <label class="form-label">Publish to wordpress?</label>
                        <select class="form-input form-select" id="publish-to-wordpress">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Publish Status</label>
                        <select class="form-input form-select" id="publish-status">
                            <option value="Publish">Publish</option>
                            <option value="Schedule">Schedule</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="article-view" id="article-view">
        <div class="header-content">
            <h1 class="page-title">Generated Articles</h1>
            <p class="page-subtitle">List of all the articles generated in this project</p>
        </div>
        <div class="article-section">
            <?php $count = 1 ?>
            <?php if (is_array($articles)): ?>
                <?php foreach ($articles as $article): ?>
                    <a href="../article?id=<?= $article['id'] ?>"><?= $count++ . ". " ?> <?= $article['title'] ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
</div>

<script src="article-generator.js?v=<?= time(); ?>"></script>
</body>

</html>