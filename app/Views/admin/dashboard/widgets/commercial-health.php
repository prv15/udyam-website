<?php
$currentRevenue = (float) $stats['monthRevenue'];
$previousRevenue = (float) $business['previousMonthRevenue'];
$revenueChange = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : null;
?>
<div class="panel health-panel">
    <div class="widget-header">
        <div class="widget-title-group"><span class="widget-label">BUSINESS HEALTH</span><h3>Commercial pulse</h3><p>Items requiring management attention</p></div>
        <span class="health-score"><?= number_format((float) $business['collectionRate'], 0) ?>%</span>
    </div>
    <div class="health-progress"><span style="width:<?= min(100, max(0, (float) $business['collectionRate'])) ?>%"></span></div>
    <small class="health-caption">Lifetime invoice collection rate</small>
    <div class="health-list">
        <a href="<?= htmlspecialchars(url('/admin/subscription-plans')) ?>"><span><i data-lucide="layers-3"></i> Active plans</span><strong><?= (int) $business['activePlans'] ?></strong></a>
        <a href="<?= htmlspecialchars(url('/admin/customers')) ?>"><span><i data-lucide="calendar-clock"></i> Expiring in 30 days</span><strong><?= (int) $business['expiringSoon'] ?></strong></a>
        <a href="<?= htmlspecialchars(url('/admin/applications')) ?>"><span><i data-lucide="circle-dot-dashed"></i> Requests in review</span><strong><?= (int) $business['pendingRequests'] ?></strong></a>
        <a href="<?= htmlspecialchars(url('/admin/customers')) ?>"><span><i data-lucide="triangle-alert"></i> Overdue invoices</span><strong class="<?= (int) $business['overdueInvoices'] > 0 ? 'danger' : '' ?>"><?= (int) $business['overdueInvoices'] ?></strong></a>
    </div>
    <div class="revenue-comparison">
        <span>vs. previous month</span>
        <strong class="<?= $revenueChange !== null && $revenueChange < 0 ? 'negative' : 'positive' ?>">
            <?= $revenueChange === null ? 'No prior data' : (($revenueChange >= 0 ? '+' : '') . number_format($revenueChange, 1) . '%') ?>
        </strong>
    </div>
</div>
