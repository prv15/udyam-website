<?php if (!empty($preview)): ?>
    <div style="padding:12px;background:#fff3cd;color:#664d03;text-align:center">
        Preview mode — this page is not necessarily published.
    </div>
<?php endif; ?>

<?php if (!empty($header)) require __DIR__ . '/partials/site-header.php'; ?>
<article class="website-page">
    <?php if (!empty($page['featured_image_url'])): ?>
        <img src="<?= htmlspecialchars(public_url((string) $page['featured_image_url']), ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars((string) $page['title'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <header>
        <h1><?= htmlspecialchars((string) $page['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if (!empty($page['excerpt'])): ?><p><?= nl2br(htmlspecialchars((string) $page['excerpt'], ENT_QUOTES, 'UTF-8')) ?></p><?php endif; ?>
    </header>
    <div class="page-content"><?= (string) $page['content'] ?></div>
</article>
<?php if (!empty($footer)) require __DIR__ . '/partials/site-footer.php'; ?>
