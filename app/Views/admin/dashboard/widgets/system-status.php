<div class="panel">
    <div class="panel-header"><h3>System Status</h3></div>
    <div class="status-list">
        <div class="status-item"><span>Website</span><span class="status-badge success">Online</span></div>
        <div class="status-item"><span>Database</span><span class="status-badge success"><?= $systemStatus['database'] ? 'Connected' : 'Unavailable' ?></span></div>
        <div class="status-item"><span>PHP Version</span><span class="status-badge info"><?= htmlspecialchars($systemStatus['php']) ?></span></div>
        <div class="status-item"><span>Disk Usage</span><span class="status-badge warning"><?= $systemStatus['disk'] === null ? 'Unknown' : (int) $systemStatus['disk'] . '%' ?></span></div>
        <div class="status-item"><span>HTTPS</span><span class="status-badge <?= $systemStatus['https'] ? 'success' : 'warning' ?>"><?= $systemStatus['https'] ? 'Active' : 'Local / HTTP' ?></span></div>
        <div class="status-item"><span>Environment</span><span class="status-badge info"><?= htmlspecialchars(ucfirst($systemStatus['environment'])) ?></span></div>
    </div>
</div>
