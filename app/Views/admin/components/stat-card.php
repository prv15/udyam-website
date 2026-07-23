<?php

$title = $title ?? '';
$value = $value ?? '';
$footer = $footer ?? '';
$icon = $icon ?? 'circle';

?>

<div class="summary-card">

    <div class="summary-header">

        <span><?= htmlspecialchars($title) ?></span>

        <i data-lucide="<?= htmlspecialchars($icon) ?>"></i>

    </div>

    <div class="summary-value">

        <?= htmlspecialchars($value) ?>

    </div>

    <div class="summary-footer">

        <?= $footer ?>

    </div>

</div>