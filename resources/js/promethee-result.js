import Chart from 'chart.js/auto';

export function initializePrometheeCharts(rankingData, flowsData) {
    // Net Flow Chart
    const netFlowCtx = document.getElementById('netFlowChart');
    if (netFlowCtx) {
        new Chart(netFlowCtx, {
            type: 'bar',
            data: {
                labels: rankingData.map(item => item.name),
                datasets: [{
                    label: 'Net Flow',
                    data: rankingData.map(item => item.net_flow),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    }

    // Flow Comparison Chart
    const flowComparisonCtx = document.getElementById('flowComparisonChart');
    if (flowComparisonCtx) {
        new Chart(flowComparisonCtx, {
            type: 'bar',
            data: {
                labels: rankingData.map(item => item.name),
                datasets: [
                    {
                        label: 'Positive Flow',
                        data: rankingData.map(item => flowsData[item.id].positive),
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Negative Flow',
                        data: rankingData.map(item => flowsData[item.id].negative),
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    }
}

// Export untuk penggunaan langsung di browser
if (typeof window !== 'undefined') {
    window.initializePrometheeCharts = initializePrometheeCharts;
}