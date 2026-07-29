<?php
// views/technician/dashboard.view.php - Technician Dashboard View Template

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="app-content">
    <div class="page-header">
        <div>
            <h2>Technician Workspace</h2>
            <p>Welcome back, <?= e($user['name']) ?> (Specialization: <?= e($user['extra'] ?? 'General Maintenance') ?>)</p>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show mb-4" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Overview Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-title">My Active Tickets</div>
                <div class="stat-value" style="color: #2563eb;"><?= $assigned_tickets ?></div>
                <div class="stat-desc">Assigned & in progress</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-title">System Pending Work</div>
                <div class="stat-value" style="color: #d97706;"><?= $pending_work ?></div>
                <div class="stat-desc">Total unresolved lab issues</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-title">Resolved Tickets</div>
                <div class="stat-value" style="color: #16a34a;"><?= $resolved_tickets ?></div>
                <div class="stat-desc">Completed by you</div>
            </div>
        </div>
    </div>

    <?php if ($tab === 'overview'): ?>
        <div class="content-card">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h3 class="card-title mb-0">Assigned Maintenance Queue</h3>
                
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?= url('/technician/dashboard.php?tab=overview&filter=my_queue') ?>" class="btn <?= $filter_mode === 'my_queue' ? 'btn-primary-clrp' : 'btn-outline-clrp' ?>">My Tickets & Unassigned</a>
                    <a href="<?= url('/technician/dashboard.php?tab=overview&filter=unassigned') ?>" class="btn <?= $filter_mode === 'unassigned' ? 'btn-primary-clrp' : 'btn-outline-clrp' ?>">Unassigned Only</a>
                    <a href="<?= url('/technician/dashboard.php?tab=overview&filter=all') ?>" class="btn <?= $filter_mode === 'all' ? 'btn-primary-clrp' : 'btn-outline-clrp' ?>">All Active Tickets</a>
                </div>
            </div>

            <div class="table-custom-container">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Computer / Room</th>
                            <th>Reported Issue</th>
                            <th>Reported By</th>
                            <th>Assigned Tech</th>
                            <th>Status</th>
                            <th>Reported At</th>
                            <th class="text-end">Manage Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($queue_tickets)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No active maintenance tickets in this queue.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($queue_tickets as $t): ?>
                                <tr>
                                    <td>#<?= $t['maintenance_id'] ?></td>
                                    <td class="fw-semibold">
                                        <?= e($t['pc_label']) ?><br>
                                        <span class="text-muted font-normal" style="font-size: 0.75rem;"><?= e($t['room_number'] ?? 'Unassigned Room') ?></span>
                                    </td>
                                    <td style="max-width: 240px;">
                                        <div class="fw-normal" style="font-size: 0.875rem;"><?= e($t['issue_description']) ?></div>
                                    </td>
                                    <td>
                                        <?= e($t['student_name'] ?? 'System/Staff') ?><br>
                                        <span class="text-muted" style="font-size: 0.75rem;"><?= e($t['student_email'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <?php if ($t['technician_id'] == $technician_id): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2">Assigned to You</span>
                                        <?php elseif ($t['technician_id']): ?>
                                            <span class="text-secondary"><?= e($t['tech_name']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= render_status_badge($t['status']) ?></td>
                                    <td style="font-size: 0.8rem;" class="text-muted"><?= e($t['reported_at']) ?></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <?php if (!$t['technician_id']): ?>
                                                <form action="<?= url('/technician/dashboard.php?tab=overview') ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                    <input type="hidden" name="action" value="claim_ticket">
                                                    <input type="hidden" name="maintenance_id" value="<?= $t['maintenance_id'] ?>">
                                                    <button type="submit" class="btn btn-outline-clrp btn-sm" title="Claim ticket">
                                                        Claim <i class="bi bi-hand-index-thumb ms-1"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Status Update Dropdown Form -->
                                            <form action="<?= url('/technician/dashboard.php?tab=overview') ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="maintenance_id" value="<?= $t['maintenance_id'] ?>">
                                                
                                                <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                                    <option value="Pending" <?= $t['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="In Progress" <?= $t['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                    <option value="Resolved" <?= $t['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                                </select>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($tab === 'history'): ?>
        <div class="content-card">
            <h3 class="card-title">Resolved Ticket History</h3>

            <div class="table-custom-container">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Computer / Room</th>
                            <th>Issue Description</th>
                            <th>Reported By</th>
                            <th>Status</th>
                            <th>Reported Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($queue_tickets)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No resolved tickets logged under your account yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($queue_tickets as $t): ?>
                                <tr>
                                    <td>#<?= $t['maintenance_id'] ?></td>
                                    <td class="fw-semibold">
                                        <?= e($t['pc_label']) ?> (<?= e($t['room_number']) ?>)
                                    </td>
                                    <td><?= e($t['issue_description']) ?></td>
                                    <td><?= e($t['student_name'] ?? 'N/A') ?></td>
                                    <td><?= render_status_badge($t['status']) ?></td>
                                    <td style="font-size: 0.8rem;" class="text-muted"><?= e($t['reported_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
