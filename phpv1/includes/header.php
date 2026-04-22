<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

// Allow each page to set its own title.
$pageTitle = $pageTitle ?? SITE_NAME;
$bodyClass = trim(($bodyClass ?? '') . ' page-' . preg_replace('/[^a-z0-9\-]+/', '-', strtolower($activePage ?? pathinfo($_SERVER['PHP_SELF'] ?? 'index.php', PATHINFO_FILENAME))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#198754">
    <title><?php echo e($pageTitle); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body class="<?php echo e($bodyClass); ?>">
