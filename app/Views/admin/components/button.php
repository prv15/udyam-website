<?php

$type = $type ?? 'primary';
$icon = $icon ?? '';
$text = $text ?? '';

?>

<button class="btn btn-<?= $type ?>">

    <?php if($icon): ?>

        <i data-lucide="<?= htmlspecialchars($icon) ?>"></i>

    <?php endif; ?>

    <?= htmlspecialchars($text) ?>

</button>