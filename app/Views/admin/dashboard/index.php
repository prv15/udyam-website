<div class="dashboard-grid advanced-dashboard">
    <section class="dashboard-welcome">
        <div>
            <span class="dashboard-kicker">Executive command centre</span>
            <h2>Good <?= date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') ?>, <?= htmlspecialchars($user['first_name']) ?>.</h2>
            <p>Website, customer operations and revenue intelligence in one live workspace.</p>
        </div>
        <div class="dashboard-welcome-actions">
            <a href="<?= htmlspecialchars(url('/admin/applications')) ?>" class="dashboard-ghost-action"><i data-lucide="clipboard-check"></i> Review applications</a>
            <a href="<?= htmlspecialchars(url('/admin/subscription-plans/create')) ?>" class="dashboard-primary-action"><i data-lucide="plus"></i> Create plan</a>
        </div>
    </section>

    <?php require __DIR__ . '/widgets/erp-kpis.php'; ?>

    <div class="dashboard-split dashboard-split-main">
        <?php require __DIR__ . '/widgets/analytics.php'; ?>
        <?php require __DIR__ . '/widgets/commercial-health.php'; ?>
    </div>

    <div class="dashboard-split dashboard-split-equal">
        <?php require __DIR__ . '/widgets/subscriptions.php'; ?>
        <?php require __DIR__ . '/widgets/application-pipeline.php'; ?>
        <?php require __DIR__ . '/widgets/quick-actions.php'; ?>
    </div>

    <div class="dashboard-split dashboard-split-main">
        <?php require __DIR__ . '/widgets/recent-activity.php'; ?>
        <?php require __DIR__ . '/widgets/latest-customers.php'; ?>
    </div>

    <div class="dashboard-bottom-grid">
        <?php require __DIR__ . '/widgets/system-status.php'; ?>
    </div>
</div>
