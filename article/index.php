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
$title = $article['title'];


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
        <a href="../history" class="btn btn-secondary">
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
                    echo $article['output'];
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$is_editing): ?>
            <div class="output-actions">
                <button type="button" class="btn btn-secondary copy-btn" data-content="<?php echo htmlspecialchars($article['output']); ?>">
                    <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M5 15C4.06812 15 3.60218 15 3.23463 14.8478C2.74458 14.6448 2.35523 14.2554 2.15224 13.7654C2 13.3978 2 12.9319 2 12V5.2C2 4.0799 2 3.51984 2.21799 3.09202C2.40973 2.71569 2.71569 2.40973 3.09202 2.21799C3.51984 2 4.0799 2 5.2 2H12C12.9319 2 13.3978 2 13.7654 2.15224C14.2554 2.35523 14.6448 2.74458 14.8478 3.23463C15 3.60218 15 4.06812 15 5M12.2 22H18.8C19.9201 22 20.4802 22 20.908 21.782C21.2843 21.5903 21.5903 21.2843 21.782 20.908C22 20.4802 22 19.9201 22 18.8V12.2C22 11.0799 22 10.5198 21.782 10.092C21.5903 9.71569 21.2843 9.40973 20.908 9.21799C20.4802 9 19.9201 9 18.8 9H12.2C11.0799 9 10.5198 9 10.092 9.21799C9.71569 9.40973 9.40973 9.71569 9.21799 10.092C9 10.5198 9 11.0799 9 12.2V18.8C9 19.9201 9 20.4802 9.21799 20.908C9.40973 21.2843 9.71569 21.5903 10.092 21.782C10.5198 22 11.0799 22 12.2 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
  Copy Text
                </button>
                <button type="button" class="btn btn-secondary" onclick="downloadText()">
                    <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M3 21H21M12 3V17M12 17L19 10M12 17L5 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
  Download
                </button>
                <button type="button" class="btn btn-secondary" onclick="enableEditMode()">
                    <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M18 10L14 6M2.49997 21.5L5.88434 21.124C6.29783 21.078 6.50457 21.055 6.69782 20.9925C6.86926 20.937 7.03242 20.8586 7.18286 20.7594C7.35242 20.6475 7.49951 20.5005 7.7937 20.2063L21 7C22.1046 5.89543 22.1046 4.10457 21 3C19.8954 1.89543 18.1046 1.89543 17 3L3.7937 16.2063C3.49952 16.5005 3.35242 16.6475 3.24061 16.8171C3.1414 16.9676 3.06298 17.1307 3.00748 17.3022C2.94493 17.4954 2.92195 17.7021 2.87601 18.1156L2.49997 21.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
  Edit Content
                </button>
                <button type="button" class="btn btn-primary" onclick="alert('WordPress publishing coming soon!')">
                    <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M21 11.5V8.8C21 7.11984 21 6.27976 20.673 5.63803C20.3854 5.07354 19.9265 4.6146 19.362 4.32698C18.7202 4 17.8802 4 16.2 4H7.8C6.11984 4 5.27976 4 4.63803 4.32698C4.07354 4.6146 3.6146 5.07354 3.32698 5.63803C3 6.27976 3 7.11984 3 8.8V17.2C3 18.8802 3 19.7202 3.32698 20.362C3.6146 20.9265 4.07354 21.3854 4.63803 21.673C5.27976 22 6.11984 22 7.8 22H12.5M21 10H3M16 2V6M8 2V6M18 21V15M15 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
  Publish to WordPress
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