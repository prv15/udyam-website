<?php
$errors = \App\Core\Session::getFlash('form_errors', []);
$old = \App\Core\Session::getFlash('old', []);
$success = \App\Core\Session::getFlash('success');
$error = \App\Core\Session::getFlash('error');
$cssVersion = (string)(@filemtime(ASSET_PATH . '/customer/portal.css') ?: '1');
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($title) ?> | Udyam Ventures</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cormorant+Garamond:wght@600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars(url('/assets/customer/portal.css')) ?>?v=<?= rawurlencode($cssVersion) ?>">
<script src="https://unpkg.com/lucide@latest"></script></head>
<body class="customer-auth"><main class="auth-wrap">
<section class="auth-story"><a href="<?= htmlspecialchars(url('/')) ?>"><img src="<?= htmlspecialchars(url('/uploads/media/original/home/udyam-ventures-logo-cropped.png')) ?>" alt="Udyam Ventures"></a>
<div><span>Secure Customer Workspace</span><h1>Clarity across every service, document and payment.</h1><p>Your unified portal for advisory services, subscriptions, applications, invoices and collaboration with Udyam Ventures.</p></div>
<small>Protected access · Enterprise-grade customer experience</small></section>
<section class="auth-panel"><div class="auth-card">
<?php if($success):?><div class="portal-alert success"><?=htmlspecialchars($success)?></div><?php endif;?>
<?php if($error):?><div class="portal-alert error"><?=htmlspecialchars($error)?></div><?php endif;?>
<?php if($errors):?><div class="portal-alert error"><?php foreach($errors as $message):?><div><?=htmlspecialchars($message)?></div><?php endforeach;?></div><?php endif;?>
<?php require $formView; ?>
</div></section></main><script>lucide.createIcons();</script></body></html>
