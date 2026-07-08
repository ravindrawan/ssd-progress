<?php
session_start();

function require_login() {
    // 5 minutes session inactivity timeout (300 seconds)
    $timeout_duration = 300;

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        // Last activity was more than 15 minutes ago
        session_unset();
        session_destroy();
        header("Location: login.php?message=session_expired");
        exit;
    }
    
    $_SESSION['last_activity'] = time(); // Update last activity timestamp
}

function get_user_role() {
    return $_SESSION['role'] ?? null;
}

function get_username() {
    return $_SESSION['username'] ?? null;
}
?>
