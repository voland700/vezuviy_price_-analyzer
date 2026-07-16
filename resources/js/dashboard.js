import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const brands = document.getElementById('brandsChart');
    if (brands) {
        const data = JSON.parse(brands.dataset.json || '{}');
        new Chart(brands, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: [
                        '#3b82f6', '#ef4444', '#10b981', '#f59e0b',
                        '#8b5cf6', '#ec4899', '#14b8a6', '#f97316',
                        '#6366f1', '#84cc16',
                    ],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151' } } },
            },
        });
    }

    const bar = document.getElementById('summaryChart');
    if (bar) {
        const data = JSON.parse(bar.dataset.json || '{}');
        new Chart(bar, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Количество',
                    data: data.values,
                    backgroundColor: data.colors,
                    borderWidth: 0,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb' }, ticks: { color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151' } },
                    y: { grid: { color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb' }, ticks: { color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151' } },
                },
            },
        });
    }
});
