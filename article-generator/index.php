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
$projectStmt = $db->prepare("SELECT id, name, wp_url FROM projects WHERE id = ?");

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
$project = $result->fetch_assoc();

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

$articleStmt = $db->prepare("SELECT id, title, status FROM articles WHERE project_id = ? AND user_id = ? ORDER BY created_at DESC");
$articleStmt->bind_param('ii', $project_id, $user_id);
$articleStmt->execute();
$result = $articleStmt->get_result();
$articles = [];
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}

$articleStmt->close();

// Fetch wp account details
$wpStmt = $db->prepare("SELECT id, wp_name, wp_url FROM wp_accounts WHERE user_id = ?");
$wpStmt->bind_param('i', $user_id);
$wpStmt->execute();
$wpResult = $wpStmt->get_result();
$wp_accounts = [];
while ($wpRow = $wpResult->fetch_assoc()) {
    $wp_accounts[] = $wpRow;
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

    <div class="project-details">
        <p class="page-subtitle">Project: <strong><?= htmlspecialchars($project['name']) ?></strong></p>
        <p class="page-subtitle">WP: <strong><?= htmlspecialchars($project['wp_url']) ?></strong></p>
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
                            <option value="1500">Short</option>
                            <option value="2000">Medium</option>
                            <option value="2500">Long</option>
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
                        <select class="form-input form-select" id="publish-to-wordpress" disabled>
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Publish Status</label>
                        <select class="form-input form-select" id="publish-status" disabled>
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
        </div>
        <div class="schedule-section">
            <input type="date" id="schedule-date" class="form-input" style="width: max-content;">
            <select class="form-input form-select" id="wp_accounts">
                <option value="">Select WP Account</option>
                <?php foreach ($wp_accounts as $wp_account): ?>
                    <option value="<?= htmlspecialchars($wp_account['wp_url']) ?>"><?= htmlspecialchars($wp_account['wp_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" id="schedule-selected-btn">Schedule Selected</button>
        </div>
        <div class="article-section">
            <?php if (is_array($articles)): ?>
                <?php foreach ($articles as $article): ?>
                    <div class="article">
                        <div class="left-btns">
                        <input type="checkbox" class="article-checkbox" data-id="article-<?= $article['id'] ?>">
                        <?php if ($article['status'] === 'published'): ?>
                            <span class="status published">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" color="var(--success)" xmlns="http://www.w3.org/2000/svg">
 <path d="M2.33938 14.5896C1.44846 11.2534 2.31164 7.54623 4.92893 4.92893C8.83418 1.02369 15.1658 1.02369 19.0711 4.92893C22.9763 8.83418 22.9763 15.1658 19.0711 19.0711C16.4538 21.6884 12.7466 22.5515 9.41045 21.6606M15.0001 15.0001V9.0001M15.0001 9.0001H9.00014M15.0001 9.0001L4.99995 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
                            </span>
                        <?php elseif ($article['status'] === 'scheduled'): ?>
                            <span class="status scheduled">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" color="var(--success)" xmlns="http://www.w3.org/2000/svg">
 <path d="M12 6V12L16 14M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
                            </span>
                        <?php else: ?>
                            <span class="status draft">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" color="var(--grey-500) xmlns="http://www.w3.org/2000/svg">
 <path d="M20 9.5V6.8C20 5.11984 20 4.27976 19.673 3.63803C19.3854 3.07354 18.9265 2.6146 18.362 2.32698C17.7202 2 16.8802 2 15.2 2H8.8C7.11984 2 6.27976 2 5.63803 2.32698C5.07354 2.6146 4.6146 3.07354 4.32698 3.63803C4 4.27976 4 5.11984 4 6.8V17.2C4 18.8802 4 19.7202 4.32698 20.362C4.6146 20.9265 5.07354 21.3854 5.63803 21.673C6.27976 22 7.11984 22 8.8 22H14M16.5 15.0022C16.6762 14.5014 17.024 14.079 17.4817 13.81C17.9395 13.5409 18.4777 13.4426 19.001 13.5324C19.5243 13.6221 19.999 13.8942 20.3409 14.3004C20.6829 14.7066 20.87 15.2207 20.8692 15.7517C20.8692 17.2506 18.6209 18 18.6209 18M18.65 21H18.66" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>    </span>
                        <?php endif; ?>
                        </div>
                        <div class="flex">
                            <a href="../article?id=<?= $article['id'] ?>"> <?= $article['title'] ?></a>
                            <div class="action-btns">
                                <div class="calender-btn" onclick="openCalendar(<?= $article['id'] ?>)" title="Schedule Article">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M21 10H3M16 2V6M8 2V6M10.5 14L12 13V18M10.75 18H13.25M7.8 22H16.2C17.8802 22 18.7202 22 19.362 21.673C19.9265 21.3854 20.3854 20.9265 20.673 20.362C21 19.7202 21 18.8802 21 17.2V8.8C21 7.11984 21 6.27976 20.673 5.63803C20.3854 5.07354 19.9265 4.6146 19.362 4.32698C18.7202 4 17.8802 4 16.2 4H7.8C6.11984 4 5.27976 4 4.63803 4.32698C4.07354 4.6146 3.6146 5.07354 3.32698 5.63803C3 6.27976 3 7.11984 3 8.8V17.2C3 18.8802 3 19.7202 3.32698 20.362C3.6146 20.9265 4.07354 21.3854 4.63803 21.673C5.27976 22 6.11984 22 7.8 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
                                </div>
                                <div class="publish-btn" onclick="publishArticle(<?= $article['id'] ?>)" title="Publish Article">
                                    <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.54661 19.7675C10.9457 20.8319 13.8032 20.7741 16.2502 19.3613C20.3157 17.0141 21.7086 11.8156 19.3614 7.75008L19.1114 7.31706M4.63851 16.2502C2.2913 12.1847 3.68424 6.98619 7.74972 4.63898C10.1967 3.22621 13.0542 3.16841 15.4533 4.23277M2.49341 16.3338L5.22546 17.0659L5.95751 14.3338M18.0426 9.6659L18.7747 6.93385L21.5067 7.6659" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <a class="edit-btn" href="../article/?id=<?= $article['id'] ?>&edit=true" title="Edit Article">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill='var(--primary)' width="20px" height="20px">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                    </svg>
                                </a>
                                <div class="delete-btn" onclick="deleteArticle(<?= $article['id'] ?>)" title="Delete Article">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill='var(--error)' width="20px" height="20px">
                                        <path d="M 10.806641 2 C 10.289641 2 9.7956875 2.2043125 9.4296875 2.5703125 L 9 3 L 4 3 A 1.0001 1.0001 0 1 0 4 5 L 20 5 A 1.0001 1.0001 0 1 0 20 3 L 15 3 L 14.570312 2.5703125 C 14.205312 2.2043125 13.710359 2 13.193359 2 L 10.806641 2 z M 4.3652344 7 L 5.8925781 20.263672 C 6.0245781 21.253672 6.877 22 7.875 22 L 16.123047 22 C 17.121047 22 17.974422 21.254859 18.107422 20.255859 L 19.634766 7 L 4.3652344 7 z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
</div>

<script src="article-generator.js?v=<?= time(); ?>"></script>
</body>

</html>