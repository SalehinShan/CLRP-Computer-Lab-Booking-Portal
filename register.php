<?php
// register.php - User Registration Controller Logic

require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: " . url('/index.php'));
    exit();
}

$error = '';
$success = '';

if (!empty($db_connection_error)) {
    $error = "Database Connection Error: " . $db_connection_error;
}

// Fetch departments for student registration dropdown
$departments = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT dept_id, dept_name FROM Department ORDER BY dept_name ASC");
        $departments = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback default departments if table empty or error
        $departments = [
            ['dept_id' => 'CSE', 'dept_name' => 'Computer Science and Engineering'],
            ['dept_id' => 'EEE', 'dept_name' => 'Electrical and Electronic Engineering'],
            ['dept_id' => 'BBA', 'dept_name' => 'School of Business and Economics'],
            ['dept_id' => 'Civil', 'dept_name' => 'Civil and Environmental Engineering'],
            ['dept_id' => 'English', 'dept_name' => 'English and Modern Languages'],
            ['dept_id' => 'BBT', 'dept_name' => 'Biotechnology']
        ];
    }
}

// Form state values for repopulation
$form = [
    'role' => 'student',
    'name' => '',
    'email' => '',
    'student_id' => '',
    'dept_id' => 'CSE',
    'specialization' => 'Hardware'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($db_connection_error)) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = "Security session expired. Please refresh and try again.";
    } else {
        $role = trim($_POST['role'] ?? 'student');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $student_id = trim($_POST['student_id'] ?? '');
        $dept_id = trim($_POST['dept_id'] ?? 'CSE');
        $specialization = trim($_POST['specialization'] ?? 'Hardware');

        // Repopulate form values
        $form['role'] = $role;
        $form['name'] = $name;
        $form['email'] = $email;
        $form['student_id'] = $student_id;
        $form['dept_id'] = $dept_id;
        $form['specialization'] = $specialization;

        // Validation Checks
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "Please fill in all required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide a valid email address.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match. Please verify your password.";
        } elseif (!in_array($role, ['student', 'technician', 'admin'])) {
            $error = "Invalid account role selected.";
        } else {
            // Check if email already exists in any user table
            $email_exists = false;
            foreach (['Student', 'Technician', 'Admin'] as $tbl) {
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM {$tbl} WHERE LOWER(email) = LOWER(?)");
                $check_stmt->execute([$email]);
                if ($check_stmt->fetchColumn() > 0) {
                    $email_exists = true;
                    break;
                }
            }

            if ($email_exists) {
                $error = "An account with this email address already exists. Please sign in instead.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $new_user_id = null;
                $inserted = false;

                if ($role === 'student') {
                    if (empty($student_id)) {
                        $error = "Student ID is required for student account registration.";
                    } else {
                        // Check if student ID exists
                        $st_stmt = $pdo->prepare("SELECT COUNT(*) FROM Student WHERE student_id = ?");
                        $st_stmt->execute([$student_id]);
                        if ($st_stmt->fetchColumn() > 0) {
                            $error = "A student with ID '{$student_id}' is already registered.";
                        } else {
                            $ins_stmt = $pdo->prepare("INSERT INTO Student (student_id, name, email, password, dept_id) VALUES (?, ?, ?, ?, ?)");
                            $inserted = $ins_stmt->execute([$student_id, $name, $email, $hashed_password, $dept_id]);
                            $new_user_id = $student_id;
                        }
                    }
                } elseif ($role === 'technician') {
                    $ins_stmt = $pdo->prepare("INSERT INTO Technician (name, email, password, specialization) VALUES (?, ?, ?, ?)");
                    $inserted = $ins_stmt->execute([$name, $email, $hashed_password, $specialization]);
                    $new_user_id = $pdo->lastInsertId();
                } elseif ($role === 'admin') {
                    $ins_stmt = $pdo->prepare("INSERT INTO Admin (name, email, password) VALUES (?, ?, ?)");
                    $inserted = $ins_stmt->execute([$name, $email, $hashed_password]);
                    $new_user_id = $pdo->lastInsertId();
                }

                if ($inserted && $new_user_id) {
                    // Auto login after successful registration
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['role'] = $role;
                    $_SESSION['user_extra'] = ($role === 'student') ? $dept_id : (($role === 'technician') ? $specialization : null);

                    set_flash('success', "Account created successfully! Welcome to the portal, {$name}.");
                    session_write_close();

                    switch ($role) {
                        case 'admin':
                            header("Location: " . url('/admin/dashboard.php'));
                            break;
                        case 'technician':
                            header("Location: " . url('/technician/dashboard.php'));
                            break;
                        case 'student':
                        default:
                            header("Location: " . url('/student/dashboard.php'));
                            break;
                    }
                    exit();
                } elseif (empty($error)) {
                    $error = "Failed to create account. Please try again or contact support.";
                }
            }
        }
    }
}

// Render Registration HTML View
require_once __DIR__ . '/views/register.html';
