document.addEventListener('DOMContentLoaded', function() {
    // Load configuration from hidden element
    const configElement = document.getElementById('js-config');
    if (!configElement) return;
    
    try {
        const ranking = JSON.parse(configElement.dataset.ranking || '{}');
        const flows = JSON.parse(configElement.dataset.flows || '{}');
        
        // Initialize charts with the data
        initializeCharts(ranking, flows);
    } catch (e) {
        console.error('Error parsing decision data:', e);
    }
});

function initializeCharts(ranking, flows) {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded');
        return;
    }

    // Process ranking data
    const rankingData = Object.entries(ranking).map(([id, data]) => ({
        id: id,
        name: data.name || 'Unknown',
        net_flow: parseFloat(data.net_flow) || 0
    }));

    // Set Chart.js defaults
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6B7280';
    
    // Initialize Net Flow Chart
    initNetFlowChart(rankingData);
    
    // Initialize Flow Comparison Chart
    initFlowComparisonChart(rankingData, flows);
    
    // Add resize handler
    window.addEventListener('resize', function() {
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    });
}

function initNetFlowChart(rankingData) {
    const ctx = document.getElementById('netFlowChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: rankingData.map(item => item.name),
            datasets: [{
                label: 'Net Flow',
                data: rankingData.map(item => item.net_flow),
                backgroundColor: rankingData.map((item, index) => {
                    if (index === 0) return 'rgba(245, 158, 11, 0.8)';
                    return item.net_flow >= 0 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)';
                }),
                borderColor: rankingData.map((item, index) => {
                    if (index === 0) return 'rgba(245, 158, 11, 1)';
                    return item.net_flow >= 0 ? 'rgba(16, 185, 129, 1)' : 'rgba(239, 68, 68, 1)';
                }),
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: getNetFlowChartOptions()
    });
}

function getNetFlowChartOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                titleColor: 'white',
                bodyColor: 'white',
                cornerRadius: 8,
                callbacks: {
                    title: function(context) {
                        return `Rank ${context[0].dataIndex + 1}: ${context[0].label}`;
                    },
                    label: function(context) {
                        return `Net Flow: ${context.parsed.y.toFixed(4)}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                grid: { color: 'rgba(156, 163, 175, 0.1)' },
                ticks: {
                    color: 'rgba(107, 114, 128, 0.8)',
                    callback: function(value) { return value.toFixed(3); }
                },
                title: {
                    display: true,
                    text: 'Net Flow Value (Φ)',
                    color: 'rgba(75, 85, 99, 0.9)',
                    font: { size: 12, weight: '600' }
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    color: 'rgba(107, 114, 128, 0.8)',
                    maxRotation: 45
                },
                title: {
                    display: true,
                    text: 'Alternatives',
                    color: 'rgba(75, 85, 99, 0.9)',
                    font: { size: 12, weight: '600' }
                }
            }
        }
    };
}

function initFlowComparisonChart(rankingData, flows) {
    const ctx = document.getElementById('flowComparisonChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: rankingData.map(item => item.name),
            datasets: [
                {
                    label: 'Positive Flow (Φ+)',
                    data: rankingData.map(item => {
                        const flow = flows[item.id] || {};
                        return parseFloat(flow.positive) || 0;
                    }),
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    borderRadius: 8
                },
                {
                    label: 'Negative Flow (Φ-)',
                    data: rankingData.map(item => {
                        const flow = flows[item.id] || {};
                        return parseFloat(flow.negative) || 0;
                    }),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 2,
                    borderRadius: 8
                }
            ]
        },
        options: getFlowComparisonChartOptions()
    });
}

function getFlowComparisonChartOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    color: 'rgba(107, 114, 128, 0.9)',
                    font: { size: 12, weight: '600' }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                titleColor: 'white',
                bodyColor: 'white',
                cornerRadius: 8,
                callbacks: {
                    title: function(context) {
                        return `Rank ${context[0].dataIndex + 1}: ${context[0].label}`;
                    },
                    label: function(context) {
                        return `${context.dataset.label}: ${context.parsed.y.toFixed(4)}`;
                    },
                    afterLabel: function(context) {
                        if (context.datasetIndex === 0) {
                            const altIndex = context.dataIndex;
                            const positive = context.parsed.y;
                            const negative = context.chart.data.datasets[1].data[altIndex];
                            const netFlow = positive - negative;
                            return `Net Flow: ${netFlow.toFixed(4)}`;
                        }
                        return '';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                grid: { color: 'rgba(156, 163, 175, 0.1)' },
                ticks: {
                    color: 'rgba(107, 114, 128, 0.8)',
                    callback: function(value) { return value.toFixed(3); }
                },
                title: {
                    display: true,
                    text: 'Flow Value',
                    color: 'rgba(75, 85, 99, 0.9)',
                    font: { size: 12, weight: '600' }
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    color: 'rgba(107, 114, 128, 0.8)',
                    maxRotation: 45
                },
                title: {
                    display: true,
                    text: 'Alternatives',
                    color: 'rgba(75, 85, 99, 0.9)',
                    font: { size: 12, weight: '600' }
                }
            }
        }
    };
}