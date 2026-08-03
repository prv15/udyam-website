<div class="panel analytics-panel">
    <div class="panel-header">
        <div>
            <span class="widget-label">REVENUE INTELLIGENCE</span>
            <h3>Payment performance</h3>
            <p>Successful customer payments over the last six months</p>
        </div>
        <div class="analytics-highlight"><small>This month</small><strong>₹<?= number_format((float) $stats['monthRevenue'], 0) ?></strong></div>
    </div>
    <div class="chart-container"><canvas id="revenueChart"></canvas></div>
</div>
<script>
window.dashboardDistribution = <?= json_encode($distribution, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
window.dashboardRevenue = <?= json_encode($revenueTrend, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
window.dashboardPipeline = <?= json_encode($applicationPipeline, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
