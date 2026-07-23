<div class="stats-grid">
    <?php
    $cards = [
        ['Website Pages', 'file-text', $stats['pages'], $stats['media'] . ' media files'],
        ['Services', 'briefcase-business', $stats['services'], 'Active service records'],
        ['Applications', 'clipboard-list', $stats['applications'], $stats['pendingApplications'] . ' pending review'],
        ['Customers', 'users-round', $stats['customers'], $stats['newMessages'] . ' open enquiries'],
    ];
    foreach ($cards as [$label, $icon, $value, $footer]):
    ?>
        <div class="summary-card">
            <div class="summary-header">
                <span><?= htmlspecialchars($label) ?></span>
                <i data-lucide="<?= htmlspecialchars($icon) ?>"></i>
            </div>
            <div class="summary-value"><?= number_format((int) $value) ?></div>
            <div class="summary-footer"><span><?= htmlspecialchars($footer) ?></span></div>
        </div>
    <?php endforeach; ?>
</div>
