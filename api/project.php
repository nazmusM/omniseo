<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$user_id = $auth->getCurrentUser()['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$project_name = $_POST['project_name'] ?? '';
$wp_url = $_POST['wp_url'] ?? '';

if (empty($project_name) || empty($wp_url)) {
    throw new Exception('Please enter details of the project.');
}

$stmt = $db->prepare("INSERT INTO projects(user_id, name, wp_url) VALUES(?, ?, ?)");
$stmt->bind_param('iss', $user_id, $project_name, $wp_url);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Project created successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create project. Please try again later.']);
}
