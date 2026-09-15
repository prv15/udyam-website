<?php
$profile = $data['profile'] ?? [];
$profileFields = ['company_name', 'mobile', 'contact_person', 'city', 'state', 'address_line_1'];
$profileComplete = !empty($profile['email_verified_at']);
foreach ($profileFields as $field) {
    $profileComplete = $profileComplete && !empty($profile[$field]);
}
$hasSubscription = !empty($data['subscription']);
$tenders = $data['tenders'] ?? [];
$pipeline = $data['applicationStatus'] ?? [];
$pipelineLabels = [];
$pipelineValues = [];
foreach ($pipeline as $stage) {
    $pipelineLabels[] = ucwords(str_replace('_', ' ', (string) $stage['status']));
    $pipelineValues[] = (int) $stage['total'];
}
?>

<section class="welcome-panel customer-welcome">
    <div><span>Welcome back</span><h2><?= htmlspecialchars($customer['first_name']) ?>, your Udyam workspace is ready.</h2><p>Track services, applications, documents, subscriptions and billing from one secure place.</p></div>
    <a href="<?= htmlspecialchars(url('/customer/services')) ?>" class="primary-action">Explore services <i data-lucide="arrow-right"></i></a>
</section>

<section class="portal-priority-stack" aria-label="Account actions">
    <?php if (!$profileComplete): ?>
        <article class="dash-alert dash-alert-profile"><span class="dash-alert-icon"><i data-lucide="user-round-cog"></i></span><div><strong>Complete your company profile</strong><p>Add your organization and contact details so we can personalise opportunities, process applications faster and keep invoices accurate.</p></div><a href="<?= htmlspecialchars(url('/customer/profile')) ?>" class="ghost-action">Complete profile <i data-lucide="arrow-right"></i></a></article>
    <?php endif; ?>
    <?php if (!$hasSubscription): ?>
        <article class="subscribe-cta"><span class="dash-alert-icon"><i data-lucide="sparkles"></i></span><div class="subscribe-cta-body"><span>Membership access</span><h3>Unlock notices, tenders and priority support.</h3><p>Choose a Udyam Project Funding membership to access your complete opportunity workspace.</p></div><a href="<?= htmlspecialchars(url('/customer/plans')) ?>" class="subscribe-cta-button">View plans <i data-lucide="arrow-right"></i></a></article>
    <?php endif; ?>
</section>

<section class="customer-highlights" aria-label="Account highlights">
    <div class="section-heading"><div><span>At a glance</span><h2>Your workspace highlights</h2><p>Key activity, service progress and billing details in one view.</p></div></div>
    <div class="metric-grid customer-metric-grid">
        <?php foreach ([
            ['sparkles', 'Active Subscription', $data['subscription']['plan_name'] ?? 'No active plan', $hasSubscription ? 'View membership' : 'Explore plans', $hasSubscription ? '/customer/plans' : '/customer/plans'],
            ['clipboard-clock', 'Pending Applications', (string) $data['pendingApplications'], 'Track applications', '/customer/applications'],
            ['badge-check', 'Approved Services', (string) $data['approvedServices'], 'View services', '/customer/services'],
            ['wallet-cards', 'Outstanding Payments', '₹' . number_format((float) $data['outstanding'], 2), 'Open billing', '/customer/billing'],
        ] as $metric): ?>
            <a class="metric" href="<?= htmlspecialchars(url($metric[4])) ?>"><i data-lucide="<?= $metric[0] ?>"></i><span><?= htmlspecialchars($metric[1]) ?></span><strong><?= htmlspecialchars($metric[2]) ?></strong><small><?= htmlspecialchars($metric[3]) ?> <i data-lucide="arrow-up-right"></i></small></a>
        <?php endforeach; ?>
    </div>
</section>

<div class="portal-grid two customer-chart-grid">
    <section class="portal-card chart-card"><div class="card-heading"><div><span>Billing intelligence</span><h3>Payment trend</h3></div><a href="<?= htmlspecialchars(url('/customer/billing/payments')) ?>">Payment history</a></div><div class="customer-chart-wrap"><canvas id="customerBillingChart"></canvas></div></section>
    <section class="portal-card chart-card"><div class="card-heading"><div><span>Service progress</span><h3>Application pipeline</h3></div><a href="<?= htmlspecialchars(url('/customer/applications')) ?>">View applications</a></div><div class="customer-chart-wrap"><canvas id="customerApplicationChart"></canvas><?php if ($pipelineLabels === []): ?><div class="chart-empty"><i data-lucide="clipboard-list"></i><span>Your application progress will appear here.</span></div><?php endif; ?></div></section>
</div>

<section class="portal-card tenders-card" id="tenders"><div class="card-heading"><div><span>Government Updates</span><h3>Notices &amp; Tenders</h3></div><a href="<?= htmlspecialchars(url($hasSubscription ? '/customer/tenders' : '/customer/plans')) ?>"><?= $hasSubscription ? 'View all' : 'Unlock access' ?></a></div>
<?php if (!$tenders): ?><div class="empty">No active notices or tenders right now.</div><?php else: ?><div class="tender-mini-table<?= $hasSubscription ? '' : ' is-locked' ?>"><?php foreach ($tenders as $item):
    $title = (string) ($item['title'] ?? 'Untitled');
    $lead = mb_substr($title, 0, 10); $rest = mb_substr($title, 10); $closing = $item['closing_date'] ?? null;
?><div class="tender-mini-row"><div class="tender-mini-title"><strong><?= htmlspecialchars($lead) ?><?php if ($rest !== ''): ?><span class="<?= $hasSubscription ? '' : 'tender-blur' ?>"><?= htmlspecialchars($rest) ?></span><?php endif; ?></strong><small><?= htmlspecialchars(ucfirst((string) ($item['type'] ?? 'Tender'))) ?></small></div><div class="tender-mini-meta <?= $hasSubscription ? '' : 'tender-blur' ?>"><?= htmlspecialchars($item['department'] ?? '—') ?></div><div class="tender-mini-date <?= $hasSubscription ? '' : 'tender-blur' ?>"><?= $closing ? htmlspecialchars(date('d M Y', strtotime($closing))) : '—' ?></div><div><?php if ($hasSubscription): ?><a class="tender-detail" href="<?= htmlspecialchars(url('/customer/tenders/' . (int) $item['id'])) ?>">View <span>→</span></a><?php else: ?><a class="tender-unlock" href="<?= htmlspecialchars(url('/customer/plans')) ?>">Unlock <span>↗</span></a><?php endif; ?></div></div><?php endforeach; ?></div><?php endif; ?></section>

<div class="portal-grid two"><section class="portal-card"><div class="card-heading"><div><span>Billing</span><h3>Recent invoices</h3></div><a href="<?= htmlspecialchars(url('/customer/billing/invoices')) ?>">View all</a></div><?php if (!$data['invoices']): ?><div class="empty">No invoices yet.</div><?php else: ?><div class="portal-list"><?php foreach ($data['invoices'] as $row): ?><a href="<?= htmlspecialchars(url('/customer/billing/invoices/' . $row['id'])) ?>"><span><strong><?= htmlspecialchars($row['invoice_number']) ?></strong><small><?= htmlspecialchars($row['issue_date']) ?></small></span><b>₹<?= number_format((float) $row['total_amount'], 2) ?></b></a><?php endforeach; ?></div><?php endif; ?></section>
<section class="portal-card"><div class="card-heading"><div><span>Updates<?= (int) ($data['unreadNotifications'] ?? 0) > 0 ? ' · ' . (int) $data['unreadNotifications'] . ' new' : '' ?></span><h3>Notifications</h3></div><a href="<?= htmlspecialchars(url('/customer/notifications')) ?>">View all</a></div><?php if (!$data['notifications']): ?><div class="empty">You are all caught up.</div><?php else: ?><div class="portal-list"><?php foreach ($data['notifications'] as $row): ?><div><i data-lucide="bell-ring"></i><span><strong><?= htmlspecialchars($row['title']) ?></strong><small><?= htmlspecialchars($row['message']) ?></small></span></div><?php endforeach; ?></div><?php endif; ?></section></div>

<section class="portal-card"><div class="card-heading"><div><span>Audit trail</span><h3>Recent activity</h3></div><a href="<?= htmlspecialchars(url('/customer/activity')) ?>">Full history</a></div><div class="timeline"><?php foreach ($data['activity'] as $row): ?><div><i></i><span><strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $row['action']))) ?></strong><small><?= htmlspecialchars($row['description'] ?? '') ?> · <?= htmlspecialchars($row['created_at']) ?></small></span></div><?php endforeach; ?></div></section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;
    const billing = <?= json_encode($data['billingTrend'] ?? ['labels' => [], 'values' => []], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const application = <?= json_encode(['labels' => $pipelineLabels, 'values' => $pipelineValues], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const line = document.getElementById('customerBillingChart');
    if (line) new Chart(line, {type:'line',data:{labels:billing.labels,datasets:[{data:billing.values,borderColor:'#3159d6',backgroundColor:'rgba(49,89,214,.12)',fill:true,tension:.42,borderWidth:2.5,pointRadius:3,pointHoverRadius:5,pointBackgroundColor:'#fff',pointBorderColor:'#3159d6',pointBorderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{backgroundColor:'#102350',padding:11,cornerRadius:9,callbacks:{label:function(context){return ' ₹' + Number(context.raw).toLocaleString('en-IN');}}}},scales:{x:{grid:{display:false},border:{display:false},ticks:{color:'#8490a5',font:{family:'Inter',size:10}}},y:{beginAtZero:true,grid:{color:'#edf0f6'},border:{display:false},ticks:{color:'#8490a5',font:{family:'Inter',size:10},callback:function(value){return value >= 1000 ? '₹' + (value/1000) + 'k' : '₹' + value;}}}}}});
    const doughnut = document.getElementById('customerApplicationChart');
    if (doughnut && application.values.length) new Chart(doughnut, {type:'doughnut',data:{labels:application.labels,datasets:[{data:application.values,backgroundColor:['#3159d6','#6c4be6','#0c9596','#e4a426','#e15f59','#8190a9'],borderColor:'#fff',borderWidth:4,hoverOffset:5}]},options:{responsive:true,maintainAspectRatio:false,cutout:'69%',plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:7,padding:13,color:'#65718a',font:{family:'Inter',size:9}}}}}});
});
</script>
