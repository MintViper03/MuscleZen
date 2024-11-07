<?php
class ErrorTracker {
    private static $instance = null;
    private $errors = [];
    private $notificationThreshold = 5;
    private $errorCount = 0;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ErrorTracker();
        }
        return self::$instance;
    }

    public function __construct() {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    public function handleError($errno, $errstr, $errfile, $errline) {
        $error = [
            'type' => 'Error',
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s'),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            'session' => isset($_SESSION) ? $_SESSION : [],
            'request' => [
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'params' => $_REQUEST
            ]
        ];

        $this->logError($error);
        return true;
    }

    public function handleException($exception) {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => date('Y-m-d H:i:s'),
            'trace' => $exception->getTrace(),
            'session' => isset($_SESSION) ? $_SESSION : [],
            'request' => [
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'params' => $_REQUEST
            ]
        ];

        $this->logError($error);
    }

    public function handleFatalError() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    private function logError($error) {
        $this->errors[] = $error;
        $this->errorCount++;

        // Log to file
        Logger::error(json_encode($error));

        // Check if we should send notification
        if ($this->errorCount >= $this->notificationThreshold) {
            $this->sendErrorNotification();
            $this->errorCount = 0;
        }
    }

    private function sendErrorNotification() {
        $message = "Multiple errors detected:\n\n";
        foreach ($this->errors as $error) {
            $message .= sprintf(
                "[%s] %s: %s in %s:%d\n",
                $error['timestamp'],
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }

        // Send email notification
        require_once __DIR__ . '/mailer.php';
        $mailer = new Mailer();
        $mailer->sendErrorAlert($message);

        // Clear error buffer
        $this->errors = [];
    }

    public function getErrorStats() {
        return [
            'total_errors' => count($this->errors),
            'types' => array_count_values(array_column($this->errors, 'type')),
            'recent' => array_slice($this->errors, -5)
        ];
    }
}
