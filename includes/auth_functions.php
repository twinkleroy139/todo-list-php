<?php
// includes/auth_functions.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current logged-in user ID (returns null if not logged in)
function get_current_user_id() {
    return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

// Protect pages - redirect to login if not authenticated
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Optional: Get username for display
function get_current_username() {
    return is_logged_in() && isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
}
?>