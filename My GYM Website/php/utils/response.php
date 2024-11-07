<?php
class Response {
    public static function success($data = null, $message = 'Success') {
        self::sendJson([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message = 'Error', $code = 400) {
        http_response_code($code);
        self::sendJson([
            'status' => 'error',
            'message' => $message
        ]);
    }

    private static function sendJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            self::error('Not authenticated', 401);
        }
    }

    public static function requireCSRF() {
        if (!isset($_POST['csrf_token']) || 
            !isset($_SESSION['csrf_token']) || 
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            self::error('Invalid CSRF token', 403);
        }
    }
}
?>
