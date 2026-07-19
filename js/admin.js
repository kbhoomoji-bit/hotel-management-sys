/**
 * Hotel Management System - Admin Dashboard Analytics Scripts
 */

function initAdminDashboardCharts(revenueData, bookingData) {
    // 1. Revenue Chart (Line Chart)
    const revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: revenueData.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenueData.values || [1200, 1900, 3000, 5000, 4200, 6800, 8500],
                    borderColor: '#C5A880',
                    backgroundColor: 'rgba(197, 168, 128, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0B1528',
                    pointBorderColor: '#C5A880',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Monthly Booking Graph (Bar Chart)
    const bookCtx = document.getElementById('bookingChart');
    if (bookCtx) {
        new Chart(bookCtx, {
            type: 'bar',
            data: {
                labels: bookingData.labels || ['Standard', 'Deluxe', 'Suite', 'Family', 'Luxury'],
                datasets: [{
                    label: 'Bookings',
                    data: bookingData.values || [15, 22, 10, 8, 5],
                    backgroundColor: [
                        '#0B1528',
                        '#C5A880',
                        '#182642',
                        '#10B981',
                        '#F59E0B'
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }
}
