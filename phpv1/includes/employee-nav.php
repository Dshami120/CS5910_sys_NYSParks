<?php
$employeeSection = $employeeSection ?? '';
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="employee-dashboard.php" class="btn <?php echo $employeeSection === 'dashboard' ? 'btn-success' : 'btn-outline-success'; ?> btn-sm">
                <i class="bi bi-calendar3 me-1"></i>My Schedule
            </a>
            <a href="employee-pto.php" class="btn <?php echo $employeeSection === 'pto' ? 'btn-success' : 'btn-outline-success'; ?> btn-sm">
                <i class="bi bi-person-workspace me-1"></i>Apply PTO
            </a>
        </div>
    </div>
</div>
