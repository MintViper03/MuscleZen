<?php
class Security {
    // CSRF Token management
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }
        return true;
    }

    // Input sanitization
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // Rate limiting
    public static function checkRateLimit($action, $limit = 60, $period = 3600) {
        $user_id = $_SESSION['user_id'] ?? 'guest';
        $key = "rate_limit:{$action}:{$user_id}";
        
        if (isset($_SESSION[$key])) {
            $attempts = $_SESSION[$key]['attempts'];
            $first_attempt = $_SESSION[$key]['first_attempt'];
            
            if ($attempts >= $limit && (time() - $first_attempt) < $period) {
                throw new Exception('Rate limit exceeded. Please try again later.');
            }
            
            if ((time() - $first_attempt) >= $period) {
                $_SESSION[$key] = [
                    'attempts' => 1,
                    'first_attempt' => time()
                ];
            } else {
                $_SESSION[$key]['attempts']++;
            }
        } else {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
        }
    }

    // Password strength validation
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        return true;
    }
}
?>
