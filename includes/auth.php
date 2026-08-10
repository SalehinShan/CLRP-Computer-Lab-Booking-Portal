<?php
// includes/auth.php - Session Management, Security & RBAC Enforcement

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Helper to get or generate a server instance ID tied to the current running server process
function get_server_instance_id() {
    static $server_instance_id = null;
    if ($server_instance_id !== null) {
        return $server_instance_id;
    }

    $pid = getmypid();
    $file = sys_get_temp_dir() . '/clrp_server_instance.json';

    if (file_exists($file)) {
        $content = @file_get_contents($file);
        $data = @json_decode($content, true);
        if (is_array($data) && isset($data['pid']) && isset($data['server_id'])) {
            if ($data['pid'] === $pid) {
                $server_instance_id = $data['server_id'];
                return $server_instance_id;
            }
        }
    }

    $server_instance_id = bin2hex(random_bytes(16));
    $newData = [
        'pid' => $pid,
        'server_id' => $server_instance_id,
        'created_at' => time()
    ];
    @file_put_contents($file, json_encode($newData));

    return $server_instance_id;
}

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
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    
    // Verify session belongs to the current running server process
    if (!isset($_SESSION['server_instance_id']) || $_SESSION['server_instance_id'] !== get_server_instance_id()) {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        return false;
    }

    return true;
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
