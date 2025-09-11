<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get articles with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM articles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_articles = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_articles / $limit);

// Get articles for current page
$stmt = $db->prepare("SELECT id, title, created_at, SUBSTRING(output, 1, 200) as excerpt FROM articles WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = 'History';
$stylesheet = 'history.css';
?>
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Article History</h1>
                <p class="page-subtitle">View and manage your generated articles</p>
            </div>

            <?php if (empty($articles)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                    </div>
                    <h3>No articles yet</h3>
                    <p>You haven't generated any articles yet. Start creating amazing content!</p>
                    <a href="../article-generator" class="btn btn-primary">Generate Your First Article</a>
                </div>
            <?php else: ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $article): ?>
                        <div class="article-card">
                            <div class="article-header">
                                <h3 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                                <div class="article-date">
                                    <?php echo date('M j, Y', strtotime($article['created_at'])); ?>
                                </div>
                            </div>
                            <div class="article-excerpt">
                                <?php echo htmlspecialchars(strip_tags($article['excerpt'])); ?>...
                            </div>
                            <div class="article-actions">
                                <a href="../article?id=<?php echo $article['id']; ?>" class="btn btn-secondary">View</a>
                                <button class="btn btn-secondary" onclick="publishArticle(<?php echo $article['id']; ?>)"> 
                                    <!-- WordPress monochrome (inherits color via `currentColor`) -->
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="#21759b" d="M256 8C119.3 8 8 119.3 8 256s111.3 248 248 248 248-111.3 248-248S392.7 8 256 8zM32 256c0-123.7 100.3-224 224-224s224 100.3 224 224-100.3 224-224 224S32 379.7 32 256zm252.8 102.4c-19.5 0-35.2-15.7-35.2-35.2s15.7-35.2 35.2-35.2 35.2 15.7 35.2 35.2-15.7 35.2-35.2 35.2zm-70.4-35.2c0 19.5-15.7 35.2-35.2 35.2s-35.2-15.7-35.2-35.2 15.7-35.2 35.2-35.2 35.2 15.7 35.2 35.2zm70.4-35.2c-19.5 0-35.2-15.7-35.2-35.2s15.7-35.2 35.2-35.2 35.2 15.7 35.2 35.2-15.7 35.2-35.2 35.2zm-70.4-35.2c0 19.5-15.7 35.2-35.2 35.2s-35.2-15.7-35.2-35.2 15.7-35.2 35.2-35.2 35.2 15.7 35.2 35.2zm70.4-35.2c-19.5 0-35.2-15.7-35.2-35.2s15.7-35.2 35.2-35.2 35.2 15.7 35.2 35.2-15.7 35.2-35.2 35.2z"/>
</svg>

                                Publish
                            </button>
                                <button class="btn btn-secondary delete-btn" onclick="deleteArticle(<?php echo $article['id']; ?>)">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <script src="history.js"></script>
</body>
</html>
