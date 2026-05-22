<?php
// =====================================================
// NewsLanka — Application Configuration
// =====================================================

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'newslanka');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'NewsLanka');
define('SITE_TAGLINE', 'Sri Lanka\'s Trusted News Source');
define('SITE_URL', 'http://localhost/News-Site-Student/public');
define('ADMIN_URL', 'http://localhost/News-Site-Student/admin');
define('UPLOAD_DIR', __DIR__ . '/../public/assets/images/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/images/uploads/');

// Pagination
define('ARTICLES_PER_PAGE', 6);

// Session settings (must be set before session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

// Timezone
date_default_timezone_set('Asia/Colombo');

// Error reporting (development)
error_reporting(E_ALL);
ini_set('display_errors', 1);
