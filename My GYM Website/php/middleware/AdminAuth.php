<?php
class AdminAuth {
    public static function requireAdmin() {
        session_start();
        
        if (!isset($_SESSION['admin_id'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Admin authentication required'
            ]);
            exit;
        }
    }

    public static function requireSuperAdmin() {
        self::requireAdmin();
        
        if ($_SESSION['admin_role'] !== 'super_admin') {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Super admin privileges required'
            ]);
            exit;
        }
    }

    public static function validateSession() {
        if (isset($_SESSION['admin_last_activity']) && 
            (time() - $_SESSION['admin_last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            return false;
        }
        $_SESSION['admin_last_activity'] = time();
        return true;
    }

    public static function logActivity($admin_id, $action, $details = null) {
        require_once __DIR__ . '/../db_config.php';
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO admin_activity_log 
                (admin_id, action, details, ip_address) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $admin_id,
                $action,
                $details,
                $_SERVER['REMOTE_ADDR']
            ]);
            return true;
        } catch(Exception $e) {
            error_log("Admin activity log error: " . $e->getMessage());
            return false;
        }
    }
}
?>
