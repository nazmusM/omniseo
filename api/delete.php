<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$auth->requireLogin();
$user = $auth->getCurrentUser();

$input = json_decode(file_get_contents('php://input'), true);
$generation_id = filter_var($input['id'], FILTER_VALIDATE_INT);

if (!$generation_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// Delete the generation
global $db;
$stmt = $db->prepare("DELETE FROM articles WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $generation_id, $user['id']);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Article deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete article']);
}
?>
