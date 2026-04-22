<?php
require_once __DIR__ . '/db.php';

/*
|--------------------------------------------------------------------------
| Session bootstrap
|--------------------------------------------------------------------------
| Start a PHP session once so login state can be shared across pages.
| We keep this here because every page that includes header.php will gain
| access to the current user automatically.
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Flash messages
|--------------------------------------------------------------------------
| Flash messages last for one request. This is useful after redirects.
*/
function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlashMessage(): ?array
{
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $message;
}

/*
|--------------------------------------------------------------------------
| Basic auth state helpers
|--------------------------------------------------------------------------
*/
function isLoggedIn(): bool
{
    return !empty($_SESSION['user']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function currentUserRole(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}


function getRoleHomePath(?string $role = null): string
{
    $role = $role ?? currentUserRole();

    switch ($role) {
        case 'client':
            return 'client-dashboard.php';
        case 'employee':
            return 'employee-dashboard.php';
        case 'admin':
            return 'admin-dashboard.php';
        default:
            return 'account.php';
    }
}

function getRoleHomeLabel(?string $role = null): string
{
    $role = $role ?? currentUserRole();

    switch ($role) {
        case 'client':
            return 'Dashboard';
        case 'employee':
            return 'Employee Dashboard';
        case 'admin':
            return 'Admin Dashboard';
        default:
            return 'My Account';
    }
}

function redirectTo(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function loginUserSession(array $user): void
{
    $_SESSION['user'] = [
        'user_id' => $user['user_id'] ?? null,
        'first_name' => $user['first_name'] ?? 'User',
        'last_name' => $user['last_name'] ?? '',
        'email' => $user['email'] ?? '',
        'role' => $user['role'] ?? 'client',
    ];
}

function logoutUserSession(): void
{
    unset($_SESSION['user']);
}

function requireLogin(?array $allowedRoles = null): void
{
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Please log in first to view that page.');
        redirectTo('login.php');
    }

    if ($allowedRoles !== null && !in_array(currentUserRole(), $allowedRoles, true)) {
        setFlashMessage('danger', 'You do not have permission to access that page.');
        redirectTo('account.php');
    }
}

/*
|--------------------------------------------------------------------------
| Demo accounts
|--------------------------------------------------------------------------
| These allow the pages to be shown in class even before the Users table is
| imported or seeded. Once the real database is ready, the real database is
| used first.
*/
function getDemoUsers(): array
{
    $passwordHash = password_hash('Password123!', PASSWORD_DEFAULT);

    return [
        'client.demo@nysparks.local' => [
            'user_id' => 1001,
            'first_name' => 'Casey',
            'last_name' => 'Client',
            'email' => 'client.demo@nysparks.local',
            'password_hash' => $passwordHash,
            'role' => 'client',
        ],
        'employee.demo@nysparks.local' => [
            'user_id' => 1002,
            'first_name' => 'Ellis',
            'last_name' => 'Employee',
            'email' => 'employee.demo@nysparks.local',
            'password_hash' => $passwordHash,
            'role' => 'employee',
        ],
        'admin.demo@nysparks.local' => [
            'user_id' => 1003,
            'first_name' => 'Avery',
            'last_name' => 'Admin',
            'email' => 'admin.demo@nysparks.local',
            'password_hash' => $passwordHash,
            'role' => 'admin',
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Database helpers for users
|--------------------------------------------------------------------------
*/
function findUserByEmail(string $email): ?array
{
    $db = getDbConnection();

    if ($db && tableExists($db, 'Users')) {
        $sql = "SELECT user_id, first_name, last_name, email, password_hash, role, account_status
                FROM Users
                WHERE email = ?
                LIMIT 1";

        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($user) {
                return $user;
            }
        }
    }

    $demoUsers = getDemoUsers();
    return $demoUsers[strtolower($email)] ?? null;
}

function databaseSupportsLiveUsers(): bool
{
    $db = getDbConnection();
    return (bool) ($db && tableExists($db, 'Users'));
}

function createClientUser(string $firstName, string $lastName, string $email, string $password, ?string $phone = null): array
{
    $db = getDbConnection();

    if (!$db || !tableExists($db, 'Users')) {
        return [
            'success' => false,
            'message' => 'Live registration is not ready yet. Import the Users table into MySQL first, or use the demo login on the login page.',
        ];
    }

    $email = strtolower(trim($email));

    if (findUserByEmail($email)) {
        return [
            'success' => false,
            'message' => 'That email address is already registered.',
        ];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $accountStatus = 'active';
    $role = 'client';

    $sql = "INSERT INTO Users (first_name, last_name, email, password_hash, role, phone, account_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Unable to prepare the registration query. Check the Users table structure in MySQL.',
        ];
    }

    $stmt->bind_param('sssssss', $firstName, $lastName, $email, $passwordHash, $role, $phone, $accountStatus);
    $success = $stmt->execute();
    $newUserId = $db->insert_id;
    $stmt->close();

    if (!$success) {
        return [
            'success' => false,
            'message' => 'Registration failed. Please double-check the database connection and schema.',
        ];
    }

    return [
        'success' => true,
        'message' => 'Account created successfully. You can now log in.',
        'user' => [
            'user_id' => $newUserId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'role' => $role,
        ],
    ];
}
