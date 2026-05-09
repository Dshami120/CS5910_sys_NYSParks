<?php
// Load project setup.
require 'bootstrap.php';
$user = require_role($db, 'admin');

function employee_accounts_path(array $params = []): string {
    $accountFilterValue = $params['account_filter'] ?? post('account_filter', get('account_filter'));
    $parkFilterValue = $params['park_filter'] ?? post('park_filter', get('park_filter'));
    $employeeValue = array_key_exists('employee_id', $params) ? $params['employee_id'] : get('employee_id');
    $query = array_filter([
        'employee_id' => $employeeValue,
        'account_filter' => $accountFilterValue,
        'park_filter' => $parkFilterValue,
    ], fn($value) => $value !== null && $value !== '' && $value !== 'all' && $value !== '0');
    return 'admin-employee-accounts.php' . ($query ? '?' . http_build_query($query) : '');
}

// Handle submitted form actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');
    if ($action === 'create_employee') {
        [$first,$last] = split_name(post('full_name'));
        if (!$first || !$last || !valid_email(post('email')) || strlen(post('password')) < 8) {
            flash_set('error', 'Enter a full name, valid email, and password of at least 8 characters.');
            redirect(employee_accounts_path(['employee_id' => null]));
        }
        $email = strtolower(post('email'));
        $emailCheck = $db->prepare("SELECT COUNT(*) FROM users WHERE email=?");
        $emailCheck->execute([$email]);
        if ((int)$emailCheck->fetchColumn() > 0) {
            flash_set('error', 'Email already exists.');
            redirect(employee_accounts_path(['employee_id' => null]));
        }
        $db->prepare("INSERT INTO users (first_name,last_name,email,password_hash,role,phone,birthdate,notes,park_id,account_status) VALUES (?,?,?,?, 'employee',?,?,?,?, 'active')")
            ->execute([$first,$last,$email,password_hash(post('password'), PASSWORD_DEFAULT),post('phone') ?: null, post('birthdate') ?: null, post('notes') ?: null, (int) post('park_id') ?: null]);
        $newId = (int) $db->lastInsertId();
        flash_set('success', 'Employee account created.');
        redirect(employee_accounts_path(['employee_id' => $newId]));
    }
    if ($action === 'update_employee') {
        $id = (int) post('employee_id');
        [$first,$last] = split_name(post('full_name'));
        if ($id <= 0) {
            flash_set('error', 'Select an employee account to update.');
            redirect(employee_accounts_path(['employee_id' => null]));
        }
        if (!$first || !$last || !valid_email(post('email'))) {
            flash_set('error', 'Enter a full name and valid email.');
            redirect(employee_accounts_path(['employee_id' => $id]));
        }
        $exists = $db->prepare("SELECT COUNT(*) FROM users WHERE id=? AND role='employee'");
        $exists->execute([$id]);
        if ((int)$exists->fetchColumn() === 0) {
            flash_set('error', 'Employee account not found.');
            redirect(employee_accounts_path(['employee_id' => null]));
        }
        $email = strtolower(post('email'));
        $emailCheck = $db->prepare("SELECT COUNT(*) FROM users WHERE email=? AND id<>?");
        $emailCheck->execute([$email, $id]);
        if ((int)$emailCheck->fetchColumn() > 0) {
            flash_set('error', 'Email already exists.');
            redirect(employee_accounts_path(['employee_id' => $id]));
        }
        $db->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, birthdate=?, notes=?, park_id=? WHERE id=? AND role='employee'")
            ->execute([$first,$last,$email,post('phone') ?: null, post('birthdate') ?: null, post('notes') ?: null, (int) post('park_id') ?: null, $id]);
        if (post('password') !== '') {
            if (strlen(post('password')) < 8) {
                flash_set('error', 'Password must be at least 8 characters.');
                redirect(employee_accounts_path(['employee_id' => $id]));
            }
            $db->prepare("UPDATE users SET password_hash=? WHERE id=? AND role='employee'")->execute([password_hash(post('password'), PASSWORD_DEFAULT), $id]);
        }
        flash_set('success', 'Employee account updated.');
        redirect(employee_accounts_path(['employee_id' => $id]));
    }
    if ($action === 'disable_employee') {
        $id = (int) post('employee_id');
        if ($id <= 0) {
            flash_set('error', 'Select an employee account to disable.');
            redirect(employee_accounts_path(['employee_id' => null]));
        }
        $exists = $db->prepare("SELECT COUNT(*) FROM users WHERE id=? AND role='employee'");
        $exists->execute([$id]);
        if ((int)$exists->fetchColumn() === 0) {
            flash_set('error', 'Employee account not found.');
            redirect(employee_accounts_path(['employee_id' => null]));
        }
        $db->prepare("UPDATE users SET account_status='disabled' WHERE id=? AND role='employee'")->execute([$id]);
        cancel_future_schedules_for_employee($db, $id);
        flash_set('success', 'Employee account disabled. Future scheduled shifts were cancelled.');
        redirect(employee_accounts_path(['employee_id' => $id]));
    }
    if ($action === 'reactivate_employee') {
        $id = (int) post('employee_id');
        if ($id <= 0) {
            flash_set('error', 'Select an employee account to reactivate.');
            redirect(employee_accounts_path(['employee_id' => null]));
        }
        $exists = $db->prepare("SELECT COUNT(*) FROM users WHERE id=? AND role='employee'");
        $exists->execute([$id]);
        if ((int)$exists->fetchColumn() === 0) {
            flash_set('error', 'Employee account not found.');
            redirect(employee_accounts_path(['employee_id' => null]));
        }
        $db->prepare("UPDATE users SET account_status='active' WHERE id=? AND role='employee'")->execute([$id]);
        flash_set('success', 'Account reactivated.');
        redirect(employee_accounts_path(['employee_id' => $id]));
    }
}

$parks = park_options($db);
$accountFilter = get('account_filter', 'all');
$parkFilter = (int) get('park_filter');
$employeeParams = [];
$employeeSql = "SELECT u.*, p.name AS park_name FROM users u LEFT JOIN parks p ON p.id=u.park_id WHERE u.role='employee'";
if ($accountFilter === 'active' || $accountFilter === 'disabled') {
    $employeeSql .= " AND u.account_status=?";
    $employeeParams[] = $accountFilter;
}
if ($parkFilter > 0) {
    $employeeSql .= " AND u.park_id=?";
    $employeeParams[] = $parkFilter;
}
$employeeSql .= " ORDER BY u.account_status='disabled', u.first_name, u.last_name";
$stmt = $db->prepare($employeeSql);
$stmt->execute($employeeParams);
$employees = $stmt->fetchAll();

$allStmt = $db->query("SELECT u.*, p.name AS park_name FROM users u LEFT JOIN parks p ON p.id=u.park_id WHERE u.role='employee' ORDER BY u.account_status='disabled', u.first_name, u.last_name");
$allEmployees = $allStmt->fetchAll();
$activeEmployees = array_values(array_filter($allEmployees, fn($emp) => ($emp['account_status'] ?? '') === 'active'));
$disabledEmployees = array_values(array_filter($allEmployees, fn($emp) => ($emp['account_status'] ?? '') === 'disabled'));

$selectedId = (int) get('employee_id');
$selected = null;
if ($selectedId) {
    $stmt = $db->prepare("SELECT u.*, p.name AS park_name FROM users u LEFT JOIN parks p ON p.id=u.park_id WHERE u.id=? AND u.role='employee'");
    $stmt->execute([$selectedId]);
    $selected = $stmt->fetch();
}
?>
<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Employee Accounts';
$bodyPage = 'admin-employee-accounts';
$extraHead = '';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container py-5">
    <?php if ($flash): ?><div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
    <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-2">Admin Workspace</p>
            <h1 class="display-6 fw-bold mb-2">Employee Accounts</h1>
            <p class="text-muted mb-0">Create new employee accounts, select existing users, update details, or disable accounts.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-dark" href="admin-dashboard.php"><i class="bi bi-speedometer2"></i> Admin Dash</a>
            <a class="btn btn-outline-dark" href="admin-employee-schedule.php"><i class="bi bi-calendar3"></i> Employee Schedules</a>
            <a class="btn btn-outline-dark" href="admin-pto.php"><i class="bi bi-briefcase"></i> PTO Requests</a>
            <a class="btn btn-outline-dark" href="admin-bookings.php"><i class="bi bi-journal-check"></i> Client Bookings</a>
            <a class="btn btn-outline-dark" href="admin-news.php"><i class="bi bi-newspaper"></i> News Manager</a>
            <a class="btn btn-success" href="admin-employee-accounts.php"><i class="bi bi-people"></i> Employee Accounts</a>
            <a class="btn btn-outline-dark" href="admin-csv.php"><i class="bi bi-filetype-csv"></i> CSV</a>
        </div>
    </section>

    <section class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                <div>
                    <p class="eyebrow mb-1">Employee Account Manager</p>
                    <h2 class="h3 fw-bold mb-1"><?= $selected ? 'Update Selected Employee Account' : 'Create New Employee Account' ?></h2>
                </div>
                <a href="admin-employee-accounts.php" class="btn btn-outline-dark align-self-start"><i class="bi bi-plus-circle"></i> Create New Account</a>
            </div>

            <div class="row g-4">
                <div class="col-xl-5">
                    <div class="account-panel h-100">
                        <h3 class="h5 fw-bold mb-3">Find Employee Account</h3>
                        <div class="vstack gap-3">
                            <form method="get">
                                <label class="form-label">Employee Dropdown</label>
                                <input type="hidden" name="account_filter" value="<?= e($accountFilter) ?>" />
                                <input type="hidden" name="park_filter" value="<?= (int)$parkFilter ?>" />
                                <select class="form-select form-select-lg" name="employee_id" onchange="this.form.submit()">
                                    <option value="">Select employee account</option>
                                    <?php if ($activeEmployees): ?>
                                        <optgroup label="Active Employees">
                                            <?php foreach ($activeEmployees as $emp): ?>
                                                <option value="<?= (int)$emp['id'] ?>" <?= $selected && (int)$selected['id'] === (int)$emp['id'] ? 'selected' : '' ?>><?= e(full_name($emp)) ?> — <?= e($emp['email']) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                    <?php if ($disabledEmployees): ?>
                                        <optgroup label="Disabled Employees">
                                            <?php foreach ($disabledEmployees as $emp): ?>
                                                <option value="<?= (int)$emp['id'] ?>" <?= $selected && (int)$selected['id'] === (int)$emp['id'] ? 'selected' : '' ?>><?= e(full_name($emp)) ?> — <?= e($emp['email']) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                </select>
                            </form>

                            <form method="get" class="row g-3">
                                <?php if ($selected): ?><input type="hidden" name="employee_id" value="<?= (int)$selected['id'] ?>" /><?php endif; ?>
                                <div class="col-md-6">
                                    <label class="form-label">Quick Filter</label>
                                    <select class="form-select" name="account_filter" onchange="this.form.submit()">
                                        <option value="all" <?= $accountFilter === 'all' ? 'selected' : '' ?>>All Employees</option>
                                        <option value="active" <?= $accountFilter === 'active' ? 'selected' : '' ?>>Active Employees</option>
                                        <option value="disabled" <?= $accountFilter === 'disabled' ? 'selected' : '' ?>>Disabled Employees</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Assigned Park</label>
                                    <select class="form-select" name="park_filter" onchange="this.form.submit()">
                                        <option value="0">All Parks</option>
                                        <?php foreach ($parks as $park): ?><option value="<?= (int)$park['id'] ?>" <?= $parkFilter === (int)$park['id'] ? 'selected' : '' ?>><?= e($park['name']) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                            </form>

                            <div class="vstack gap-2">
                                <?php foreach ($employees as $emp): ?>
                                    <a class="text-decoration-none text-dark border rounded-3 p-3 <?= $selected && (int)$selected['id']===(int)$emp['id'] ? 'bg-light' : '' ?>" href="<?= e(employee_accounts_path(['employee_id' => (int)$emp['id']])) ?>">
                                        <strong class="d-block"><?= e(full_name($emp)) ?></strong>
                                        <span class="small text-muted"><?= e(ucfirst($emp['account_status'])) ?> · <?= e($emp['park_name'] ?? 'Unassigned') ?></span>
                                    </a>
                                <?php endforeach; ?>
                                <?php if (!$employees): ?><p class="text-muted mb-0">No employee accounts match these filters.</p><?php endif; ?>
                            </div>

                            <div class="mini-panel">
                                <strong class="d-block mb-2">Selected Account Preview</strong>
                                <?php if ($selected): ?>
                                    <p class="small text-muted mb-1">Name: <?= e(full_name($selected)) ?></p>
                                    <p class="small text-muted mb-1">Email: <?= e($selected['email']) ?></p>
                                    <p class="small text-muted mb-1">Birthdate: <?= e((string)$selected['birthdate']) ?: '—' ?></p>
                                    <p class="small text-muted mb-1">Park: <?= e((string)($selected['park_name'] ?? 'Unassigned')) ?></p>
                                    <p class="small text-muted mb-0">Status: <?= e(ucfirst($selected['account_status'])) ?></p>
                                <?php else: ?>
                                    <p class="small text-muted mb-0">No employee selected. The form is ready for a new employee account.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="account-panel h-100">
                        <h3 class="h5 fw-bold mb-3">Employee Account Form</h3>
                        <form class="row g-3" method="post">
                            <input type="hidden" name="employee_id" value="<?= (int)($selected['id'] ?? 0) ?>" />
                            <input type="hidden" name="account_filter" value="<?= e($accountFilter) ?>" />
                            <input type="hidden" name="park_filter" value="<?= (int)$parkFilter ?>" />
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control form-control-lg" placeholder="Jordan Lee" value="<?= e($selected ? full_name($selected) : '') ?>" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="employee@nysparks.gov" value="<?= e($selected['email'] ?? '') ?>" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control form-control-lg" placeholder="<?= $selected ? 'Leave blank to keep current password' : 'Set initial password' ?>" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Birthdate</label>
                                <input type="date" name="birthdate" class="form-control form-control-lg" value="<?= e((string)($selected['birthdate'] ?? '')) ?>" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Assigned Park</label>
                                <select name="park_id" class="form-select form-select-lg">
                                    <option value="">Select park</option>
                                    <?php foreach ($parks as $park): ?><option value="<?= (int)$park['id'] ?>" <?= (int)($selected['park_id'] ?? 0)===(int)$park['id']?'selected':'' ?>><?= e($park['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control form-control-lg" value="<?= e((string)($selected['phone'] ?? '')) ?>" />
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Optional employee account notes, department details, or hiring status."><?= e((string)($selected['notes'] ?? '')) ?></textarea>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-3">
                                <button type="submit" name="action" value="create_employee" class="btn btn-success btn-lg" <?= $selected ? 'disabled' : '' ?>>Create Account</button>
                                <button type="submit" name="action" value="update_employee" class="btn btn-outline-dark btn-lg" <?= $selected ? '' : 'disabled' ?>>Update Account</button>
                                <button type="submit" name="action" value="disable_employee" class="btn btn-outline-danger btn-lg" data-confirm="Disable this employee account?" <?= $selected && ($selected['account_status'] ?? '') !== 'disabled' ? '' : 'disabled' ?>>Disable Account</button>
                                <button type="submit" name="action" value="reactivate_employee" class="btn btn-outline-success btn-lg" <?= $selected && ($selected['account_status'] ?? '') === 'disabled' ? '' : 'disabled' ?>>Reactivate Account</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
