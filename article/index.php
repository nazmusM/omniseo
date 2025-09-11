<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->requireLogin();
$user = $auth->getCurrentUser();

// Get the article
$article_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$article_id) {
    header('Location: ../history');
    exit;
}

global $db;
$stmt = $db->prepare("SELECT * FROM articles WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $article_id, $user['id']);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    header('Location: ../history');
    exit;
}

// Extract title
$title = $article['prompt'];
$content_lines = explode("\n", $article['output']);
foreach ($content_lines as $line) {
    $line = trim($line);
    if (!empty($line) && (strpos($line, '#') === 0 || strlen($line) > 10)) {
        $title = strip_tags(str_replace('#', '', $line));
        break;
    }
}

// Edit mode
$is_editing = isset($_GET['edit']) && $_GET['edit'] === 'true';
$stylesheet = 'article.css';
?>
<?php include('../includes/sidebar.php'); ?>

<!-- Styles -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<main class="main-content">
    <div class="back-button">
        <a href="history.php" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to History
        </a>
    </div>

    <article class="article-view">
        <div class="article-header">
            <h1 class="article-title" id="articleTitle"><?php echo htmlspecialchars($title); ?></h1>

            <div class="article-meta">
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6 2a1 1 0 00-1 1v1H3a1 1 0 100 2h1v1a1 1 0 002 0V6h1a1 1 0 100-2H6V3a1 1 0 00-1-1z"/>
                    </svg>
                    <?php echo date('F j, Y \a\t g:i A', strtotime($article['created_at'])); ?>
                </div>
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M4 3a2 2 0 00-2 2v11a1 1 0 001.447.894L10 14.618l6.553 2.276A1 1 0 0018 16V5a2 2 0 00-2-2H4z"/>
                    </svg>
                    <?php echo number_format($article['word_count']); ?> words
                </div>
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                    <?php echo ucwords(str_replace('_', ' ', $article['content_type'])); ?>
                </div>
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6h13M9 12h9"/>
                    </svg>
                    <?php echo ucfirst($article['tone']); ?> tone
                </div>
            </div>
        </div>

        <div class="article-content">
            <?php if ($is_editing): ?>
                <div id="editor" class="editor-container"><?php echo htmlspecialchars($article['output']); ?></div>

                <div class="edit-mode-actions">
                    <button type="button" class="btn btn-success" onclick="saveContent()">
                        💾 Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='../article?id=<?php echo $article_id; ?>'">
                        ❌ Cancel
                    </button>
                </div>
            <?php else: ?>
                <div id="articleContent">
                    <?php
                    $content = $article['output'];
                    $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
                    $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
                    $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
                    $content = preg_replace('/\n\n+/', '</p><p>', $content);
                    $content = '<p>' . $content . '</p>';
                    $content = preg_replace('/^\* (.*$)/m', '<li>$1</li>', $content);
                    $content = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $content);
                    $content = preg_replace('/^\d+\. (.*$)/m', '<li>$1</li>', $content);
                    $content = preg_replace('/(<li>.*<\/li>)+/s', '<ol>$0</ol>', $content);
                    $content = preg_replace('/<p><\/p>/', '', $content);
                    $content = preg_replace('/<p>(<h[1-6]>.*<\/h[1-6]>)<\/p>/', '$1', $content);
                    $content = preg_replace('/<p>(<ul>.*<\/ul>)<\/p>/s', '$1', $content);
                    $content = preg_replace('/<p>(<ol>.*<\/ol>)<\/p>/s', '$1', $content);
                    echo $content;
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$is_editing): ?>
            <div class="output-actions">
                <button type="button" class="btn btn-secondary copy-btn" data-content="<?php echo htmlspecialchars($article['output']); ?>">
                    📋 Copy Text
                </button>
                <button type="button" class="btn btn-secondary" onclick="downloadText()">
                    💾 Download
                </button>
                <button type="button" class="btn btn-secondary" onclick="enableEditMode()">
                    ✏️ Edit Content
                </button>
                <button type="button" class="btn btn-primary" onclick="alert('WordPress publishing coming soon!')">
                    📝 Publish to WordPress
                </button>
            </div>
        <?php endif; ?>
    </article>
</main>

<div id="alertContainer"></div>

<script>
    <?php if ($is_editing): ?>
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'blockquote', 'code-block', 'image'],
                    [{ 'align': [] }],
                    ['clean']
                ]
            }
        });
    <?php endif; ?>

    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function() {
            const content = this.getAttribute('data-content');
            navigator.clipboard.writeText(content).then(() => {
                showAlert('Content copied to clipboard!', 'success');
            }).catch(() => {
                showAlert('Failed to copy content', 'error');
            });
        });
    });

    function downloadText() {
        const content = `<?php echo addslashes($article['output']); ?>`;
        const title = `<?php echo addslashes($title); ?>`;
        const blob = new Blob([content], { type: 'text/plain' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `${title.replace(/\s+/g, '_')}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function enableEditMode() {
        window.location.href = `../article/?id=<?php echo $article_id; ?>&edit=true`;
    }

    function saveContent() {
        const content = quill.root.innerHTML;
        const plainText = quill.getText();

        fetch('../api/update_article.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                article_id: <?php echo $article_id; ?>,
                content: content,
                plain_text: plainText,
                word_count: plainText.split(/\s+/).length,
                csrf_token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Content updated successfully!', 'success');
                setTimeout(() => window.location.href = `view.php?id=<?php echo $article_id; ?>`, 1500);
            } else {
                showAlert(data.message || 'Failed to update content', 'error');
            }
        })
        .catch(() => showAlert('Network error occurred', 'error'));
    }

    function showAlert(message, type = 'success') {
        const alert = document.createElement('div');
        alert.className = `alert ${type === 'error' ? 'error' : 'success'}`;
        alert.textContent = message;
        document.getElementById('alertContainer').appendChild(alert);
        setTimeout(() => alert.classList.add('show'), 10);
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    }
</script>
</body>
</html>