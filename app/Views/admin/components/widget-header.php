<?php

$title = $title ?? '';
$subtitle = $subtitle ?? '';
$actions = $actions ?? '';

?>

<div class="widget-header">

    <div class="widget-title-group">

        <h3><?= htmlspecialchars($title) ?></h3>

        <?php if ($subtitle): ?>

            <p><?= htmlspecialchars($subtitle) ?></p>

        <?php endif; ?>

    </div>

    <?php if ($actions): ?>

        <div class="widget-actions">

            <?= $actions ?>

        </div>

    <?php endif; ?>

</div>