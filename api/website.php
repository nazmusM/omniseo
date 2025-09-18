<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$user = $auth->getCurrentUser();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $site_name = trim($_POST['site_name']);
            $site_url = trim($_POST['site_url']);
            $api_key = trim($_POST['api_key'] ?? '');

            if (empty($site_name) || empty($site_url)) {
                throw new Exception('Site name and URL are required');
            }

            // Validate URL format
            if (!filter_var($site_url, FILTER_VALIDATE_URL)) {
                throw new Exception('Please enter a valid URL');
            }

            $stmt = $db->prepare("INSERT INTO wp_accounts (user_id, wp_name, wp_url, api_key, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("isss", $user['id'], $site_name, $site_url, $api_key);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to add website to database');
            }
            break;

        case 'sync':
            $website_id = (int)($_POST['website_id'] ?? 0);
            
            if ($website_id <= 0) {
                throw new Exception('Invalid website ID');
            }

            // Verify website belongs to user
            $stmt = $db->prepare("SELECT id FROM wp_accounts WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $website_id, $user['id']);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows === 0) {
                throw new Exception('Website not found');
            }

            // Update sync time
            $stmt = $db->prepare("UPDATE wp_accounts SET last_sync = NOW(), status = 'connected' WHERE id = ?");
            $stmt->bind_param("i", $website_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to sync website');
            }
            break;

        case 'delete':
            $website_id = (int)($_POST['website_id'] ?? 0);
            
            if ($website_id <= 0) {
                throw new Exception('Invalid website ID');
            }

            // Verify website belongs to user
            $stmt = $db->prepare("SELECT id FROM wp_accounts WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $website_id, $user['id']);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows === 0) {
                throw new Exception('Website not found');
            }

            // Delete website
            $stmt = $db->prepare("DELETE FROM wp_accounts WHERE id = ?");
            $stmt->bind_param("i", $website_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to delete website');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}