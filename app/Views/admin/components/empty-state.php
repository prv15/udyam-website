<?php

$title = $title ?? '';
$message = $message ?? '';
$button = $button ?? '';

?>

<div class="empty-state">

    <i data-lucide="inbox"></i>

    <h4><?= htmlspecialchars($title) ?></h4>

    <p><?= htmlspecialchars($message) ?></p>

    <?= $button ?>

</div>