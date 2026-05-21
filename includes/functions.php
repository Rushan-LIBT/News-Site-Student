<?php
// =====================================================
// Helper Functions
// =====================================================

require_once __DIR__ . '/db.php';

// Start session if not already started
function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Always start the session up-front so headers (set-cookie) can be sent
// before any markup output. All page files require this file first.
start_session();

// Sanitize output to prevent XSS
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit;
}

// Generate URL-friendly slug
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

// CSRF token helpers
function csrf_token() {
    start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify($token) {
    start_session();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

// Flash messages
function flash($key, $message = null) {
    start_session();
    if ($message === null) {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    $_SESSION['flash'][$key] = $message;
}

// Format date for display
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' minutes ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 604800) return floor($time / 86400) . ' days ago';
    return format_date($datetime);
}

// Truncate text
function truncate($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

// Generate unique slug for an article
function unique_slug($title, $table = 'articles', $excludeId = null) {
    $base = slugify($title);
    $slug = $base;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM $table WHERE slug = :slug";
        $params = [':slug' => $slug];
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

// File upload helper — validates type & size, returns saved filename
function upload_image($file, $prefix = 'img') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false; // 5MB max

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = $prefix . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
        return $name;
    }
    return false;
}

// Get article image URL with fallback
// Lookup order: uploads/ -> defaults/ -> external placeholder
function article_image_url($filename) {
    if (!empty($filename)) {
        $upload_path  = UPLOAD_DIR . $filename;
        $default_path = __DIR__ . '/../public/assets/images/defaults/' . $filename;
        if (file_exists($upload_path))  return UPLOAD_URL . $filename;
        if (file_exists($default_path)) return SITE_URL . '/assets/images/defaults/' . $filename;
        // Map known default keys to seeded picsum images
        $seed_map = [
            'cricket-default.jpg'       => 'https://picsum.photos/seed/cricket/800/500',
            'tech-default.jpg'          => 'https://picsum.photos/seed/tech/800/500',
            'politics-default.jpg'      => 'https://picsum.photos/seed/politics/800/500',
            'local-default.jpg'         => 'https://picsum.photos/seed/srilanka/800/500',
            'entertainment-default.jpg' => 'https://picsum.photos/seed/cinema/800/500',
            'business-default.jpg'      => 'https://picsum.photos/seed/business/800/500',
        ];
        if (isset($seed_map[$filename])) return $seed_map[$filename];
    }
    return 'https://picsum.photos/seed/newslanka/800/500';
}
