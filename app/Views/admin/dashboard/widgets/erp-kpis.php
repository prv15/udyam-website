<?php
$kpis = [
    ['Partners', 'users-round', $erp['customers']['total'], $erp['customers']['active'] . ' active · ' . $erp['customers']['new'] . ' new this month', '/admin/customers', 'blue'],
    ['Subscriptions', 'badge-indian-rupee', $erp['subscriptions']['active'], $erp['subscriptions']['pending'] . ' pending · ' . $erp['renewalDue'] . ' renewal due', '/admin/subscription-plans', 'violet'],
    ['Applications', 'clipboard-list', array_sum($erp['applications']), $erp['applications']['under_review'] . ' under review · ' . $erp['applications']['in_progress'] . ' in progress', '/admin/applications', 'amber'],
    ['Collections', 'indian-rupee', '₹' . number_format((float) $erp['revenue']['month'], 0), ($erp['revenueTrend'] >= 0 ? '▲ ' : '▼ ') . number_format(abs((float) $erp['revenueTrend']), 1) . '% vs last month', '/admin/customers', 'green'],
    ['Payments', 'credit-card', $erp['payments']['successful'], $erp['payments']['pending'] . ' pending · ' . $erp['payments']['failed'] . ' failed', '/admin/customers', 'cyan'],
    ['Operations', 'folder-check', $erp['documents']['pending'], $erp['documents']['today'] . ' documents today · ' . $erp['funding']['tenders'] . ' live tenders', '/admin/documents', 'blue'],
    ['Funding updates', 'megaphone', $erp['funding']['notices'] + $erp['funding']['tenders'], $erp['funding']['notices'] . ' notices · ' . $erp['funding']['tenders'] . ' tenders live', '/admin/tenders', 'violet'],
];
?>
<section class="erp-kpi-grid" aria-label="Live ERP performance indicators"><?php foreach ($kpis as [$label, $icon, $value, $detail, $route, $tone]): ?><a href="<?= htmlspecialchars(url($route)) ?>" class="erp-kpi erp-kpi-<?= htmlspecialchars($tone) ?>"><span class="erp-kpi-icon"><i data-lucide="<?= htmlspecialchars($icon) ?>"></i></span><div><small><?= htmlspecialchars($label) ?></small><strong><?= htmlspecialchars((string) $value) ?></strong><p><?= htmlspecialchars($detail) ?></p></div><svg class="erp-sparkline" viewBox="0 0 86 26" aria-hidden="true"><path d="M1 21 C11 17 15 19 23 12 S37 17 46 9 S61 13 69 5 S80 9 85 2"></path></svg></a><?php endforeach; ?></section>
