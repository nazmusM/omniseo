<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit();
}

// Get user stats
$user_id = $_SESSION['user_id'];

// Get total articles generated
$stmt = $db->prepare("SELECT COUNT(*) as total_articles FROM articles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_articles = $stmt->get_result()->fetch_assoc()['total_articles'];

// Get articles generated this month
$stmt = $db->prepare("SELECT COUNT(*) as monthly_articles FROM articles WHERE user_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$monthly_articles = $stmt->get_result()->fetch_assoc()['monthly_articles'];

// Get recent articles
$stmt = $db->prepare("SELECT id, title, created_at FROM articles WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = 'Dashboard';
?>


        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back! Here's your content generation overview.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_articles; ?></div>
                    <div class="stat-label">Total Articles Generated</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $monthly_articles; ?></div>
                    <div class="stat-label">Articles This Month</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $user['credits']; ?></div>
                    <div class="stat-label">Available Credits</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $user['plan']; ?></div>
                    <div class="stat-label">Current Plan</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-content">
                        <div class="quick-actions">
                            <a href="../projects" class="btn btn-primary">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14,2 14,8 20,8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                                Generate Article
                            </a>
                            <a href="../history" class="btn btn-secondary">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12,6 12,12 16,14"/>
                                </svg>
                                View History
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Articles</h3>
                    </div>
                    <div class="card-content">
                        <?php if (empty($recent_articles)): ?>
                            <p class="empty-state">No articles generated yet. <a href="../projects">Create your first article</a>!</p>
                        <?php else: ?>
                            <div class="recent-articles">
                                <?php foreach ($recent_articles as $article): ?>
                                    <div class="article-item">
                                        <a href="../article?id=<?= $article['id']?>">
                                            <h4><?php echo htmlspecialchars($article['title']); ?></h4>
                                        <p><?php echo date('M j, Y', strtotime($article['created_at'])); ?></p>
                                    </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: var(--spacing-xl);
}

.quick-actions {
    display: flex;
    gap: var(--spacing-md);
    flex-wrap: wrap;
}

.recent-articles {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.article-item {
    padding: var(--spacing-md);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
}

.article-item:hover {
    border-color: var(--primary);
    background: var(--surface-hover);
}

.article-item a{
    text-decoration: none;
    color: var(--text-primary);
}

.article-item h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: var(--spacing-xs);
    color: var(--text-primary);
}

.article-item p {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.empty-state {
    text-align: center;
    color: var(--text-secondary);
    padding: var(--spacing-xl);
}

.empty-state a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.empty-state a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        flex-direction: column;
    }
}
</style>
