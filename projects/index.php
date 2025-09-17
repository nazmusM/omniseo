<?php
ini_set("display_errors", 1);
include("../includes/config.php");
include("../includes/auth.php");

// Pagination setup
$projects_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $projects_per_page;

// Get total number of projects for the current user
$user_id = $_SESSION['user_id']; // Assuming user is logged in
$total_query = "SELECT COUNT(*) as total FROM projects WHERE user_id = ?";
$stmt = $db->prepare($total_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_data = $result->fetch_assoc();
$total_projects = $total_data['total'];
$total_pages = ceil($total_projects / $projects_per_page);
$stmt->close();

// Get projects for current page
$query = "
    SELECT 
        p.*,
        COUNT(a.id) AS total_articles,
        SUM(CASE WHEN a.status = 'published' THEN 1 ELSE 0 END) AS published_articles,
        SUM(CASE WHEN a.status = 'scheduled' THEN 1 ELSE 0 END) AS scheduled_articles
    FROM projects p
    LEFT JOIN articles a ON a.project_id = p.id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $db->prepare($query);
$stmt->bind_param("iii", $user_id, $projects_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();




$title = 'Projects';
$stylesheet = 'projects.css';
?>
<?php include("../includes/sidebar.php"); ?>
<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">
            My Projects
            <button class="create-project-btn" id="createProjectBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span class="item-name">Create Project</span>
            </button>
        </h1>

        <p class="page-subtitle"> Create projects and start generating articles</p>
    </div>

    <div class="projects-grid">
        <?php if (count($projects) > 0): ?>
            <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <div class="project-card-header">
                        <div class="project-card-meta">
                            <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                            <span><i class="far fa-calendar"></i> <?php echo date('M j, Y', strtotime($project['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="project-card-body">
                        <p class="project-description">
                            WP URL: <?php echo htmlspecialchars($project['wp_url']); ?>
                        </p>
                        <div class="project-settings">
                            <span class="setting-badge">Total Articles: <span><?= $project['total_articles'] ?></span></span>
                            <span class="setting-badge">Published Articles: <span><?= $project['published_articles'] ?></span></span>
                            <span class="setting-badge">Scheduled Articles: <span><?= $project['scheduled_articles'] ?></span></span>
                        </div>
                    </div>
                    <div class="project-card-footer">
                        <a href="../article-generator?project_id=<?php echo $project['id']; ?>" class="project-action-btn btn btn-primary">
                            <svg class="eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>

                        </a>
                        <button class="project-action-btn btn btn-warning" onclick="deleteProject('<?= $project['id'] ?>')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill='#fff' width="20px" height="20px">
                                <path d="M 10.806641 2 C 10.289641 2 9.7956875 2.2043125 9.4296875 2.5703125 L 9 3 L 4 3 A 1.0001 1.0001 0 1 0 4 5 L 20 5 A 1.0001 1.0001 0 1 0 20 3 L 15 3 L 14.570312 2.5703125 C 14.205312 2.2043125 13.710359 2 13.193359 2 L 10.806641 2 z M 4.3652344 7 L 5.8925781 20.263672 C 6.0245781 21.253672 6.877 22 7.875 22 L 16.123047 22 C 17.121047 22 17.974422 21.254859 18.107422 20.255859 L 19.634766 7 L 4.3652344 7 z" />
                            </svg>

                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" fill='var(--primary)' width="50px" height="50px">
                        <path d="M 5 4 C 3.895 4 3 4.895 3 6 L 3 9 L 3 11 L 22 11 L 27 11 L 27 8 C 27 6.895 26.105 6 25 6 L 12.199219 6 L 11.582031 4.9707031 C 11.221031 4.3687031 10.570187 4 9.8671875 4 L 5 4 z M 2.5019531 13 C 1.4929531 13 0.77040625 13.977406 1.0664062 14.941406 L 4.0351562 24.587891 C 4.2941563 25.426891 5.0692656 26 5.9472656 26 L 15 26 L 24.052734 26 C 24.930734 26 25.705844 25.426891 25.964844 24.587891 L 28.933594 14.941406 C 29.229594 13.977406 28.507047 13 27.498047 13 L 15 13 L 2.5019531 13 z" />
                    </svg>
                </div>
                <h3>No projects yet</h3>
                <p>Create your first project to start generating content for your WordPress site.</p>
                <button class="create-project-btn" id="createProjectBtnEmpty">
                    <i class="fas fa-plus"></i> Create Your First Project
                </button>
            </div>
        <?php endif; ?>
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
    </div>

    <!-- Create Project Modal -->
    <div id="createProjectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Project</h2>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="project_name" class="form-label">Project Name (Max 50 characters)</label>
                        <input type="text" id="project_name" name="project_name" class="form-input" maxlength="50" required placeholder="e.g., My Blog Content">
                    </div>
                    <div class="form-group">
                        <label for="wp_url" class="form-label">WordPress Site URL</label>
                        <select name="wp_url" id="wp_url" class="form-select">
                            <option value="https://nazmustech.com">https://nazmustech.com</option>
                            <option value="https://nazmustech.com">https://nazmustech.com</option>
                            <option value="https://nazmustech.com">https://nazmustech.com</option>
                            <option value="https://nazmustech.com">https://nazmustech.com</option>
                            <option value="https://nazmustech.com">https://nazmustech.com</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn btn btn-secondary small-width" id="cancelProject">Cancel</button>
                    <button type="submit" name="create_project" class="modal-btn btn btn-primary small-width create-btn">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</main>
<script src="projects.js?v=<?= time() ?>"></script>
<script>
    async function deleteProject(id) {

        // Basic confirmation
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
                    formData.append('action', 'deleteProject');
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
                    'Project has been deleted.',
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