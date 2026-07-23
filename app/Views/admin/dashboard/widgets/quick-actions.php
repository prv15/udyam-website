<div class="panel">
    <div class="widget-header">
        <div class="widget-title-group"><h3>Quick Actions</h3><p>Frequently used shortcuts</p></div>
    </div>
    <div class="quick-actions-grid">
        <?php
        $actions = [
            ['/admin/pages/create', 'plus', 'Create Page'],
            ['/admin/services/create', 'briefcase-business', 'Add Service'],
            ['/admin/blog/create', 'newspaper', 'Write Blog'],
            ['/admin/media', 'image-plus', 'Upload Media'],
            ['/admin/team/create', 'users', 'Team Member'],
            ['/admin/newsletter/create', 'mail-plus', 'Subscriber'],
        ];
        foreach ($actions as [$route, $icon, $label]):
        ?>
            <a href="<?= htmlspecialchars(url($route)) ?>" class="quick-action">
                <i data-lucide="<?= htmlspecialchars($icon) ?>"></i><span><?= htmlspecialchars($label) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
