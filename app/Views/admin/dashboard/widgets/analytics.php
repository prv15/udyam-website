<div class="panel analytics-panel">
    <div class="panel-header">
        <div>
            <span class="widget-label">CONTENT OVERVIEW</span>
            <h3>Records by Module</h3>
            <p>Current operational content</p>
        </div>
    </div>
    <div class="chart-container"><canvas id="trafficChart"></canvas></div>
</div>
<script>
window.dashboardDistribution = <?= json_encode($distribution, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
