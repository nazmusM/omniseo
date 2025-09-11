<?php
// includes/auth.php
require_once 'db.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

class Auth
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function requireLogin($redirectUrl = 'login.php')
    {
        if (!$this->isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                exit;
            } else {
                header('Location: ' . $redirectUrl);
                exit;
            }
        }
    }

    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT id, name, email, credits, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function updateCredits($user_id, $credits)
    {
        $stmt = $this->db->prepare("UPDATE users SET credits = ? WHERE id = ?");
        $stmt->bind_param("ii", $credits, $user_id);
        return $stmt->execute();
    }
    
    public function logout()
    {
        // Unset all session variables
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    public function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getUserEmail()
    {
        return $_SESSION['user_email'] ?? null;
    }
    
    public function getUserCredits()
    {
        return $_SESSION['user_credits'] ?? 0;
    }
    
    public function setUserCredits($credits)
    {
        $_SESSION['user_credits'] = $credits;
    }
}

// Create auth instance with dependency injection
$auth = new Auth($db);

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function to get CSRF token for forms
function csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

// Helper function to validate CSRF token
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}