<?php
include("../includes/config.php");
include("../includes/auth.php");
header('Content-Type: application/json');

// Generic delete function
function deleteRecord($db, $table, $id, $idColumn = 'id') {
    
    // Validate ID
    if (!is_numeric($id) || $id <= 0) {
        return ['success' => false, 'message' => 'Invalid ID'];
    }
    
    // Check if user owns the record (add your ownership validation logic here)
    if (!userOwnsRecord($db, $table, $id)) {
        return ['success' => false, 'message' => 'Access denied'];
    }
    
    // Prepare and execute delete statement
    $stmt = $db->prepare("DELETE FROM $table WHERE $idColumn = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $db->error];
    }
    
    $stmt->bind_param('i', $id);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        return ['success' => true, 'message' => 'Record deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete record'];
    }
}

// Check if user owns the record (customize based on your database structure)
function userOwnsRecord($db, $table, $recordId) {
    $userId = $_SESSION['user_id'];
    
    // Define table-specific user ID columns
    $userColumnMap = [
        'projects' => 'user_id',
        'articles' => 'user_id',
        'credit_usage' => 'user_id',
        // Add other tables as needed
    ];
    
    if (!isset($userColumnMap[$table])) {
        return false; // Table not in map, deny access
    }
    
    $userColumn = $userColumnMap[$table];
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM $table WHERE id = ? AND $userColumn = ?");
    $stmt->bind_param('ii', $recordId, $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['count'] > 0;
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify authentication
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Get action and ID
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? 0;
    
    // Validate ID
    if (!is_numeric($id) || $id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }
    
    // Process action
    switch ($action) {
        case 'deleteProject':
            $result = deleteRecord($db, 'projects', $id);
            break;
            
        case 'deleteArticle':
            $result = deleteRecord($db, 'articles', $id);
            break;
            
        case 'deleteSubscription':
            $result = deleteRecord($db, 'subscriptions', $id);
            break;
            
        case 'deleteWpAccount':
            $result = deleteRecord($db, 'wp_accounts', $id, 'account_id'); // Example with custom ID column
            break;
            
        case 'deleteCreditUsage':
            $result = deleteRecord($db, 'credit_usage', $id);
            break;
            
        default:
            $result = ['success' => false, 'message' => 'Invalid action'];
            break;
    }
    
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>