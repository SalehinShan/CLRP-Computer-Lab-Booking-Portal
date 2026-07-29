<?php
// login.php - Login Controller Logic

require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header("Location: " . url('/index.php'));
    exit();
}

$error = '';
if (!empty($db_connection_error)) {
    $error = "MySQL Connection Error: " . $db_connection_error . ". Please make sure your MySQL server is running in XAMPP, MAMP, or Terminal.";
}

$email = '';
$selected_role = $_POST['role'] ?? 'auto';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($db_connection_error)) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = "Security session expired. Please refresh the page and try logging in again.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $selected_role = trim($_POST['role'] ?? 'auto');

        if (empty($email) || empty($password)) {
            $error = "Please enter both email address and password.";
        } else {
            $user_found = false;
            $user_data = null;
            $role_assigned = '';

            $tables_to_check = [];
            if ($selected_role === 'student') {
                $tables_to_check['student'] = "SELECT student_id AS id, name, email, password, dept_id AS extra FROM Student WHERE email = ?";
            } elseif ($selected_role === 'technician') {
                $tables_to_check['technician'] = "SELECT technician_id AS id, name, email, password, specialization AS extra FROM Technician WHERE email = ?";
            } elseif ($selected_role === 'admin') {
                $tables_to_check['admin'] = "SELECT admin_id AS id, name, email, password, NULL AS extra FROM Admin WHERE email = ?";
            } else {
                $tables_to_check = [
                    'admin' => "SELECT admin_id AS id, name, email, password, NULL AS extra FROM Admin WHERE email = ?",
                    'technician' => "SELECT technician_id AS id, name, email, password, specialization AS extra FROM Technician WHERE email = ?",
                    'student' => "SELECT student_id AS id, name, email, password, dept_id AS extra FROM Student WHERE email = ?"
                ];
            }

            foreach ($tables_to_check as $role_key => $query) {
                $stmt = $pdo->prepare($query);
                $stmt->execute([$email]);
                $row = $stmt->fetch();

                if ($row && verify_user_password($password, $row['password'])) {
                    $user_found = true;
                    $user_data = $row;
                    $role_assigned = $role_key;

                    if ($password === $row['password']) {
                        $new_hash = password_hash($password, PASSWORD_BCRYPT);
                        $update_tbl = ucfirst($role_key);
                        $pk_col = ($role_key === 'student') ? 'student_id' : (($role_key === 'technician') ? 'technician_id' : 'admin_id');
                        $up_stmt = $pdo->prepare("UPDATE {$update_tbl} SET password = ? WHERE {$pk_col} = ?");
                        $up_stmt->execute([$new_hash, $row['id']]);
                    }
                    break;
                }
            }

            if ($user_found && $user_data) {
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['user_name'] = $user_data['name'];
                $_SESSION['user_email'] = $user_data['email'];
                $_SESSION['role'] = $role_assigned;
                $_SESSION['user_extra'] = $user_data['extra'];

                set_flash('success', "Welcome back, " . $user_data['name'] . "!");
                session_write_close();

                switch (strtolower($role_assigned)) {
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
                        header("Location: " . url('/index.php'));
                        break;
                }
                exit();
            } else {
                $error = "Invalid credentials or account not found for selected portal.";
            }
        }
    }
}

// Render Login View HTML
require_once __DIR__ . '/views/login.html';
