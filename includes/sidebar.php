<?php
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit();
}

// Get user info
$stmt = $db->prepare("SELECT name, email, credits, plan, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title . ' - ' .  SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= $stylesheet . '?v=' . time(); ?>">
    <script src="../assets/sweetalert/sweetalert.min.js"></script>
    <script defer src="../assets/js/main.js?v=<?=time();?>"></script>
</head>

<body>
    <div class="dashboard-layout">
        <div class="navbar">
            <div class="mobile-toggle">
                <svg xmlns="http://www.w3.org/2000/svg"
                    width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    role="img" aria-label="Menu">
                    <title>Menu</title>
                    <rect x="3" y="5" width="18" height="1" rx="1" />
                    <rect x="3" y="11" width="18" height="1" rx="1" />
                    <rect x="3" y="17" width="18" height="1" rx="1" />
                </svg>

            </div>
            <div class="navbar-header">
                <div class="logo">
                    <h2><?= SITE_NAME?></h2>
                </div>
            </div>
            <div class="nav-right-menu">
                <div class="credits-badge">
                    <span class="credits-count"><?php echo $user['credits']; ?></span>
                    <span class="credits-label">Credits</span>
                </div>
                <div class="plan-upgrade">
                    <a href="../plans" class="credits-badge btn-primary" title="Upgrade Plan">
                        <span class="item-name">Upgrade Plan</span> 
               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <rect x="3" y="8" width="18" height="12" rx="2"></rect>
  <path d="M12 16h.01"></path>
  <path d="M17 8V6a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2"></path>
  <path d="M7 16v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"></path>
</svg>
                </a>
                    <a href="../credits" class="credits-badge btn-secondary" title="Add Credits">
                        <span class="item-name">Buy Credits</span>
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="12" cy="12" r="10"></circle>
  <line x1="12" y1="8" x2="12" y2="16"></line>
  <line x1="8" y1="12" x2="16" y2="12"></line>
</svg>
                        </a>
                </div>
            </div>
        </div>

        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <h2>omniSEO</h2>
                </div>
                <div class="close-btn">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        role="img" aria-label="Close">
                        <title>Close</title>
                        <line x1="4" y1="4" x2="20" y2="20" />
                        <line x1="20" y1="4" x2="4" y2="20" />
                    </svg>

                </div>
            </div>

            <div class="user-info">
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li class="<?php echo basename($_SERVER['REQUEST_URI']) == 'dashboard' ? 'active' : ''; ?>">
                        <a href="../dashboard">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" />
                                <rect x="14" y="3" width="7" height="7" />
                                <rect x="14" y="14" width="7" height="7" />
                                <rect x="3" y="14" width="7" height="7" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li class="<?php echo basename($_SERVER['REQUEST_URI']) == 'projects' ? 'active' : ''; ?>">
                        <a href="../projects">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14,2 14,8 20,8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <polyline points="10,9 9,9 8,9" />
                            </svg>
                            Create Projects
                        </a>
                    </li>
                    <li class="<?php echo basename($_SERVER['REQUEST_URI']) == 'history' ? 'active' : ''; ?>">
                        <a href="../history">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12,6 12,12 16,14" />
                            </svg>
                            History
                        </a>
                    </li>
<li class="<?php echo basename($_SERVER['REQUEST_URI']) == 'websites' ? 'active' : ''; ?>">
                        <a href="../websites">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M22 9H2M14 17.5L16.5 15L14 12.5M10 12.5L7.5 15L10 17.5M2 7.8L2 16.2C2 17.8802 2 18.7202 2.32698 19.362C2.6146 19.9265 3.07354 20.3854 3.63803 20.673C4.27976 21 5.11984 21 6.8 21H17.2C18.8802 21 19.7202 21 20.362 20.673C20.9265 20.3854 21.3854 19.9265 21.673 19.362C22 18.7202 22 17.8802 22 16.2V7.8C22 6.11984 22 5.27977 21.673 4.63803C21.3854 4.07354 20.9265 3.6146 20.362 3.32698C19.7202 3 18.8802 3 17.2 3L6.8 3C5.11984 3 4.27976 3 3.63803 3.32698C3.07354 3.6146 2.6146 4.07354 2.32698 4.63803C2 5.27976 2 6.11984 2 7.8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>
                            My Websites
                        </a>
                    </li>
                    <li class="<?php echo basename($_SERVER['REQUEST_URI']) == 'account' ? 'active' : ''; ?>">
                        <a href="../account">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Account
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <a href="../logout" class="logout-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16,17 21,12 16,7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
            const mobileToggle = document.querySelector(".mobile-toggle");
            const sidebar = document.querySelector(".sidebar");
            const closeBtn = document.querySelector(".close-btn");

            mobileToggle.addEventListener("click", function() {
                sidebar.classList.toggle('open');
            })

            closeBtn.addEventListener("click", function(){
                if(sidebar.classList.contains("open")){
                    sidebar.classList.remove("open")
                }
            })

            document.addEventListener("click", (e) => {
    const target = e.target;

    if (mobileToggle && sidebar) {
      if (!mobileToggle.contains(target) && !sidebar.contains(target)) {
        sidebar.classList.remove("open");
      }
    }
  });
        })
        </script>