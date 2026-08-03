<div class="panel pipeline-panel">
    <div class="widget-header">
        <div class="widget-title-group"><span class="widget-label">APPLICATIONS</span><h3>Service pipeline</h3><p>Status distribution</p></div>
        <a class="text-link" href="<?= htmlspecialchars(url('/admin/applications')) ?>">View all</a>
    </div>
    <div class="pipeline-chart"><canvas id="pipelineChart"></canvas></div>
    <?php if (($applicationPipeline['labels'] ?? []) === []): ?>
        <div class="dashboard-empty compact"><span>No applications yet</span></div>
    <?php endif; ?>
</div>
