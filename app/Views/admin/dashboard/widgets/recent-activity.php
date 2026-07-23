<div class="panel">
    <div class="widget-header">
        <div class="widget-title-group">
            <h3>Recent Activity</h3>
            <p>Latest updates across the platform</p>
        </div>
    </div>
    <div class="activity-list">
        <?php if ($activities === []): ?>
            <p>No activity yet. Create your first page or module record.</p>
        <?php else: foreach ($activities as $activity): ?>
            <div class="activity-item">
                <div class="activity-avatar"><?= htmlspecialchars(strtoupper(substr((string) $activity['source'], 0, 2))) ?></div>
                <div class="activity-content">
                    <h5><?= htmlspecialchars((string) $activity['title']) ?></h5>
                    <p><?= htmlspecialchars(ucwords(str_replace('-', ' ', (string) $activity['source']))) ?>
                        · <?= htmlspecialchars(ucfirst((string) $activity['action_status'])) ?>
                        · <?= htmlspecialchars(date('d M Y, h:i A', strtotime((string) $activity['updated_at']))) ?></p>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
