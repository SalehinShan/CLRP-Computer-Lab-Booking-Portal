<?php
// admin/dashboard.php - Admin Controller Logic

require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

define('PAGE_TITLE', 'Admin Portal - CLRP');
$user = get_logged_in_user();

$tab = $_GET['tab'] ?? 'overview';
$active_tab = $tab;
$flash = get_flash();

// Handle Form POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    // --- LAB ACTIONS ---
    if ($action === 'add_lab') {
        $room_number = trim($_POST['room_number'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        if (!empty($room_number) && $capacity > 0) {
            $stmt = $pdo->prepare("INSERT INTO Lab (room_number, capacity) VALUES (?, ?)");
            $stmt->execute([$room_number, $capacity]);
            set_flash('success', "Lab Room {$room_number} added successfully.");
        } else {
            set_flash('danger', 'Please enter valid room number and capacity.');
        }
    } elseif ($action === 'edit_lab') {
        $lab_id = (int)($_POST['lab_id'] ?? 0);
        $room_number = trim($_POST['room_number'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        if ($lab_id > 0 && !empty($room_number) && $capacity > 0) {
            $stmt = $pdo->prepare("UPDATE Lab SET room_number = ?, capacity = ? WHERE lab_id = ?");
            $stmt->execute([$room_number, $capacity, $lab_id]);
            set_flash('success', "Lab #{$lab_id} updated.");
        }
    } elseif ($action === 'delete_lab') {
        $lab_id = (int)($_POST['lab_id'] ?? 0);
        if ($lab_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM Lab WHERE lab_id = ?");
            $stmt->execute([$lab_id]);
            set_flash('success', "Lab #{$lab_id} removed.");
        }
    }

    // --- COMPUTER ACTIONS ---
    elseif ($action === 'add_computer') {
        $pc_label = trim($_POST['pc_label'] ?? '');
        $ip_address = trim($_POST['ip_address'] ?? '');
        $status = trim($_POST['status'] ?? 'Available');
        $lab_id = (int)($_POST['lab_id'] ?? 0);

        if (!empty($pc_label) && $lab_id > 0) {
            $stmt = $pdo->prepare("INSERT INTO Computer (pc_label, ip_address, status, lab_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$pc_label, $ip_address, $status, $lab_id]);
            set_flash('success', "Computer {$pc_label} added successfully.");
        } else {
            set_flash('danger', 'PC Label and Lab assignment are required.');
        }
    } elseif ($action === 'edit_computer') {
        $computer_id = (int)($_POST['computer_id'] ?? 0);
        $pc_label = trim($_POST['pc_label'] ?? '');
        $ip_address = trim($_POST['ip_address'] ?? '');
        $status = trim($_POST['status'] ?? 'Available');
        $lab_id = (int)($_POST['lab_id'] ?? 0);

        if ($computer_id > 0 && !empty($pc_label) && $lab_id > 0) {
            $stmt = $pdo->prepare("UPDATE Computer SET pc_label = ?, ip_address = ?, status = ?, lab_id = ? WHERE computer_id = ?");
            $stmt->execute([$pc_label, $ip_address, $status, $lab_id, $computer_id]);
            set_flash('success', "Computer {$pc_label} updated.");
        }
    } elseif ($action === 'delete_computer') {
        $computer_id = (int)($_POST['computer_id'] ?? 0);
        if ($computer_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM Computer WHERE computer_id = ?");
            $stmt->execute([$computer_id]);
            set_flash('success', "Computer record removed.");
        }
    }

    // --- SOFTWARE ACTIONS ---
    elseif ($action === 'add_software') {
        $name = trim($_POST['software_name'] ?? '');
        $version = trim($_POST['version'] ?? '');
        $license = trim($_POST['license_type'] ?? '');
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO Software (software_name, version, license_type) VALUES (?, ?, ?)");
            $stmt->execute([$name, $version, $license]);
            set_flash('success', "Software '{$name}' added.");
        }
    } elseif ($action === 'edit_software') {
        $sw_id = (int)($_POST['software_id'] ?? 0);
        $name = trim($_POST['software_name'] ?? '');
        $version = trim($_POST['version'] ?? '');
        $license = trim($_POST['license_type'] ?? '');
        if ($sw_id > 0 && !empty($name)) {
            $stmt = $pdo->prepare("UPDATE Software SET software_name = ?, version = ?, license_type = ? WHERE software_id = ?");
            $stmt->execute([$name, $version, $license, $sw_id]);
            set_flash('success', "Software entry updated.");
        }
    } elseif ($action === 'delete_software') {
        $sw_id = (int)($_POST['software_id'] ?? 0);
        if ($sw_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM Software WHERE software_id = ?");
            $stmt->execute([$sw_id]);
            set_flash('success', "Software entry deleted.");
        }
    } elseif ($action === 'link_software') {
        $computer_id = (int)($_POST['computer_id'] ?? 0);
        $software_id = (int)($_POST['software_id'] ?? 0);
        $inst_date = $_POST['installation_date'] ?: date('Y-m-d');
        if ($computer_id > 0 && $software_id > 0) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO Computer_Software (computer_id, software_id, installation_date) VALUES (?, ?, ?)");
            $stmt->execute([$computer_id, $software_id, $inst_date]);
            set_flash('success', "Software mapped to computer.");
        }
    } elseif ($action === 'unlink_software') {
        $computer_id = (int)($_POST['computer_id'] ?? 0);
        $software_id = (int)($_POST['software_id'] ?? 0);
        if ($computer_id > 0 && $software_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM Computer_Software WHERE computer_id = ? AND software_id = ?");
            $stmt->execute([$computer_id, $software_id]);
            set_flash('success', "Software unlinked from computer.");
        }
    }

    // --- USER MANAGEMENT ACTIONS ---
    elseif ($action === 'add_user') {
        $user_type = trim($_POST['user_type'] ?? 'student');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $raw_pass = trim($_POST['password'] ?? 'password123');
        $hash_pass = password_hash($raw_pass, PASSWORD_BCRYPT);

        if (!empty($name) && !empty($email)) {
            if ($user_type === 'student') {
                $student_id = trim($_POST['student_id'] ?? '');
                $dept_id = trim($_POST['dept_id'] ?? 'CSE');
                if (!empty($student_id)) {
                    $stmt = $pdo->prepare("INSERT INTO Student (student_id, name, email, password, dept_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$student_id, $name, $email, $hash_pass, $dept_id]);
                    set_flash('success', "Student {$name} created successfully.");
                } else {
                    set_flash('danger', 'Student ID is required.');
                }
            } elseif ($user_type === 'technician') {
                $spec = trim($_POST['specialization'] ?? 'General');
                $stmt = $pdo->prepare("INSERT INTO Technician (name, email, password, specialization) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hash_pass, $spec]);
                set_flash('success', "Technician {$name} created successfully.");
            } elseif ($user_type === 'admin') {
                $stmt = $pdo->prepare("INSERT INTO Admin (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hash_pass]);
                set_flash('success', "Administrator {$name} created successfully.");
            }
        }
    } elseif ($action === 'delete_user') {
        $user_type = trim($_POST['user_type'] ?? '');
        $uid = trim($_POST['id'] ?? '');

        if ($user_type === 'student') {
            $stmt = $pdo->prepare("DELETE FROM Student WHERE student_id = ?");
            $stmt->execute([$uid]);
            set_flash('success', "Student deleted.");
        } elseif ($user_type === 'technician') {
            $stmt = $pdo->prepare("DELETE FROM Technician WHERE technician_id = ?");
            $stmt->execute([$uid]);
            set_flash('success', "Technician deleted.");
        } elseif ($user_type === 'admin') {
            $stmt = $pdo->prepare("DELETE FROM Admin WHERE admin_id = ?");
            $stmt->execute([$uid]);
            set_flash('success', "Admin deleted.");
        }
    }

    // --- RESERVATION ACTIONS ---
    elseif ($action === 'approve_reservation') {
        $res_id = (int)($_POST['reservation_id'] ?? 0);
        if ($res_id > 0) {
            // Find the reservation details first
            $stmt_res = $pdo->prepare("SELECT computer_id, reservation_date, time_slot FROM Reservation WHERE reservation_id = ?");
            $stmt_res->execute([$res_id]);
            $res = $stmt_res->fetch();

            if ($res) {
                // Check if this computer is already booked (Approved) for the same date and time slot by someone else
                $chk_res = $pdo->prepare("SELECT COUNT(*) FROM Reservation WHERE computer_id = ? AND reservation_date = ? AND time_slot = ? AND status = 'Approved' AND reservation_id != ?");
                $chk_res->execute([$res['computer_id'], $res['reservation_date'], $res['time_slot'], $res_id]);

                if ($chk_res->fetchColumn() > 0) {
                    set_flash('danger', "Cannot approve. This computer is already booked (Approved) for the same date and time slot.");
                } else {
                    $stmt = $pdo->prepare("UPDATE Reservation SET status = 'Approved' WHERE reservation_id = ?");
                    $stmt->execute([$res_id]);
                    set_flash('success', "Reservation #{$res_id} Approved.");
                }
            }
        }
    } elseif ($action === 'reject_reservation') {
        $res_id = (int)($_POST['reservation_id'] ?? 0);
        if ($res_id > 0) {
            $stmt = $pdo->prepare("UPDATE Reservation SET status = 'Rejected' WHERE reservation_id = ?");
            $stmt->execute([$res_id]);
            set_flash('success', "Reservation #{$res_id} Rejected.");
        }
    }

    // --- MAINTENANCE ASSIGNMENT ACTIONS ---
    elseif ($action === 'assign_technician') {
        $maint_id = (int)($_POST['maintenance_id'] ?? 0);
        $tech_id = (int)($_POST['technician_id'] ?? 0);
        if ($maint_id > 0 && $tech_id > 0) {
            $stmt = $pdo->prepare("UPDATE Maintenance SET technician_id = ? WHERE maintenance_id = ?");
            $stmt->execute([$tech_id, $maint_id]);
            set_flash('success', "Ticket #{$maint_id} assigned to technician.");
        }
    } elseif ($action === 'update_maint_status') {
        $maint_id = (int)($_POST['maintenance_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($maint_id > 0 && !empty($status)) {
            $stmtM = $pdo->prepare("SELECT computer_id FROM Maintenance WHERE maintenance_id = ?");
            $stmtM->execute([$maint_id]);
            $comp_id = $stmtM->fetchColumn();

            $stmt = $pdo->prepare("UPDATE Maintenance SET status = ? WHERE maintenance_id = ?");
            $stmt->execute([$status, $maint_id]);

            if ($status === 'In Progress' && $comp_id) {
                $pdo->prepare("UPDATE Computer SET status = 'Under Maintenance' WHERE computer_id = ?")->execute([$comp_id]);
            } elseif ($status === 'Resolved' && $comp_id) {
                $pdo->prepare("UPDATE Computer SET status = 'Available' WHERE computer_id = ?")->execute([$comp_id]);
            }
            set_flash('success', "Maintenance ticket #{$maint_id} updated to '{$status}'.");
        }
    }

    header("Location: " . url('/admin/dashboard.php?tab=' . urlencode($tab)));
    exit();
}

// Data Queries
$total_pcs = $pdo->query("SELECT COUNT(*) FROM Computer")->fetchColumn();
$avail_pcs = $pdo->query("SELECT COUNT(*) FROM Computer WHERE status = 'Available'")->fetchColumn();
$maint_pcs = $pdo->query("SELECT COUNT(*) FROM Computer WHERE status = 'Under Maintenance'")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM Student")->fetchColumn();
$active_bookings = $pdo->query("SELECT COUNT(*) FROM Reservation WHERE status IN ('Approved', 'Pending')")->fetchColumn();

$labs = $pdo->query("SELECT l.*, COUNT(c.computer_id) AS total_computers FROM Lab l LEFT JOIN Computer c ON l.lab_id = c.lab_id GROUP BY l.lab_id ORDER BY l.room_number ASC")->fetchAll();
$computers = $pdo->query("SELECT c.*, l.room_number FROM Computer c LEFT JOIN Lab l ON c.lab_id = l.lab_id ORDER BY l.room_number ASC, c.pc_label ASC")->fetchAll();
$softwares = $pdo->query("SELECT s.*, COUNT(cs.computer_id) AS total_installations FROM Software s LEFT JOIN Computer_Software cs ON s.software_id = cs.software_id GROUP BY s.software_id ORDER BY s.software_name ASC")->fetchAll();
$technicians = $pdo->query("SELECT * FROM Technician ORDER BY name ASC")->fetchAll();
$departments = $pdo->query("SELECT * FROM Department ORDER BY dept_id ASC")->fetchAll();

$students_list = $pdo->query("SELECT s.*, d.dept_name FROM Student s LEFT JOIN Department d ON s.dept_id = d.dept_id ORDER BY s.student_id ASC")->fetchAll();
$admins_list = $pdo->query("SELECT * FROM Admin ORDER BY admin_id ASC")->fetchAll();

$reservations = $pdo->query("
    SELECT r.*, c.pc_label, l.room_number, s.name AS student_name, s.email AS student_email
    FROM Reservation r
    JOIN Computer c ON r.computer_id = c.computer_id
    LEFT JOIN Lab l ON c.lab_id = l.lab_id
    LEFT JOIN Student s ON r.student_id = s.student_id
    ORDER BY r.reservation_date DESC, r.reservation_id DESC
")->fetchAll();

$maintenance_tickets = $pdo->query("
    SELECT m.*, c.pc_label, l.room_number, s.name AS student_name, t.name AS tech_name
    FROM Maintenance m
    JOIN Computer c ON m.computer_id = c.computer_id
    LEFT JOIN Lab l ON c.lab_id = l.lab_id
    LEFT JOIN Student s ON m.student_id = s.student_id
    LEFT JOIN Technician t ON m.technician_id = t.technician_id
    ORDER BY m.reported_at DESC
")->fetchAll();

// Render View
require_once __DIR__ . '/../views/admin/dashboard.html';
