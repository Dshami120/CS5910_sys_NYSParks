<?php
$adminSection = $adminSection ?? 'dashboard';
?>
<div class="admin-subnav card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div>
                <div class="section-kicker mb-1">Admin Navigation</div>
                <div class="text-muted small">Use the dashboard for summary metrics, then open dedicated management screens for full workflows.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="admin-dashboard.php" class="btn <?php echo $adminSection === 'dashboard' ? 'btn-success' : 'btn-outline-success'; ?> btn-sm">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
                <a href="admin-bookings.php" class="btn <?php echo $adminSection === 'bookings' ? 'btn-success' : 'btn-outline-success'; ?> btn-sm">
                    <i class="bi bi-calendar2-week me-1"></i>Booking Requests
                </a>
                <a href="admin-schedule.php" class="btn <?php echo $adminSection === 'schedule' ? 'btn-success' : 'btn-outline-success'; ?> btn-sm">
                    <i class="bi bi-calendar3 me-1"></i>Employee Schedule
                </a>
                <a href="admin-pto.php" class="btn <?php echo $adminSection === 'pto' ? 'btn-success' : 'btn-outline-success'; ?> btn-sm">
                    <i class="bi bi-person-workspace me-1"></i>PTO Queue
                </a>
                <a href="admin-xml.php" class="btn <?php echo $adminSection === 'xml' ? 'btn-success' : 'btn-outline-success'; ?> btn-sm">
                    <i class="bi bi-filetype-xml me-1"></i>XML Tools
                </a>
            </div>
        </div>
    </div>
</div>
