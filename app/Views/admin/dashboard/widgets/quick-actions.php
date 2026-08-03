<div class="panel quick-actions-panel">
    <div class="widget-header">
        <div class="widget-title-group"><span class="widget-label">SHORTCUTS</span><h3>Create & manage</h3><p>Common operational actions</p></div>
    </div>
    <div class="quick-actions-grid">
        <?php
        $actions = [
            ['/admin/pages/create', 'file-plus-2', 'New page'],
            ['/admin/subscription-plans/create', 'badge-indian-rupee', 'New plan'],
            ['/admin/services/create', 'briefcase-business', 'New service'],
            ['/admin/customers/create', 'user-plus', 'New customer'],
            ['/admin/applications/create', 'clipboard-plus', 'New application'],
            ['/admin/media', 'image-plus', 'Upload media'],
        ];
        foreach ($actions as [$route, $icon, $label]):
        ?>
            <a href="<?= htmlspecialchars(url($route)) ?>" class="quick-action">
                <i data-lucide="<?= htmlspecialchars($icon) ?>"></i><span><?= htmlspecialchars($label) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
