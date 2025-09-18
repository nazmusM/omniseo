<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

$auth->requireLogin();
$user = $auth->getCurrentUser();

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total websites for pagination
$stmt = $db->prepare("SELECT COUNT(*) as total FROM wp_accounts WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$totalWebsites = $totalResult['total'];
$totalPages = ceil($totalWebsites / $limit);

// Fetch websites with pagination
$stmt = $db->prepare("SELECT * FROM wp_accounts WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $user['id'], $limit, $offset);
$stmt->execute();
$websites = $stmt->get_result();

$title = 'My Websites';
$stylesheet = 'websites.css';
?>
  <?php include '../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="content">
      <div class="page-header">
        <h1 class="page-title"> My Websites</h1>
        <button class="btn btn-primary" onclick="openModal()">
          <i class="icon-plus"></i> Add New Website
        </button>
      </div>
      

          <?php if ($websites->num_rows > 0): ?>
            <div class="websites-grid">
              <?php while ($site = $websites->fetch_assoc()): ?>
                <div class="website-card" data-id="<?= $site['id'] ?>">
                  <div class="website-header">
                    <h3><?= htmlspecialchars($site['wp_name']) ?></h3>
                    <div class="website-actions">
                      <div class="btn-icon sync" onclick="syncWebsite(<?= $site['id'] ?>)" title="Sync">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" color="var(--primary)" xmlns="http://www.w3.org/2000/svg">
 <path d="M8.54661 19.7675C10.9457 20.8319 13.8032 20.7741 16.2502 19.3613C20.3157 17.0141 21.7086 11.8156 19.3614 7.75008L19.1114 7.31706M4.63851 16.2502C2.2913 12.1847 3.68424 6.98619 7.74972 4.63898C10.1967 3.22621 13.0542 3.16841 15.4533 4.23277M2.49341 16.3338L5.22546 17.0659L5.95751 14.3338M18.0426 9.6659L18.7747 6.93385L21.5067 7.6659" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
              </div>
                      <div class="btn-icon delete" onclick="deleteWebsite(<?= $site['id'] ?>)" title="Delete">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" color="var(--error)" xmlns="http://www.w3.org/2000/svg">
 <path d="M9 3H15M3 6H21M19 6L18.2987 16.5193C18.1935 18.0975 18.1409 18.8867 17.8 19.485C17.4999 20.0118 17.0472 20.4353 16.5017 20.6997C15.882 21 15.0911 21 13.5093 21H10.4907C8.90891 21 8.11803 21 7.49834 20.6997C6.95276 20.4353 6.50009 20.0118 6.19998 19.485C5.85911 18.8867 5.8065 18.0975 5.70129 16.5193L5 6M10 10.5V15.5M14 10.5V15.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
              </div>
                    </div>
                  </div>
                  <div class="website-url"><?= htmlspecialchars($site['wp_url']) ?></div>
                  <div class="website-status">
                    <span class="status-badge status-<?= $site['status'] ?>">
                      <?= ucfirst($site['status']) ?>
                    </span>
                    <span class="last-sync">
                      Last sync: <?= $site['last_sync'] ? date('M j, Y g:i A', strtotime($site['last_sync'])) : 'Never' ?>
                    </span>
                  </div>
                  <div class="website-footer">
                    <a href="seo-analysis.php?site_id=<?= $site['id'] ?>" class="btn btn-outline">
                      <i class="icon-analytics"></i> Manage SEO
                    </a>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
              <div class="pagination">
                <?php if ($page > 1): ?>
                  <a href="?page=<?= $page - 1 ?>" class="pagination-item">
                    <i class="icon-chevron-left"></i> Previous
                  </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <a href="?page=<?= $i ?>" class="pagination-item <?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                  </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                  <a href="?page=<?= $page + 1 ?>" class="pagination-item">
                    Next <i class="icon-chevron-right"></i>
                  </a>
                <?php endif; ?>
              </div>
            <?php endif; ?>

          <?php else: ?>
            <div class="empty-state">
              <i class="icon-website"></i>
              <h3>No websites added yet</h3>
              <p>Add your first website to start tracking SEO performance</p>
              <button class="btn btn-primary" onclick="openModal()">
                <i class="icon-plus"></i> Add Your First Website
              </button>
            </div>
          <?php endif; ?>
    </div>
  </div>

  <!-- Add Website Modal -->
  <div id="websiteModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Add New Website</h2>
        <span class="modal-close" onclick="closeModal()">&times;</span>
      </div>
      <form id="addWebsiteForm">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Site Name</label>
            <input type="text" name="site_name" class="form-input" required placeholder="e.g., My Blog">
          </div>
          <div class="form-group">
            <label class="form-label">Site URL</label>
            <input type="url" name="site_url" class="form-input" required placeholder="https://example.com">
          </div>
          <div class="form-group">
            <label class="form-label">WordPress API Key <small>(Optional)</small></label>
            <input type="text" name="api_key" class="form-input" placeholder="Enter your WordPress API key">
            <div class="help-text">Get this from your WordPress admin panel under OmniSEO settings</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary small-width">Save Website</button>
        </div>
      </form>
    </div>
          </main>
          </div>

  <script src="websites.js?v=<?=time();?>"></script>
</body>
</html>