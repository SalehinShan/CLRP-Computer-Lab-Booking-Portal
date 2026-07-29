<?php
// includes/auth.php - Session Management, Security & RBAC Enforcement

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Dynamic URL helper for root or subfolder deployments
function url($path = '') {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($script));
    if ($dir === '/' || $dir === '.') {
        $dir = '';
    }
    // Strip role subdirectories from base URL calculation
    $dir = preg_replace('#/(student|technician|admin)$#i', '', $dir);
    
    $path = '/' . ltrim($path, '/');
    return $dir . $path;
}

// Helper for XSS sanitization
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Generate CSRF Token
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verify_csrf() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die("Security error: Invalid CSRF Token.");
    }
}

// Password verification supporting bcrypt and seeded plaintext fallback
function verify_user_password($inputPassword, $storedPassword) {
    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }
    // Fallback for seeded plaintext passwords (e.g. 'password123')
    if ($inputPassword === $storedPassword) {
        return true;
    }
    return false;
}

// Flash message functions
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Get logged-in user info
function get_logged_in_user() {
    if (!is_logged_in()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['role'],
        'extra' => $_SESSION['user_extra'] ?? null
    ];
}

// Enforce login
function require_login() {
    if (!is_logged_in()) {
        header("Location: " . url('/login.php'));
        exit();
    }
}

// Enforce specific role(s)
function require_role($roles) {
    require_login();
    if (is_string($roles)) {
        $roles = [$roles];
    }
    $current_role = strtolower($_SESSION['role']);
    $allowed = array_map('strtolower', $roles);
    
    if (!in_array($current_role, $allowed)) {
        switch ($current_role) {
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
}

// Status badge component helper
function render_status_badge($status) {
    $s = trim($status);
    $badgeClass = 'badge-secondary';
    
    switch ($s) {
        case 'Available':
        case 'Approved':
        case 'Resolved':
            $badgeClass = 'badge-available';
            break;
        case 'Pending':
        case 'In Progress':
            $badgeClass = 'badge-pending';
            break;
        case 'Under Maintenance':
        case 'Rejected':
            $badgeClass = 'badge-maintenance';
            break;
        case 'In Use':
        case 'Reserved':
        case 'Cancelled':
        case 'Completed':
            $badgeClass = 'badge-neutral';
            break;
    }
    return '<span class="status-badge ' . $badgeClass . '">' . e($s) . '</span>';
}
