<div class="media-page">

    <?php require __DIR__ . '/widgets/toolbar.php'; ?>

    <?php if (empty($media)): ?>

        <?php require __DIR__ . '/widgets/empty-state.php'; ?>

    <?php else: ?>

        <?php require __DIR__ . '/widgets/grid.php'; ?>

        <?php require __DIR__ . '/widgets/pagination.php'; ?>

    <?php endif; ?>

    <?php require __DIR__ . '/widgets/upload-modal.php'; ?>

</div>