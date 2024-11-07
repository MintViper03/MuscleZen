<?php
require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/security.php';
require_once __DIR__ . '/../utils/validation.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/logger.php';

class AuthController {
    public static function login($email, $password) {
        try {
            // Validate inputs
            Validation::validateEmail($email);
            if (empty($password)) {
                throw new Exception('Password is required');
            }

            // Rate limiting
            Security::checkRateLimit('login', 5, 300); // 5 attempts per 5 minutes

            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                Logger::info("Failed login attempt for email: $email");
                throw new Exception('Invalid credentials');
            }

            // Success - set up session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();

            Auth::regenerateSession();
            Logger::info("Successful login for user ID: {$user['id']}");

            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ];

        } catch (Exception $e) {
            Logger::error("Login error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function signup($data) {
        try {
            // Validate required fields
            Validation::validateRequired($data, ['username', 'email', 'password']);
            
            // Validate email and password
            Validation::validateEmail($data['email']);
            Security::validatePassword($data['password']);

            // Sanitize inputs
            $username = Security::sanitizeInput($data['username']);
            $email = strtolower(trim($data['email']));

            // Check if email already exists
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email already registered');
            }

            // Hash password and create user
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            Database::beginTransaction();
            try {
                // Insert user
                $stmt = $db->prepare("
                    INSERT INTO users (username, email, password, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$username, $email, $hashedPassword]);
                $userId = Database::lastInsertId();

                // Initialize user settings
                $stmt = $db->prepare("
                    INSERT INTO user_settings (user_id, setting_key, setting_value)
                    VALUES 
                    (?, 'profile_visibility', 'public'),
                    (?, 'workout_reminders', '1'),
                    (?, 'progress_updates', '1')
                ");
                $stmt->execute([$userId, $userId, $userId]);

                Database::commit();
                Logger::info("New user registered with ID: $userId");

                return [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email
                ];

            } catch (Exception $e) {
                Database::rollback();
                throw $e;
            }
        } catch (Exception $e) {
            Logger::error("Signup error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        session_unset();
        session_destroy();
        if ($userId) {
            Logger::info("User ID: $userId logged out");
        }
    }

    public static function resetPassword($email) {
        try {
            Validation::validateEmail($email);
            Security::checkRateLimit('password_reset', 3, 3600); // 3 attempts per hour

            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Email not found');
            }

            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $db->prepare("
                INSERT INTO password_resets (email, token, expires_at)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$email, $token, $expiry]);

            // Send reset email logic here
            Logger::info("Password reset requested for email: $email");

            return true;
        } catch (Exception $e) {
            Logger::error("Password reset error: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
