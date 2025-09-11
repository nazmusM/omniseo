<?php
// api/auth.php

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Include database connection
require_once '../includes/db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper functions
function validate_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// Get JSON input if Content-Type is application/json
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $_POST = array_merge($_POST, $input);
}

// Process the request
try {
    if (!isset($_POST['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No action specified']);
        exit;
    }

    $action = $_POST['action'];

    // Validate CSRF token for non-login actions
    if ($action == 'login' && $action == 'register') {
        if (empty($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
            exit;
        }
    }

    switch ($action) {
        case 'register':
            handle_register();
            break;

        case 'login':
            handle_login();
            break;

        case 'logout':
            handle_logout();
            break;

        case 'profile':
            handle_profile();
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("API Auth error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An internal error occurred']);
}

// Handler functions
function handle_register()
{
    global $db;

    if (!isset($_POST['name'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $name = trim($_POST['name']);
    $email = trim(strtolower($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
        exit;
    }

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain uppercase, lowercase letters and numbers']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }

    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Hash password and create user
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password_hash);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_credits'] = 100;

        // Regenerate session ID
        session_regenerate_id(true);

        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully',
            'user_id' => $user_id
        ]);
    } else {
        error_log("Registration failed for email: $email. Error: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
}

function handle_login()
{
    global $db;

    if (!isset($_POST['email'], $_POST['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }

    $email = trim(strtolower($_POST['email']));
    $password = $_POST['password'];

    // Validate inputs
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Check user credentials
    $stmt = $db->prepare("SELECT id, name, email, password_hash, credits FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_credits'] = $user['credits'];

            // Regenerate session ID
            session_regenerate_id(true);

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'credits' => $user['credits']
                ]
            ]);
            exit;
        }
    }

    // Generic error message
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
}

function handle_logout()
{
    // Unset all session variables
    $_SESSION = array();

    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

function handle_profile()
{
    global $db;

    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    if (isset($_POST['subaction']) && $_POST['subaction'] === 'profile_info') {
        // Update profile
        if (!isset($_POST['name'], $_POST['email'])) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            exit;
        }

        $name = trim($_POST['name']);
        $email = trim(strtolower($_POST['email']));

        // Validate inputs
        if (empty($name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }

        // Check if email is already taken by another user
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already taken']);
            exit;
        }

        // Update user profile
        $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);

        if ($stmt->execute()) {
            // Update session email
            $_SESSION['user_email'] = $email;

            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
    } elseif (isset($_POST['subaction']) && $_POST['subaction'] === 'password') {
        // Change password
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to change your password.']);
            exit;
        }

        $user_id = $_SESSION['user_id'];

        if (!isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
            echo json_encode(['success' => false, 'message' => 'You cannot leave any field empty.']);
            exit;
        }

        if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            echo json_encode(['success' => false, 'message' => 'You cannot leave any field empty.']);
            exit;
        }

        // Get the input values
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate that new password and confirmation match
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match.']);
            exit;
        }

        // Validate that new password is different from current password
        if ($current_password === $new_password) {
            echo json_encode(['success' => false, 'message' => 'New password must be different from current password.']);
            exit;
        }

        // Validate new password strength
        if (strlen($new_password) < 8) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long.']);
            exit;
        }

        if (!preg_match('/[A-Z]/', $new_password)) {
            echo json_encode(['success' => false, 'message' => 'New password must contain at least one uppercase letter.']);
            exit;
        }

        if (!preg_match('/[a-z]/', $new_password)) {
            echo json_encode(['success' => false, 'message' => 'New password must contain at least one lowercase letter.']);
            exit;
        }

        if (!preg_match('/[0-9]/', $new_password)) {
            echo json_encode(['success' => false, 'message' => 'New password must contain at least one number.']);
            exit;
        }

        if (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
            echo json_encode(['success' => false, 'message' => 'New password must contain at least one special character.']);
            exit;
        }

        // Verify current password
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }

        $user = $result->fetch_assoc();

        if (!password_verify($current_password, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            exit;
        }

        // Hash the new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Begin transaction for atomic update
        $db->begin_transaction();

        try {
            // Update the password in users table
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password_hash, $user_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update password.");
            }

            // Commit the transaction
            $db->commit();

            // Invalidate all other sessions for security (optional)
            $_SESSION['password_changed_at'] = time();

            echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollback();
            error_log("Password update error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while updating your password.']);
        }
    } else {
        // Get profile
        $stmt = $db->prepare("SELECT id, name, email, credits, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    }
}
