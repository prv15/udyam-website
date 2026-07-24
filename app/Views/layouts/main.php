<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php if (!empty($metaDescription)): ?><meta name="description" content="<?= htmlspecialchars((string) $metaDescription, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
<?php if (!empty($metaKeywords)): ?><meta name="keywords" content="<?= htmlspecialchars((string) $metaKeywords, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
<title><?= htmlspecialchars((string) ($title ?? 'Udyam Ventures'), ENT_QUOTES, 'UTF-8'); ?></title>
<?php $websiteCssVersion = (string) (@filemtime(ASSET_PATH . '/website/css/website.css') ?: '1'); ?>
<link rel="stylesheet" href="<?= htmlspecialchars(url('/assets/website/css/website.css') . '?v=' . $websiteCssVersion, ENT_QUOTES, 'UTF-8') ?>">

</head>

<body class="<?= htmlspecialchars((string) ($bodyClass ?? ''), ENT_QUOTES, 'UTF-8') ?>">

<?= $content ?>

</body>
</html>
