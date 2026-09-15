<?php
$groups = ['Today' => [], 'Yesterday' => [], 'Earlier' => []];
foreach ($notifications as $notification) {
    $date = new DateTimeImmutable((string) $notification['created_at']);
    $key = $date->format('Y-m-d') === date('Y-m-d') ? 'Today' : ($date->format('Y-m-d') === date('Y-m-d', strtotime('-1 day')) ? 'Yesterday' : 'Earlier');
    $groups[$key][] = $notification;
}
?>
<div class="page-header notification-center-heading"><div><span class="page-eyebrow">Operations inbox</span><h2>Notification Center</h2><p>Real-time alerts from partner, subscription, billing and service workflows.</p></div><button class="btn btn-primary" type="button" data-notification-read-all><i data-lucide="check-check"></i> Mark all read</button></div>
<section class="notification-center" data-notification-center data-read-all-url="<?= htmlspecialchars(url('/admin/notification-center/read-all')) ?>">
<?php if ($notifications === []): ?><div class="workspace-empty"><i data-lucide="bell-off"></i>Your operations inbox is clear.</div><?php endif; ?>
<?php foreach ($groups as $label => $items): if ($items === []) continue; ?><div class="notification-day"><h3><?= htmlspecialchars($label) ?></h3><?php foreach ($items as $item): $name=trim((string)($item['company_name'] ?: (($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')))); ?><article class="notification-center-item<?= empty($item['read_at']) ? ' unread' : '' ?>" data-notification-id="<?= (int) $item['id'] ?>"><span class="notification-type-icon"><i data-lucide="bell-ring"></i></span><div><strong><?= htmlspecialchars((string) $item['title']) ?></strong><p><?= htmlspecialchars((string) $item['message']) ?></p><small><?= $name !== '' ? htmlspecialchars($name) . ' · ' : '' ?><?= htmlspecialchars(date('d M Y, h:i A', strtotime((string) $item['created_at']))) ?></small></div><div class="notification-row-actions"><?php if (!empty($item['action_url'])): ?><a href="<?= htmlspecialchars(url((string) $item['action_url'])) ?>" data-notification-open>Open <i data-lucide="arrow-up-right"></i></a><?php endif; ?><button type="button" data-notification-delete aria-label="Delete notification"><i data-lucide="trash-2"></i></button></div></article><?php endforeach; ?></div><?php endforeach; ?>
</section>
