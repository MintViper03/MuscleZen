<?php
require_once '../middleware/AdminAuth.php';
AdminAuth::requireAdmin();

header('Content-Type: application/json');
require_once '../db_config.php';

try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $role = $_GET['role'] ?? '';
    $sort = $_GET['sort'] ?? 'newest';
    
    // Build query
    $query = "SELECT * FROM users WHERE 1=1";
    $params = [];
    
    if ($search) {
        $query .= " AND (username LIKE ? OR email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($status) {
        $query .= " AND status = ?";
        $params[] = $status;
    }
    
    if ($role) {
        $query .= " AND role = ?";
        $params[] = $role;
    }
    
    // Add sorting
    switch ($sort) {
        case 'oldest':
            $query .= " ORDER BY created_at ASC";
            break;
        case 'name':
            $query .= " ORDER BY username ASC";
            break;
        default:
            $query .= " ORDER BY created_at DESC";
    }
    
    // Get total count
    $countStmt = $conn->prepare(str_replace('SELECT *', 'SELECT COUNT(*)', $query));
    $countStmt->execute($params);
    $totalUsers = $countStmt->fetchColumn();
    
    // Add pagination
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $pagination = [
        'current_page' => $page,
        'total_pages' => ceil($totalUsers / $limit),
        'showing_start' => $offset + 1,
        'showing_end' => min($offset + $limit, $totalUsers),
        'total_entries' => $totalUsers
    ];
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'users' => $users,
            'pagination' => $pagination
        ]
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching users: ' . $e->getMessage()
    ]);
}
?>
