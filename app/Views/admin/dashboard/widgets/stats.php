<div class="stats-grid">
    <?php
    $cards = [
        ['Website', 'globe-2', number_format((int) $stats['publishedPages']), $stats['pages'] . ' pages · ' . $stats['services'] . ' services', 'blue'],
        ['Customers', 'users-round', number_format((int) $stats['customers']), $stats['newMessages'] . ' new enquiries', 'violet'],
        ['Subscriptions', 'badge-indian-rupee', number_format((int) $stats['activeSubscriptions']), 'Currently active', 'cyan'],
        ['Applications', 'clipboard-list', number_format((int) $stats['applications']), $stats['pendingApplications'] . ' pending review', 'amber'],
        ['Revenue this month', 'indian-rupee', '₹' . number_format((float) $stats['monthRevenue'], 0), 'Successful payments', 'green'],
        ['Outstanding', 'receipt-text', '₹' . number_format((float) $stats['outstanding'], 0), 'Issued, partial and overdue', 'red'],
    ];
    foreach ($cards as [$label, $icon, $value, $footer, $tone]):
    ?>
        <a class="summary-card summary-card-<?= htmlspecialchars($tone) ?>" href="<?= htmlspecialchars(url(match ($label) {
            'Website' => '/admin/pages',
            'Customers' => '/admin/customers',
            'Subscriptions' => '/admin/subscription-plans',
            'Applications' => '/admin/applications',
            default => '/admin/customers',
        })) ?>">
            <div class="summary-header">
                <span><?= htmlspecialchars($label) ?></span>
                <span class="summary-icon"><i data-lucide="<?= htmlspecialchars($icon) ?>"></i></span>
            </div>
            <div class="summary-value"><?= htmlspecialchars((string) $value) ?></div>
            <div class="summary-footer"><span><?= htmlspecialchars($footer) ?></span><i data-lucide="arrow-up-right"></i></div>
        </a>
    <?php endforeach; ?>
</div>
