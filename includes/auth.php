<?php
// =====================================================
// Authentication & Authorization
// =====================================================

require_once __DIR__ . '/functions.php';

function login_user($user) {
    start_session();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];
}

function logout_user() {
    start_session();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]);
    }
    session_destroy();
}

function is_logged_in() {
    start_session();
    return isset($_SESSION['user_id']);
}

function is_admin() {
    start_session();
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'admin';
}

function current_user() {
    start_session();
    if (!is_logged_in()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'],
    ];
}

function require_login($redirect = null) {
    if (!is_logged_in()) {
        $redirect = $redirect ?? SITE_URL . '/login.php';
        redirect($redirect);
    }
}

function require_admin($redirect = null) {
    if (!is_admin()) {
        $redirect = $redirect ?? SITE_URL . '/login.php';
        flash('error', 'Admin access required.');
        redirect($redirect);
    }
}

function attempt_login($email, $password) {
    $stmt = db()->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        login_user($user);
        return true;
    }
    return false;
}

function register_user($name, $email, $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = db()->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'user')");
    return $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':password' => $hash,
    ]);
}

function email_exists($email, $excludeId = null) {
    $sql = "SELECT id FROM users WHERE email = :email";
    $params = [':email' => $email];
    if ($excludeId) {
        $sql .= " AND id != :id";
        $params[':id'] = $excludeId;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (bool)$stmt->fetch();
}
