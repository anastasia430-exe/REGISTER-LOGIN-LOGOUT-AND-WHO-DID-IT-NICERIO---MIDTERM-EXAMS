<?php

// access ng user at admin.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax'
    ]);
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
}

function requireLogin() {
    // redirect sa login kapag hindi naka sign in
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUserId() {
    return (int) $_SESSION['user_id'];
}

function isAdmin() {
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

function requireAdmin() {
    // function na admin lang ang puwedeng gumamit ng admin toolss
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        exit('Administrator access is required.');
    }
}
