<?php
// Application Configuration
define('APP_NAME', 'MuscleZen');
define('APP_URL', 'http://your-domain.com');
define('APP_ENV', 'development'); // or 'production'

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('MAIL_FROM', 'noreply@musclezen.com');
define('MAIL_FROM_NAME', 'MuscleZen');

// Security Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 minutes
define('PASSWORD_MIN_LENGTH', 8);

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/quicktime']);

// API Rate Limiting
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour
?>
