<?php
require_once __DIR__ . '/includes/auth.php';
logoutUserSession();
setFlashMessage('success', 'You have been logged out.');
redirectTo('login.php');
