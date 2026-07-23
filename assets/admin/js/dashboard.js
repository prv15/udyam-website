const ctx = document.getElementById('trafficChart');

if (ctx && typeof Chart !== 'undefined') {
    const distribution = window.dashboardDistribution || { labels: [], values: [] };

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: distribution.labels,
            datasets: [{
                data: distribution.values,
                borderColor: '#4F46E5',
                borderWidth: 1,
                backgroundColor: 'rgba(79,70,229,.16)',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#F2F4F7' },
                    border: { display: false }
                }
            }
        }
    });
}
