<?php
// includes/sidebar.php - Navigation Sidebar Component

$user = get_logged_in_user();
$role = strtolower($user['role'] ?? '');
$active_tab = $active_tab ?? 'overview';
?>
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <h1>CLRP Portal</h1>
        <span>Computer Lab Resource Portal</span>
    </div>
    
    <ul class="sidebar-menu">
        <?php if ($role === 'student'): ?>
            <li>
                <a href="<?= url('/student/dashboard.php?tab=overview') ?>" class="<?= $active_tab === 'overview' ? 'active' : '' ?>">
                    <i class="bi bi-pc-display"></i> Browse Labs & PCs
                </a>
            </li>
            <li>
                <a href="<?= url('/student/dashboard.php?tab=bookings') ?>" class="<?= $active_tab === 'bookings' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i> My Bookings
                </a>
            </li>
            <li>
                <a href="<?= url('/student/dashboard.php?tab=report') ?>" class="<?= $active_tab === 'report' ? 'active' : '' ?>">
                    <i class="bi bi-exclamation-triangle"></i> Report Issue
                </a>
            </li>
        
        <?php elseif ($role === 'technician'): ?>
            <li>
                <a href="<?= url('/technician/dashboard.php?tab=overview') ?>" class="<?= $active_tab === 'overview' ? 'active' : '' ?>">
                    <i class="bi bi-tools"></i> Assigned Maintenance Queue
                </a>
            </li>
            <li>
                <a href="<?= url('/technician/dashboard.php?tab=history') ?>" class="<?= $active_tab === 'history' ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i> Ticket Log History
                </a>
            </li>

        <?php elseif ($role === 'admin'): ?>
            <li>
                <a href="<?= url('/admin/dashboard.php?tab=overview') ?>" class="<?= $active_tab === 'overview' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Analytics Overview
                </a>
            </li>
            <li>
                <a href="<?= url('/admin/dashboard.php?tab=labs') ?>" class="<?= $active_tab === 'labs' ? 'active' : '' ?>">
                    <i class="bi bi-building"></i> Manage Labs
                </a>
            </li>
            <li>
                <a href="<?= url('/admin/dashboard.php?tab=computers') ?>" class="<?= $active_tab === 'computers' ? 'active' : '' ?>">
                    <i class="bi bi-laptop"></i> Manage Computers
                </a>
            </li>
            <li>
                <a href="<?= url('/admin/dashboard.php?tab=software') ?>" class="<?= $active_tab === 'software' ? 'active' : '' ?>">
                    <i class="bi bi-code-square"></i> Manage Software
                </a>
            </li>
            <li>
                <a href="<?= url('/admin/dashboard.php?tab=users') ?>" class="<?= $active_tab === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Manage Users
                </a>
            </li>
            <li>
                <a href="<?= url('/admin/dashboard.php?tab=reservations') ?>" class="<?= $active_tab === 'reservations' ? 'active' : '' ?>">
                    <i class="bi bi-journal-bookmark"></i> Student Reservations
                </a>
            </li>
            <li>
                <a href="<?= url('/admin/dashboard.php?tab=maintenance') ?>" class="<?= $active_tab === 'maintenance' ? 'active' : '' ?>">
                    <i class="bi bi-wrench-adjustable"></i> Maintenance Queue
                </a>
            </li>
        <?php endif; ?>
    </ul>
    
    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-info">
                <div class="user-name" title="<?= e($user['name']) ?>"><?= e($user['name']) ?></div>
                <div class="user-role"><?= e(ucfirst($role)) ?> <?= $user['extra'] ? '('.e($user['extra']).')' : '' ?></div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="theme-toggle-btn sidebar-theme-toggle" title="Switch Theme">
                    <i class="bi bi-sun-fill theme-icon"></i>
                </button>
                <a href="<?= url('/logout.php') ?>" class="btn-logout" title="Sign Out">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</aside>
