<?php
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/security.php';
require_once __DIR__ . '/../utils/logger.php';

class Auth {
    public static function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            Logger::info('Unauthorized access attempt');
            Response::error('Authentication required', 401);
        }
    }

    public static function validateSession() {
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            Response::error('Session expired', 440);
        }
        $_SESSION['last_activity'] = time();
    }

    public static function regenerateSession() {
        session_regenerate_id(true);
    }
}
?>
