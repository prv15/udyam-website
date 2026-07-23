<div class="resource-menu">
    <button class="resource-trigger" type="button" aria-expanded="false">
        <?= htmlspecialchars($header['resources_label'] ?? 'Resources') ?><span>⌄</span>
    </button>
    <div class="resource-dropdown">
        <?php foreach ($header['resource_links'] as $resourceItem): ?>
            <a href="<?= htmlspecialchars(url($resourceItem['url'] ?? '#')) ?>">
                <span class="resource-icon"><?= htmlspecialchars($resourceItem['icon'] ?? '◇') ?></span>
                <span><strong><?= htmlspecialchars($resourceItem['label'] ?? '') ?></strong><small><?= htmlspecialchars($resourceItem['description'] ?? '') ?></small></span>
                <b>→</b>
            </a>
        <?php endforeach; ?>
    </div>
</div>
