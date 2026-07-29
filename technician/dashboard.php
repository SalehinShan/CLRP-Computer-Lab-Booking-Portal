<?php
// technician/dashboard.php - Technician Controller Logic

require_once __DIR__ . '/../includes/auth.php';
require_role('technician');

define('PAGE_TITLE', 'Technician Dashboard - CLRP');
$user = get_logged_in_user();
$technician_id = (int)$user['id'];

$tab = $_GET['tab'] ?? 'overview';
$active_tab = $tab;
$flash = get_flash();

// Handle Form Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'claim_ticket') {
        $maint_id = (int)($_POST['maintenance_id'] ?? 0);
        if ($maint_id > 0) {
            $upd = $pdo->prepare("UPDATE Maintenance SET technician_id = ? WHERE maintenance_id = ? AND (technician_id IS NULL OR technician_id = ?)");
            $upd->execute([$technician_id, $maint_id, $technician_id]);
            set_flash('success', "Ticket #{$maint_id} assigned to you.");
        }
        header("Location: " . url('/technician/dashboard.php?tab=' . urlencode($tab)));
        exit();
    } elseif ($action === 'update_status') {
        $maint_id = (int)($_POST['maintenance_id'] ?? 0);
        $new_status = trim($_POST['status'] ?? '');
        $allowed_statuses = ['Pending', 'In Progress', 'Resolved', 'Cancelled'];

        if ($maint_id > 0 && in_array($new_status, $allowed_statuses)) {
            $stmtM = $pdo->prepare("SELECT computer_id, status FROM Maintenance WHERE maintenance_id = ?");
            $stmtM->execute([$maint_id]);
            $maint = $stmtM->fetch();

            if ($maint) {
                $computer_id = $maint['computer_id'];

                $updM = $pdo->prepare("UPDATE Maintenance SET status = ?, technician_id = COALESCE(technician_id, ?) WHERE maintenance_id = ?");
                $updM->execute([$new_status, $technician_id, $maint_id]);

                if ($new_status === 'In Progress') {
                    $updC = $pdo->prepare("UPDATE Computer SET status = 'Under Maintenance' WHERE computer_id = ?");
                    $updC->execute([$computer_id]);
                    set_flash('success', "Ticket #{$maint_id} status changed to 'In Progress'. Computer status automatically updated to 'Under Maintenance'.");
                } elseif ($new_status === 'Resolved') {
                    $updC = $pdo->prepare("UPDATE Computer SET status = 'Available' WHERE computer_id = ?");
                    $updC->execute([$computer_id]);
                    set_flash('success', "Ticket #{$maint_id} marked as 'Resolved'. Computer status automatically restored to 'Available'.");
                } else {
                    set_flash('info', "Ticket #{$maint_id} status updated to '{$new_status}'.");
                }
            }
        }
        header("Location: " . url('/technician/dashboard.php?tab=' . urlencode($tab)));
        exit();
    }
}

// Data Queries
$stmtAssigned = $pdo->prepare("SELECT COUNT(*) FROM Maintenance WHERE technician_id = ? AND status != 'Resolved'");
$stmtAssigned->execute([$technician_id]);
$assigned_tickets = $stmtAssigned->fetchColumn();

$stmtPendingWork = $pdo->prepare("SELECT COUNT(*) FROM Maintenance WHERE status IN ('Pending', 'In Progress')");
$stmtPendingWork->execute();
$pending_work = $stmtPendingWork->fetchColumn();

$stmtResolved = $pdo->prepare("SELECT COUNT(*) FROM Maintenance WHERE technician_id = ? AND status = 'Resolved'");
$stmtResolved->execute([$technician_id]);
$resolved_tickets = $stmtResolved->fetchColumn();

$filter_mode = $_GET['filter'] ?? 'my_queue';

$sqlQueue = "
    SELECT m.*, c.pc_label, l.room_number, s.name AS student_name, s.email AS student_email, t.name AS tech_name
    FROM Maintenance m
    JOIN Computer c ON m.computer_id = c.computer_id
    LEFT JOIN Lab l ON c.lab_id = l.lab_id
    LEFT JOIN Student s ON m.student_id = s.student_id
    LEFT JOIN Technician t ON m.technician_id = t.technician_id
";

if ($tab === 'history') {
    $sqlQueue .= " WHERE m.status = 'Resolved' AND m.technician_id = {$technician_id} ORDER BY m.reported_at DESC";
} else {
    if ($filter_mode === 'unassigned') {
        $sqlQueue .= " WHERE m.technician_id IS NULL AND m.status != 'Resolved' ORDER BY m.reported_at ASC";
    } elseif ($filter_mode === 'all') {
        $sqlQueue .= " WHERE m.status != 'Resolved' ORDER BY m.reported_at ASC";
    } else {
        $sqlQueue .= " WHERE (m.technician_id = {$technician_id} OR m.technician_id IS NULL) AND m.status != 'Resolved' ORDER BY (m.technician_id = {$technician_id}) DESC, m.reported_at ASC";
    }
}

$queue_tickets = $pdo->query($sqlQueue)->fetchAll();

// Render View
require_once __DIR__ . '/../views/technician/dashboard.html';
