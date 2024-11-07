<?php
require_once '../middleware/AdminAuth.php';
AdminAuth::requireAdmin();

header('Content-Type: application/json');
require_once '../db_config.php';

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Get total count
    $countStmt = $conn->query("SELECT COUNT(*) FROM admin_activity_log");
    $totalActivities = $countStmt->fetchColumn();

    // Get activities with admin user info
    $stmt = $conn->prepare("
        SELECT 
            al.*,
            au.username as admin_username
        FROM admin_activity_log al
        JOIN admin_users au ON al.admin_id = au.id
        ORDER BY al.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$limit, $offset]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format activities for display
    $formattedActivities = array_map(function($activity) {
        return [
            'id' => $activity['id'],
            'type' => explode('_', $activity['action'])[0],
            'description' => formatActivityDescription($activity),
            'admin' => $activity['admin_username'],
            'created_at' => $activity['created_at']
        ];
    }, $activities);

    echo json_encode([
        'status' => 'success',
        'data' => $formattedActivities,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalActivities / $limit),
            'total_entries' => $totalActivities
        ]
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching activity log: ' . $e->getMessage()
    ]);
}

function formatActivityDescription($activity) {
    $admin = $activity['admin_username'];
    $action = $activity['action'];
    $details = $activity['details'];

    switch($action) {
        case 'login':
            return "Admin $admin logged in";
        case 'logout':
            return "Admin $admin logged out";
        case 'add_user':
            return "Admin $admin added a new user";
        case 'update_user':
            return "Admin $admin updated user information";
        case 'delete_user':
            return "Admin $admin deleted a user";
        default:
            return $details ?: "Admin $admin performed $action";
    }
}
?>
