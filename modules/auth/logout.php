<?php
/**
 * Logout Handler
 * Destroys session and redirects to login.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';

if (isset($_SESSION['user_id'])) {
    logAction($_SESSION['user_id'], 'LOGOUT', 'User logged out.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: login.php');
exit;
