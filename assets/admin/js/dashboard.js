const dashboardChartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: "#0d1c42",
            padding: 12,
            cornerRadius: 10,
            titleFont: { family: "Inter", size: 11, weight: "600" },
            bodyFont: { family: "Inter", size: 11 }
        }
    }
};

const revenueCanvas = document.getElementById("revenueChart");
if (revenueCanvas && typeof Chart !== "undefined") {
    const revenue = window.dashboardRevenue || { labels: [], values: [] };
    const context = revenueCanvas.getContext("2d");
    const fill = context.createLinearGradient(0, 0, 0, 300);
    fill.addColorStop(0, "rgba(75, 75, 225, .28)");
    fill.addColorStop(1, "rgba(75, 75, 225, .015)");

    new Chart(revenueCanvas, {
        type: "line",
        data: {
            labels: revenue.labels,
            datasets: [{
                data: revenue.values,
                borderColor: "#4b4be1",
                borderWidth: 2.5,
                backgroundColor: fill,
                pointBackgroundColor: "#fff",
                pointBorderColor: "#4b4be1",
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: .42,
                fill: true
            }]
        },
        options: {
            ...dashboardChartDefaults,
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: "#7d879b", font: { family: "Inter", size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: "#edf0f5" },
                    ticks: {
                        color: "#7d879b",
                        font: { family: "Inter", size: 10 },
                        callback: value => value >= 100000 ? `₹${(value / 100000).toFixed(1)}L` : `₹${(value / 1000).toFixed(0)}k`
                    }
                }
            },
            plugins: {
                ...dashboardChartDefaults.plugins,
                tooltip: {
                    ...dashboardChartDefaults.plugins.tooltip,
                    callbacks: { label: item => ` ₹${Number(item.raw).toLocaleString("en-IN")}` }
                }
            }
        }
    });
}

const pipelineCanvas = document.getElementById("pipelineChart");
if (pipelineCanvas && typeof Chart !== "undefined") {
    const pipeline = window.dashboardPipeline || { labels: [], values: [] };
    if (pipeline.values?.length) {
        new Chart(pipelineCanvas, {
            type: "doughnut",
            data: {
                labels: pipeline.labels,
                datasets: [{
                    data: pipeline.values,
                    backgroundColor: ["#3456d1", "#6d4ce8", "#00a6a6", "#e6a425", "#ec6a5e", "#7c8aa5", "#1b9a68"],
                    borderColor: "#fff",
                    borderWidth: 4,
                    hoverOffset: 6
                }]
            },
            options: {
                ...dashboardChartDefaults,
                cutout: "70%",
                plugins: {
                    ...dashboardChartDefaults.plugins,
                    legend: {
                        display: true,
                        position: "bottom",
                        labels: { usePointStyle: true, boxWidth: 7, padding: 14, color: "#667085", font: { family: "Inter", size: 9 } }
                    }
                }
            }
        });
    }
}
