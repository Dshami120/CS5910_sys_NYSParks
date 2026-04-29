<?php
declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = '127.0.0.1';
    $port = '3306';
    $dbname = 'nys_parks';
    $username = 'root';
    $password = '';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    return $pdo;
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
}

function prevent_private_cache(): void {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function current_user(PDO $db): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $db->prepare("SELECT u.*, p.name AS park_name FROM users u LEFT JOIN parks p ON p.id = u.park_id WHERE u.id = ? AND u.account_status='active'");
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        logout_user();
        return null;
    }
    return $user;
}

function require_login(PDO $db): array {
    prevent_private_cache();
    $user = current_user($db);
    if (!$user) {
        flash_set('error', 'Please log in first.');
        header('Location: login.php');
        exit;
    }
    return $user;
}

function require_role(PDO $db, array|string $roles): array {
    $user = require_login($db);
    $roles = (array) $roles;
    if (!in_array($user['role'], $roles, true)) {
        flash_set('error', 'You are not allowed to access that page.');
        header('Location: index.php');
        exit;
    }
    return $user;
}

function e(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash_set(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array {
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function format_date(?string $date, string $format = 'm/d/Y'): string {
    if (!$date) return '—';
    return date($format, strtotime($date));
}
function format_datetime(?string $date, string $format = 'm/d/Y g:i A'): string {
    if (!$date) return '—';
    return date($format, strtotime($date));
}
function full_name(array $user): string {
    return trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
}
function redirect(string $path): void {
    header("Location: {$path}");
    exit;
}
function post(string $key, string $default=''): string {
    return trim((string) ($_POST[$key] ?? $default));
}
function get(string $key, string $default=''): string {
    return trim((string) ($_GET[$key] ?? $default));
}
function valid_email(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}
function split_name(string $full): array {
    $parts = preg_split('/\s+/', trim($full)) ?: [];
    $first = $parts[0] ?? '';
    $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    return [$first, $last];
}
function user_options(PDO $db, string $role='employee'): array {
    $stmt = $db->prepare("SELECT u.*, p.name AS park_name FROM users u LEFT JOIN parks p ON p.id=u.park_id WHERE u.role = ? AND u.account_status='active' ORDER BY u.first_name, u.last_name");
    $stmt->execute([$role]);
    return $stmt->fetchAll();
}
function park_options(PDO $db): array {
    return $db->query("SELECT * FROM parks ORDER BY name")->fetchAll();
}
function booking_status_class(string $status): string {
    return match($status){
        'approved','confirmed','completed','attended','registered' => 'status-approved',
        'denied','cancelled','no_show' => 'status-denied',
        default => 'status-pending',
    };
}
function number_only(string $value): string {
    return preg_replace('/\D+/', '', $value) ?? '';
}
function validate_card(string $number, string $month, string $year, string $cvv=''): ?string {
    $digits = number_only($number);
    if (strlen($digits) < 13 || strlen($digits) > 19) {
        return 'Card number must be 13 to 19 digits.';
    }
    $cvvDigits = number_only($cvv);
    if (!preg_match('/^[0-9]{3,4}$/', $cvvDigits)) {
        return 'CVV must be 3 or 4 digits.';
    }
    if (!ctype_digit($month) || !ctype_digit($year)) {
        return 'Expiration month and year are required.';
    }
    $m = (int) $month; $y = (int) $year;
    if ($m < 1 || $m > 12) return 'Expiration month must be between 1 and 12.';
    $currentYear = (int) date('Y');
    $currentMonth = (int) date('n');
    if ($y < $currentYear || ($y === $currentYear && $m < $currentMonth)) {
        return 'Card expiration must be this month or later.';
    }
    return null;
}
function csv_title(string $dataset): string {
    return match($dataset){
        'parks' => 'parks_export.xml',
        'users' => 'users_export.xml',
        'fields' => 'fields_export.xml',
        'events' => 'events_export.xml',
        'news' => 'news_export.xml',
        'bookings' => 'bookings_export.xml',
        'attendance' => 'attendance_export.xml',
        'payments' => 'payments_export.xml',
        'employee_schedules' => 'employee_schedules_export.xml',
        'pto_requests' => 'pto_requests_export.xml',
        'employees' => 'employees_export.xml',
        default => preg_replace('/[^a-z0-9_]+/i', '_', $dataset) . '_export.xml',
    };
}


$db = db();
$currentUser = current_user($db);
$flash = pull_flash();
?>
