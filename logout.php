<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Destroy session
session_unset();
session_destroy();

// Clear remember me cookie if exists
if(isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home with message
session_start();
setFlash('success', 'You have been logged out successfully.');
redirect('index.php');
?>