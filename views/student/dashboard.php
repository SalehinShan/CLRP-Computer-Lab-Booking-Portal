<?php
// views/student/dashboard.php - Student Dashboard View Template

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="app-content">
    <div class="page-header">
        <div>
            <h2>Student Dashboard</h2>
            <p>Welcome back, <?= e($user['name']) ?> (Student ID: <?= e($student_id) ?>)</p>
        </div>
        <div>
            <button type="button" class="theme-toggle-btn" title="Switch Theme">
                <i class="bi bi-sun-fill theme-icon"></i>
                <span class="theme-label">Light Mode</span>
            </button>
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
                <div class="stat-title">Active Bookings</div>
                <div class="stat-value"><?= $total_active ?></div>
                <div class="stat-desc">Approved & Pending lab slots</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-title">Pending Requests</div>
                <div class="stat-value" style="color: #fbbf24;"><?= $total_pending ?></div>
                <div class="stat-desc">Awaiting administrator approval</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-title">Reported Issues</div>
                <div class="stat-value" style="color: #f87171;"><?= $total_issues ?></div>
                <div class="stat-desc">Complaints submitted to technicians</div>
            </div>
        </div>
    </div>

    <?php if ($tab === 'overview'): ?>
        <!-- Browse Labs & Computers Tab -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="card-title mb-0">Browse Lab Computers</h3>
                <span class="text-muted" style="font-size: 0.85rem;"><?= count($computers) ?> computers found</span>
            </div>

            <!-- Filters -->
            <form method="GET" action="<?= url('/student/dashboard.php') ?>" class="row g-2 mb-4">
                <input type="hidden" name="tab" value="overview">
                <div class="col-md-5">
                    <label class="form-label">Filter by Lab Room</label>
                    <select name="lab_id" class="form-select" onchange="this.form.submit()">
                        <option value="0">All Laboratory Rooms</option>
                        <?php foreach ($labs as $lab): ?>
                            <option value="<?= $lab['lab_id'] ?>" <?= $selected_lab == $lab['lab_id'] ? 'selected' : '' ?>>
                                <?= e($lab['room_number']) ?> (Cap: <?= $lab['capacity'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Filter by Installed Software</label>
                    <select name="software_id" class="form-select" onchange="this.form.submit()">
                        <option value="0">All Software Applications</option>
                        <?php foreach ($softwares as $sw): ?>
                            <option value="<?= $sw['software_id'] ?>" <?= $selected_software == $sw['software_id'] ? 'selected' : '' ?>>
                                <?= e($sw['software_name']) ?> <?= e($sw['version']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= url('/student/dashboard.php?tab=overview') ?>" class="btn btn-outline-clrp w-100">Reset</a>
                </div>
            </form>

            <!-- Table -->
            <div class="table-custom-container">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>PC Label</th>
                            <th>Room Number</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Installed Software</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($computers)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No computers matching the criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($computers as $pc): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($pc['pc_label']) ?></td>
                                    <td><?= e($pc['room_number'] ?? 'Unassigned') ?></td>
                                    <td><code><?= e($pc['ip_address'] ?? 'N/A') ?></code></td>
                                    <td><?= render_status_badge($pc['status']) ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($pc['software_list'])) {
                                            $swList = explode(',', $pc['software_list']);
                                            $count = 0;
                                            foreach ($swList as $sName) {
                                                if ($count >= 4) {
                                                    echo '<span class="software-tag">+' . (count($swList) - 4) . ' more</span>';
                                                    break;
                                                }
                                                echo '<span class="software-tag">' . e(trim($sName)) . '</span>';
                                                $count++;
                                            }
                                        } else {
                                            echo '<span class="text-muted" style="font-size: 0.8rem;">None</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($pc['status'] === 'Available'): ?>
                                            <button type="button" class="btn btn-primary-clrp btn-sm" data-bs-toggle="modal" data-bs-target="#reserveModal<?= $pc['computer_id'] ?>">
                                                Reserve <i class="bi bi-calendar-plus ms-1"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-outline-clrp btn-sm" disabled style="opacity: 0.5;">
                                                Unavailable
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($tab === 'bookings'): ?>
        <!-- My Bookings Tab -->
        <div class="content-card">
            <h3 class="card-title">My Reservation History</h3>

            <div class="table-custom-container">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>PC Label</th>
                            <th>Room Number</th>
                            <th>Date</th>
                            <th>Time Slot</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($my_reservations)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No reservations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($my_reservations as $res): ?>
                                <tr>
                                    <td>#<?= $res['reservation_id'] ?></td>
                                    <td class="fw-semibold"><?= e($res['pc_label']) ?></td>
                                    <td><?= e($res['room_number']) ?></td>
                                    <td><?= e($res['reservation_date']) ?></td>
                                    <td><code><?= e($res['time_slot']) ?></code></td>
                                    <td><?= render_status_badge($res['status']) ?></td>
                                    <td class="text-end">
                                        <?php if ($res['status'] === 'Pending'): ?>
                                            <form action="<?= url('/student/dashboard.php') ?>" method="POST" class="d-inline" onsubmit="return confirm('Cancel this pending reservation?');">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <input type="hidden" name="action" value="cancel_reservation">
                                                <input type="hidden" name="reservation_id" value="<?= $res['reservation_id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" style="font-size: 0.8rem;">
                                                    Cancel Request
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.8rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($tab === 'report'): ?>
        <!-- Report Issue Tab -->
        <div class="row">
            <div class="col-lg-6">
                <div class="content-card">
                    <h3 class="card-title">Submit Maintenance Report</h3>
                    <p class="text-muted" style="font-size: 0.875rem;">Report hardware malfunctions, missing software, or network connectivity issues to lab technicians.</p>

                    <form action="<?= url('/student/dashboard.php') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="report_issue">

                        <div class="mb-3">
                            <label for="computer_id" class="form-label">Select Computer</label>
                            <select name="computer_id" id="computer_id" class="form-select" required>
                                <option value="">-- Select Lab Computer --</option>
                                <?php foreach ($computers as $pc): ?>
                                    <option value="<?= $pc['computer_id'] ?>">
                                        <?= e($pc['pc_label']) ?> (<?= e($pc['room_number'] ?? 'No Room') ?>) - <?= e($pc['status']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="issue_description" class="form-label">Describe the Issue</label>
                            <textarea name="issue_description" id="issue_description" rows="4" class="form-control" placeholder="Describe the problem (e.g. Mouse left-click un-responsive, monitor blue screen, VS Code missing)..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary-clrp w-100">
                            Submit Report <i class="bi bi-send ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="content-card">
                    <h3 class="card-title">My Reported Issues</h3>
                    <div class="table-custom-container">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>PC / Room</th>
                                    <th>Issue Description</th>
                                    <th>Status</th>
                                    <th>Assigned Tech</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($my_issues)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No reported issues.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($my_issues as $iss): ?>
                                        <tr>
                                            <td class="fw-semibold">
                                                <?= e($iss['pc_label']) ?><br>
                                                <span class="text-muted font-normal" style="font-size: 0.75rem;"><?= e($iss['room_number']) ?></span>
                                            </td>
                                            <td style="max-width: 200px;">
                                                <div class="text-truncate" title="<?= e($iss['issue_description']) ?>"><?= e($iss['issue_description']) ?></div>
                                                <span class="text-muted" style="font-size: 0.75rem;"><?= e($iss['reported_at']) ?></span>
                                            </td>
                                            <td><?= render_status_badge($iss['status']) ?></td>
                                            <td><?= e($iss['tech_name'] ?? 'Unassigned') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</main>

<!-- Modals for Booking (Placed outside main container to ensure correct stacking context/z-index and avoid unclickable backdrop overlay) -->
<?php if ($tab === 'overview' && !empty($computers)): ?>
    <?php foreach ($computers as $pc): ?>
        <?php if ($pc['status'] === 'Available'): ?>
            <div class="modal fade" id="reserveModal<?= $pc['computer_id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="<?= url('/student/dashboard.php') ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="reserve">
                            <input type="hidden" name="computer_id" value="<?= $pc['computer_id'] ?>">

                            <div class="modal-header">
                                <h5 class="modal-title fs-6 fw-bold">Reserve Computer - <?= e($pc['pc_label']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Selected Computer</label>
                                    <input type="text" class="form-control" value="<?= e($pc['pc_label']) ?> (<?= e($pc['room_number']) ?>)" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reservation Date</label>
                                    <input type="date" name="reservation_date" id="reservation_date_<?= $pc['computer_id'] ?>" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" onchange="updateAvailableTimeSlots(<?= $pc['computer_id'] ?>)" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Time Slot</label>
                                    <select name="time_slot" id="time_slot_<?= $pc['computer_id'] ?>" class="form-select" required>
                                        <option value="08:00 - 09:30" data-original-text="08:00 AM - 09:30 AM">08:00 AM - 09:30 AM</option>
                                        <option value="09:40 - 11:10" data-original-text="09:40 AM - 11:10 AM">09:40 AM - 11:10 AM</option>
                                        <option value="11:20 - 12:50" data-original-text="11:20 AM - 12:50 PM">11:20 AM - 12:50 PM</option>
                                        <option value="01:00 - 02:30" data-original-text="01:00 PM - 02:30 PM">01:00 PM - 02:30 PM</option>
                                        <option value="02:40 - 04:10" data-original-text="02:40 PM - 04:10 PM">02:40 PM - 04:10 PM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-clrp btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary-clrp btn-sm">Confirm Booking Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <script>
    const bookedSlotsMap = <?= json_encode($bookedSlotsMap ?? []) ?>;

    function updateAvailableTimeSlots(computerId) {
        const dateInput = document.getElementById('reservation_date_' + computerId);
        const select = document.getElementById('time_slot_' + computerId);
        if (!dateInput || !select) return;

        const selectedDate = dateInput.value;
        const bookedSlots = (bookedSlotsMap[computerId] && bookedSlotsMap[computerId][selectedDate]) || [];

        // Reset and apply disabled attributes
        Array.from(select.options).forEach(option => {
            option.disabled = false;
            const originalText = option.getAttribute('data-original-text');
            option.text = originalText;

            if (bookedSlots.includes(option.value)) {
                option.disabled = true;
                option.text = originalText + ' (Booked)';
            }
        });

        // Auto-select first available option if current selected is disabled
        if (select.selectedOptions[0] && select.selectedOptions[0].disabled) {
            const firstAvailable = Array.from(select.options).find(opt => !opt.disabled);
            if (firstAvailable) {
                select.value = firstAvailable.value;
            } else {
                select.value = ''; // No slots available
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Also listen to show.bs.modal event to auto-refresh slots when modal is opened
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', function (event) {
                const dateInput = modal.querySelector('input[type="date"]');
                if (dateInput) {
                    // Trigger change event to populate/disable slots
                    dateInput.dispatchEvent(new Event('change'));
                }
            });
        });
    });
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
