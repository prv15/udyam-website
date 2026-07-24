<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>

<?php $adminCssVersion = (string) (@filemtime(ASSET_PATH . '/admin/css/admin.css') ?: '1'); ?>
<link rel="stylesheet" href="<?= htmlspecialchars(url('/assets/admin/css/admin.css') . '?v=' . $adminCssVersion, ENT_QUOTES, 'UTF-8') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://unpkg.com/lucide@latest"></script>

</head>

<body class="admin-shell">

<?php require __DIR__.'/../partials/sidebar.php'; ?>

<div class="main-wrapper">

<?php require __DIR__.'/../partials/topbar.php'; ?>

<main class="page-content">

<?php if ($message = \App\Core\Session::getFlash('success')): ?>
    <div class="alert alert-success" role="alert"><?= htmlspecialchars((string) $message) ?></div>
<?php endif; ?>

<?php if ($message = \App\Core\Session::getFlash('error')): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $message) ?></div>
<?php endif; ?>

<?php require $content; ?>

</main>

<?php require __DIR__.'/../partials/footer.php'; ?>

</div>

<?php require __DIR__.'/../partials/scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>
