<?php
// index.php - Portal Entry Point Router

require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    $role = strtolower($_SESSION['role'] ?? '');
    switch ($role) {
        case 'admin':
            header("Location: " . url('/admin/dashboard.php'));
            break;
        case 'technician':
            header("Location: " . url('/technician/dashboard.php'));
            break;
        case 'student':
            header("Location: " . url('/student/dashboard.php'));
            break;
        default:
            header("Location: " . url('/login.php'));
            break;
    }
    exit();
}

header("Location: " . url('/login.php'));
exit();
