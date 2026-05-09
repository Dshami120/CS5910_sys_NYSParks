<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();
$_SESSION = [];

// Remove the session cookie.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'] ?? '',
        (bool) $params['secure'],
        (bool) $params['httponly']
    );
}

session_destroy();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>

<?php
$pageTitle = 'NYS Parks - Logout';
$bodyPage = 'logout';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main class="py-5">
        <section class="container">
            <section class="row justify-content-center">
                <article class="col-lg-7">
                    <section class="auth-panel p-4 p-lg-5 text-center">

                        <!-- Logout message -->
                        <p class="section-kicker mb-2">Session ended</p>
                        <h1 class="h2 fw-bold mb-3">You have been logged out</h1>
                        <p class="text-muted mb-4">Your session was fully cleared. You must log in again to access any dashboard.</p>

                        <!-- Logout links -->
                        <section class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="login.php" class="btn btn-success rounded-pill px-4">Log In Again</a>
                            <a href="index.php" class="btn btn-outline-dark rounded-pill px-4">Back to Home</a>
                        </section>

                    </section>
                </article>
            </section>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>