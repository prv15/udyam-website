<div class="panel subscription-panel">
    <div class="widget-header">
        <div class="widget-title-group"><span class="widget-label">SUBSCRIPTIONS</span><h3>Plan performance</h3><p>Active customer distribution</p></div>
        <a class="text-link" href="<?= htmlspecialchars(url('/admin/subscription-plans')) ?>">Manage</a>
    </div>
    <?php
    $maxSubscriptions = max(1, ...array_map(static fn (array $plan): int => (int) $plan['subscriptions'], $subscriptionMix ?: [['subscriptions' => 1]]));
    ?>
    <div class="plan-performance">
        <?php if ($subscriptionMix === []): ?>
            <div class="dashboard-empty"><i data-lucide="badge-indian-rupee"></i><span>No subscription activity yet</span></div>
        <?php else: foreach ($subscriptionMix as $plan): ?>
            <div class="plan-performance-row">
                <div><strong><?= htmlspecialchars((string) $plan['name']) ?></strong><small><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $plan['billing_cycle']))) ?></small></div>
                <div class="plan-bar"><span style="width:<?= ((int) $plan['subscriptions'] / $maxSubscriptions) * 100 ?>%"></span></div>
                <b><?= (int) $plan['subscriptions'] ?></b>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
