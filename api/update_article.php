<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$auth->requireLogin();
$user = $auth->getCurrentUser();

// Read and decode JSON request body
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$article_id = isset($input['article_id']) ? intval($input['article_id']) : 0;
$content    = isset($input['content']) ? trim($input['content']) : '';
$csrf_token = $input['csrf_token'] ?? '';

// CSRF token check
if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    echo json_encode(["success" => false, "message" => "Invalid CSRF token"]);
    exit;
}

if ($article_id <= 0 || empty($content)) {
    echo json_encode(["success" => false, "message" => "Invalid data provided"]);
    exit;
}

global $db; // comes from config.php

try {
    $stmt = $db->prepare("UPDATE articles SET output = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $content, $article_id, $user['id']);
    $stmt->execute();

    if ($stmt->affected_rows >= 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes saved"]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error updating article"]);
}
