<?php
class Logger {
    private static $logFile = __DIR__ . '/../../logs/app.log';

    public static function init() {
        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    public static function log($message, $level = 'INFO') {
        self::init();
        $timestamp = date('Y-m-d H:i:s');
        $user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest';
        $logMessage = "[$timestamp][$level][User:$user] $message" . PHP_EOL;
        error_log($logMessage, 3, self::$logFile);
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }

    public static function info($message) {
        self::log($message, 'INFO');
    }

    public static function debug($message) {
        if (defined('DEBUG') && DEBUG) {
            self::log($message, 'DEBUG');
        }
    }
}
?>
