<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user credits
$stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_credits = $stmt->get_result()->fetch_assoc()['credits'];

$title = 'Article Generator';
$stylesheet = 'article-generator.css';
?>

        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Article Generator</h1>
                <p class="page-subtitle">Generate high-quality, SEO-optimized articles with AI</p>
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
                                            <label class="form-label">Main Topic/Niche</label>
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
                                                <option value="5">5 titles</option>
                                                <option value="10">10 titles</option>
                                                <option value="15">15 titles</option>
                                                <option value="20">20 titles</option>
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
                                    <button class="btn btn-primary" id="generate-bulk-articles-btn">
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
                                    <option value="professional">Professional</option>
                                    <option value="casual">Casual</option>
                                    <option value="friendly">Friendly</option>
                                    <option value="authoritative">Authoritative</option>
                                    <option value="conversational">Conversational</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Article Length</label>
                                <select class="form-input form-select" id="article-length">
                                    <option value="1000-1500 words">Short (1000-1500 words)</option>
                                    <option value="1500-2000 words">Medium (1500-2000 words)</option>
                                    <option value="2000+ words">Long (2000-3000 words)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Image Generation</label>
                                <div class="checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="include-images">
                                        <span class="checkmark"></span>
                                        Include AI-generated images
                                    </label>
                                </div>
                                <small class="form-help">Adds relevant images to your article</small>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Credits Usage</h3>
                        </div>
                        <div class="card-content">
                            <div class="credits-info">
                                <div class="credits-available">
                                    <span class="credits-count"><?php echo $user_credits; ?></span>
                                    <span class="credits-label">Available Credits</span>
                                </div>
                                <div class="credits-cost">
                                    <p><strong>Cost per article:</strong> <span id="article-cost">10</span> credits</p>
                                    <p><strong>Cost per title:</strong> 1 credit</p>
                                    <p><strong>With images:</strong> +5 credits per article</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="new.js?v=<?=time();?>"></script>
</body>
</html>