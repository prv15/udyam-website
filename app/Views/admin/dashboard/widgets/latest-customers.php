<div class="panel latest-customers-panel">
    <div class="widget-header">
        <div class="widget-title-group"><span class="widget-label">CUSTOMERS</span><h3>Latest customers</h3><p>Most recently created portal accounts</p></div>
        <a class="text-link" href="<?= htmlspecialchars(url('/admin/customers')) ?>">View all</a>
    </div>
    <div class="customer-mini-list">
        <?php if ($latestCustomers === []): ?>
            <div class="dashboard-empty"><i data-lucide="users-round"></i><span>No portal customers yet</span></div>
        <?php else: foreach ($latestCustomers as $customer):
            $name = trim((string) $customer['first_name'] . ' ' . (string) $customer['last_name']);
        ?>
            <a href="<?= htmlspecialchars(!empty($customer['customer_record_id'])
                ? url('/admin/customers/workspace/' . (int) $customer['customer_record_id'])
                : url('/admin/customers')) ?>">
                <span class="customer-mini-avatar"><?= htmlspecialchars(strtoupper(mb_substr($name, 0, 1))) ?></span>
                <span><strong><?= htmlspecialchars((string) ($customer['company_name'] ?: $name)) ?></strong><small><?= htmlspecialchars((string) $customer['email']) ?></small></span>
                <span class="customer-mini-plan"><?= (int) $customer['active_subscriptions'] ?> plan<?= (int) $customer['active_subscriptions'] === 1 ? '' : 's' ?></span>
            </a>
        <?php endforeach; endif; ?>
    </div>
</div>
