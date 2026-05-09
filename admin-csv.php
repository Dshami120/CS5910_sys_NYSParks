<?php
// Load project setup.
require 'bootstrap.php';
$user = require_role($db, 'admin');
$allowed = ['parks','users','fields','events','news','bookings','attendance','payments','employee_schedules','pto_requests','employees'];
$dateColumns = [
    'parks'=>'created_at',
    'users'=>'created_at',
    'fields'=>'created_at',
    'events'=>'start_datetime',
    'news'=>'published_date',
    'bookings'=>'start_datetime',
    'attendance'=>'registered_at',
    'payments'=>'created_at',
    'employee_schedules'=>'shift_date',
    'pto_requests'=>'start_date',
    'employees'=>'created_at',
];
if (isset($_GET['download']) && $_GET['download'] === '1') {
    $dataset = get('dataset', 'events');
    if (!in_array($dataset, $allowed, true)) { $dataset = 'events'; }
    $table = $dataset === 'employees' ? 'users' : $dataset;
    $where = [];
    $params = [];
    if ($dataset === 'employees') {
        $where[] = "role = 'employee'";
    }
    $dateColumn = $dateColumns[$dataset] ?? null;
    $dateFrom = get('date_from');
    $dateTo = get('date_to');
    if ($dateColumn && $dateFrom !== '') {
        $where[] = "{$dateColumn} >= ?";
        $params[] = $dateFrom;
    }
    if ($dateColumn && $dateTo !== '') {
        $where[] = "{$dateColumn} < DATE_ADD(?, INTERVAL 1 DAY)";
        $params[] = $dateTo;
    }
    $sql = "SELECT * FROM {$table}" . ($where ? " WHERE " . implode(" AND ", $where) : "");
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $columns = $rows ? array_keys($rows[0]) : $db->query("SHOW COLUMNS FROM {$table}")->fetchAll(PDO::FETCH_COLUMN);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . csv_title($dataset) . '"');
    $output = fopen('php://output', 'w');
    fputcsv($output, $columns);
    foreach ($rows as $row) {
        fputcsv($output, array_map(fn($column) => $row[$column] ?? '', $columns));
    }
    fclose($output);
    exit;
}
$recent = array_map(fn($name) => ['name'=>csv_title($name),'dataset'=>ucwords(str_replace('_',' ',$name)),'date'=>date('m/d/Y'),'status'=>'Ready'], $allowed);
?>
<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Admin CSV Export';
$bodyPage = 'admin-csv';
$extraHead = '';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="py-5">
    <section class="container">
      <header class="mb-4">
        <p class="section-kicker mb-2">Admin portal</p>
        <h1 class="h2 fw-bold mb-1">CSV Export Center</h1>
        <p class="text-muted mb-0">Prepare CSV exports for events, bookings, and park records.</p>
          <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
              <div></div>
              <div class="d-flex flex-wrap gap-2">
                  <a class="btn btn-outline-dark" href="admin-dashboard.php"><i class="bi bi-speedometer2"></i> Admin Dash</a>
                  <a class="btn btn-outline-dark" href="admin-employee-schedule.php"><i class="bi bi-calendar3"></i> Employee Schedules</a>
                  <a class="btn btn-outline-dark" href="admin-pto.php"><i class="bi bi-briefcase"></i> PTO Requests</a>
                  <a class="btn btn-outline-dark" href="admin-bookings.php"><i class="bi bi-journal-check"></i> Client Bookings</a>
                  <a class="btn btn-outline-dark" href="admin-news.php"><i class="bi bi-journal-check"></i> News Manager</a>
                  <a class="btn btn-outline-dark" href="admin-employee-accounts.php"><i class="bi bi-journal-check"></i> Employee Accounts</a>
                  <a class="btn btn-success" href="admin-csv.php"><i class="bi bi-journal-check"></i> CSV</a>
              </div>
      </header>

      <section class="row g-4">
        <article class="col-lg-4">
          <section class="form-panel p-4 h-100">
            <h2 class="h5 fw-bold mb-3">Generate export</h2>
            <form method="get">
              <label class="form-label">Dataset</label>
              <select name="dataset" class="form-select mb-3">
                <?php foreach ($allowed as $datasetName): ?><option value="<?= e($datasetName) ?>"><?= e(ucwords(str_replace("_", " ", $datasetName))) ?></option><?php endforeach; ?>
              </select>
              <label class="form-label">Date range</label>
              <input type="date" name="date_from" class="form-control mb-3" />
              <input type="date" name="date_to" class="form-control mb-4" />
              <input type="hidden" name="download" value="1" /><button class="btn btn-success rounded-pill w-100">Generate CSV</button>
            </form>
          </section>
        </article>
        <article class="col-lg-8">
          <section class="list-shell">
            <section class="p-4 border-bottom">
              <h2 class="h5 fw-bold mb-1">Recent exports</h2>
              <p class="text-muted mb-0">Starter log table for generated CSV files</p>
            </section>
            <?php foreach ($recent as $row): ?>
<section class="list-row p-4"><section class="row g-3"><article class="col-md-4 fw-semibold"><?= e($row['name']) ?></article><article class="col-md-3 text-muted"><?= e($row['dataset']) ?></article><article class="col-md-3 text-muted"><?= e($row['date']) ?></article><article class="col-md-2"><span class="status-pill status-approved"><?= e($row['status']) ?></span></article></section></section>
<?php endforeach; ?>
            </section>
          </section>
        </article>
      </section>
    </section>
  </main>
<?php include __DIR__ . '/includes/footer.php'; ?>
