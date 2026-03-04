<?php
session_start();

// Set timeout duration (in seconds)
$timeout_duration = 900; // 15 minutes

// Check for session timeout
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
        // Session expired
        session_unset();
        session_destroy();
        header("Location: ".$_SERVER['PHP_SELF']."?timeout=1");
        exit;
    }
    // Update last activity timestamp
    $_SESSION['LAST_ACTIVITY'] = time();
}

require_once 'core/password.php';

// Simulated hashed user list (bcrypt hashes)
$valid_users = array(
    'uploader1' => '$2y$10$ZOuAfU753McnSEehI4ptEefohU5nPUSl7nGQujsH47YbvfeMX/PkO',
    'uploader2' => '$2y$10$6Ynxzv4P6uczuWq6rgUBJOIbT7vmvsTeKPdBuVqW5Rh4HWv6NlzEC',
    'uploader3' => '$2y$10$uYa1WC.roZpP9yncNRP/PuQHd//l7JukdhJ8HihwP1CnyH7K9YQsy'
);

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (isset($valid_users[$username]) && password_verify($password, $valid_users[$username])) {
        $_SESSION['logged_in']     = true;
        $_SESSION['username']      = $username;
        $_SESSION['login_success'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $login_error = "Invalid username or password.";
    }
}

// Display timeout message
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $login_error = "Your session has expired due to inactivity. Please log in again.";
}


// Simulated uploaded data
$uploadedData = array(); // Replace with real DB fetch

include 'partials/header.php';

if (!isset($_SESSION['username'])) {
include 'partials/login_form.php';
} else {
include 'partials/layout.php';
}

include 'partials/scripts.php';
