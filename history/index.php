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
$stmt = $db->prepare("SELECT id, title, status, created_at, SUBSTRING(output, 1, 200) as excerpt FROM articles WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
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
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14,2 14,8 20,8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
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
                        <div class="header-flex">
                            <h3 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                            <p class="article-status">
                                <svg viewBox="0 0 122.52 122.523" xmlns="http://www.w3.org/2000/svg" width='20' height='20'>
                                    <g fill="#464342">
                                        <path d="m8.708 61.26c0 20.802 12.089 38.779 29.619 47.298l-25.069-68.686c-2.916 6.536-4.55 13.769-4.55 21.388z" />
                                        <path d="m96.74 58.608c0-6.495-2.333-10.993-4.334-14.494-2.664-4.329-5.161-7.995-5.161-12.324 0-4.831 3.664-9.328 8.825-9.328.233 0 .454.029.681.042-9.35-8.566-21.807-13.796-35.489-13.796-18.36 0-34.513 9.42-43.91 23.688 1.233.037 2.395.063 3.382.063 5.497 0 14.006-.667 14.006-.667 2.833-.167 3.167 3.994.337 4.329 0 0-2.847.335-6.015.501l19.138 56.925 11.501-34.493-8.188-22.434c-2.83-.166-5.511-.501-5.511-.501-2.832-.166-2.5-4.496.332-4.329 0 0 8.679.667 13.843.667 5.496 0 14.006-.667 14.006-.667 2.835-.167 3.168 3.994.337 4.329 0 0-2.853.335-6.015.501l18.992 56.494 5.242-17.517c2.272-7.269 4.001-12.49 4.001-16.989z" />
                                        <path d="m62.184 65.857-15.768 45.819c4.708 1.384 9.687 2.141 14.846 2.141 6.12 0 11.989-1.058 17.452-2.979-.141-.225-.269-.464-.374-.724z" />
                                        <path d="m107.376 36.046c.226 1.674.354 3.471.354 5.404 0 5.333-.996 11.328-3.996 18.824l-16.053 46.413c15.624-9.111 26.133-26.038 26.133-45.426.001-9.137-2.333-17.729-6.438-25.215z" />
                                        <path d="m61.262 0c-33.779 0-61.262 27.481-61.262 61.26 0 33.783 27.483 61.263 61.262 61.263 33.778 0 61.265-27.48 61.265-61.263-.001-33.779-27.487-61.26-61.265-61.26zm0 119.715c-32.23 0-58.453-26.223-58.453-58.455 0-32.23 26.222-58.451 58.453-58.451 32.229 0 58.45 26.221 58.45 58.451 0 32.232-26.221 58.455-58.45 58.455z" />
                                    </g>
                                </svg>
                                <?php
                                $status = strtolower($article['status']);

                                if ($status === 'publish') {
                                    echo "Published";
                                } elseif ($status === 'schedule') {
                                    echo "Scheduled";
                                } else {
                                    echo ucfirst($status);
                                }
                                ?>

                            </p>
                        </div>
                        <div class="article-date">
                            <?php echo date('M j, Y', strtotime($article['created_at'])); ?>
                        </div>
                    </div>
                    <div class="article-excerpt">
                        <?php echo htmlspecialchars(strip_tags($article['excerpt'])); ?>...
                    </div>
                    <div class="article-actions">
                        <a href="../article?id=<?php echo $article['id']; ?>" class="btn btn-secondary">
                            <svg class="eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            View
                        </a>
                        <button class="btn btn-secondary delete-btn" onclick="deleteArticle(<?php echo $article['id']; ?>)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill='var(--error)' width="20px" height="20px">
                                <path d="M 10.806641 2 C 10.289641 2 9.7956875 2.2043125 9.4296875 2.5703125 L 9 3 L 4 3 A 1.0001 1.0001 0 1 0 4 5 L 20 5 A 1.0001 1.0001 0 1 0 20 3 L 15 3 L 14.570312 2.5703125 C 14.205312 2.2043125 13.710359 2 13.193359 2 L 10.806641 2 z M 4.3652344 7 L 5.8925781 20.263672 C 6.0245781 21.253672 6.877 22 7.875 22 L 16.123047 22 C 17.121047 22 17.974422 21.254859 18.107422 20.255859 L 19.634766 7 L 4.3652344 7 z" />
                            </svg>
                            Delete
                        </button>
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

<script src="history.js?v=<?= time() ?>"></script>
<script>
    async function deleteArticle(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {


                try {
                    const formData = new FormData();
                    formData.append('action', 'deleteArticle');
                    formData.append('id', id);

                    const response = await fetch('../api/delete.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire({
                            title: "Success",
                            text: result.message,
                            icon: "success",
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: result.message,
                            icon: "error",
                        })
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the item');
                }
                Swal.fire(
                    'Deleted!',
                    'Article has been deleted.',
                    'success'
                ).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });

            }
        })
    }
</script>
</body>

</html>