<?php
// student/dashboard.php - Student Controller Logic

require_once __DIR__ . '/../includes/auth.php';
require_role('student');

define('PAGE_TITLE', 'Student Dashboard - CLRP');
$user = get_logged_in_user();
$student_id = $user['id'];

$tab = $_GET['tab'] ?? 'overview';
$active_tab = $tab;
$flash = get_flash();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $post_action = $_POST['action'] ?? '';

    if ($post_action === 'reserve') {
        $computer_id = (int)($_POST['computer_id'] ?? 0);
        $reservation_date = trim($_POST['reservation_date'] ?? '');
        $time_slot = trim($_POST['time_slot'] ?? '');

        if ($computer_id > 0 && !empty($reservation_date) && !empty($time_slot)) {
            $chk = $pdo->prepare("SELECT status, pc_label FROM Computer WHERE computer_id = ?");
            $chk->execute([$computer_id]);
            $pc = $chk->fetch();

            if (!$pc) {
                set_flash('danger', 'Invalid computer selected.');
            } elseif ($pc['status'] !== 'Available') {
                set_flash('danger', "Computer {$pc['pc_label']} is currently {$pc['status']} and cannot be reserved.");
            } else {
                $ins = $pdo->prepare("INSERT INTO Reservation (student_id, computer_id, reservation_date, time_slot, status) VALUES (?, ?, ?, ?, 'Pending')");
                $ins->execute([$student_id, $computer_id, $reservation_date, $time_slot]);
                set_flash('success', "Reservation request submitted for PC {$pc['pc_label']} on {$reservation_date} ({$time_slot}). Status: Pending.");
                header("Location: " . url('/student/dashboard.php?tab=bookings'));
                exit();
            }
        } else {
            set_flash('danger', 'Please select a computer, date, and time slot.');
        }
    } elseif ($post_action === 'cancel_reservation') {
        $res_id = (int)($_POST['reservation_id'] ?? 0);
        $upd = $pdo->prepare("UPDATE Reservation SET status = 'Cancelled' WHERE reservation_id = ? AND student_id = ? AND status = 'Pending'");
        $upd->execute([$res_id, $student_id]);
        set_flash('success', 'Reservation request has been cancelled.');
        header("Location: " . url('/student/dashboard.php?tab=bookings'));
        exit();
    } elseif ($post_action === 'report_issue') {
        $computer_id = (int)($_POST['computer_id'] ?? 0);
        $issue_description = trim($_POST['issue_description'] ?? '');

        if ($computer_id > 0 && !empty($issue_description)) {
            $ins = $pdo->prepare("INSERT INTO Maintenance (computer_id, student_id, issue_description, status, reported_at) VALUES (?, ?, ?, 'Pending', NOW())");
            $ins->execute([$computer_id, $student_id, $issue_description]);
            set_flash('success', 'Maintenance issue reported successfully. Our lab technicians will investigate.');
            header("Location: " . url('/student/dashboard.php?tab=report'));
            exit();
        } else {
            set_flash('danger', 'Please select a computer and provide a description of the issue.');
        }
    }
}

// Data Queries
$stmtActive = $pdo->prepare("SELECT COUNT(*) FROM Reservation WHERE student_id = ? AND status IN ('Approved', 'Pending')");
$stmtActive->execute([$student_id]);
$total_active = $stmtActive->fetchColumn();

$stmtPending = $pdo->prepare("SELECT COUNT(*) FROM Reservation WHERE student_id = ? AND status = 'Pending'");
$stmtPending->execute([$student_id]);
$total_pending = $stmtPending->fetchColumn();

$stmtIssues = $pdo->prepare("SELECT COUNT(*) FROM Maintenance WHERE student_id = ?");
$stmtIssues->execute([$student_id]);
$total_issues = $stmtIssues->fetchColumn();

$selected_lab = (int)($_GET['lab_id'] ?? 0);
$selected_software = (int)($_GET['software_id'] ?? 0);

$labs = $pdo->query("SELECT * FROM Lab ORDER BY room_number ASC")->fetchAll();
$softwares = $pdo->query("SELECT * FROM Software ORDER BY software_name ASC")->fetchAll();

$sqlComp = "
    SELECT c.*, l.room_number,
           GROUP_CONCAT(s.software_name ORDER BY s.software_name SEPARATOR ', ') AS software_list
    FROM Computer c
    LEFT JOIN Lab l ON c.lab_id = l.lab_id
    LEFT JOIN Computer_Software cs ON c.computer_id = cs.computer_id
    LEFT JOIN Software s ON cs.software_id = s.software_id
    WHERE 1=1
";
$paramsComp = [];

if ($selected_lab > 0) {
    $sqlComp .= " AND c.lab_id = ?";
    $paramsComp[] = $selected_lab;
}

if ($selected_software > 0) {
    $sqlComp .= " AND c.computer_id IN (SELECT computer_id FROM Computer_Software WHERE software_id = ?)";
    $paramsComp[] = $selected_software;
}

$sqlComp .= " GROUP BY c.computer_id ORDER BY l.room_number ASC, c.pc_label ASC";
$stmtComp = $pdo->prepare($sqlComp);
$stmtComp->execute($paramsComp);
$computers = $stmtComp->fetchAll();

$stmtMyRes = $pdo->prepare("
    SELECT r.*, c.pc_label, l.room_number
    FROM Reservation r
    JOIN Computer c ON r.computer_id = c.computer_id
    LEFT JOIN Lab l ON c.lab_id = l.lab_id
    WHERE r.student_id = ?
    ORDER BY r.reservation_date DESC, r.reservation_id DESC
");
$stmtMyRes->execute([$student_id]);
$my_reservations = $stmtMyRes->fetchAll();

$stmtMyMaint = $pdo->prepare("
    SELECT m.*, c.pc_label, l.room_number, t.name AS tech_name
    FROM Maintenance m
    JOIN Computer c ON m.computer_id = c.computer_id
    LEFT JOIN Lab l ON c.lab_id = l.lab_id
    LEFT JOIN Technician t ON m.technician_id = t.technician_id
    WHERE m.student_id = ?
    ORDER BY m.reported_at DESC
");
$stmtMyMaint->execute([$student_id]);
$my_issues = $stmtMyMaint->fetchAll();

// Render View
require_once __DIR__ . '/../views/student/dashboard.view.php';
