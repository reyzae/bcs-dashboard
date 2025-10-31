<?php
// Load bootstrap configuration
require_once __DIR__ . '/bootstrap.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard/');
    exit();
}

// Redirect to login if not logged in
header('Location: login.php');
exit();