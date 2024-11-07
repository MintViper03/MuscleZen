<?php
namespace MuscleZen\Middleware;

class RateLimit {
    private static function getKey($action) {
        return "rate_limit:" . $action . ":" . ($_SESSION['user_id'] ?? 'guest');
    }

    public static function check($action, $limit = null, $window = null) {
        $limit = $limit ?? $_ENV['RATE_LIMIT_REQUESTS'];
        $window = $window ?? $_ENV['RATE_LIMIT_WINDOW'];
        
        $key = self::getKey($action);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'start_time' => time()
            ];
            return true;
        }

        $data = $_SESSION[$key];
        $elapsed = time() - $data['start_time'];

        if ($elapsed > $window) {
            $_SESSION[$key] = [
                'count' => 1,
                'start_time' => time()
            ];
            return true;
        }

        if ($data['count'] >= $limit) {
            throw new \Exception("Rate limit exceeded. Please try again later.");
        }

        $_SESSION[$key]['count']++;
        return true;
    }
}
