$(document).ready(function() {
    // Initialize charts
    initializeCharts();
    
    // Load initial data
    loadReportData();

    // Date range handler
    $('#date-range').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom-range').show();
        } else {
            $('#custom-range').hide();
            loadReportData($(this).val());
        }
    });

    // Custom date range handler
    $('#apply-range').on('click', function() {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
        
        if (startDate && endDate) {
            loadReportData('custom', startDate, endDate);
        } else {
            showAlert('Please select both start and end dates', 'warning');
        }
    });

    // Export handlers
    $('#export-pdf').on('click', function() {
        exportReport('pdf');
    });

    $('#export-excel').on('click', function() {
        exportReport('excel');
    });
});

function initializeCharts() {
    // User Growth Chart
    const userCtx = document.getElementById('userGrowthChart').getContext('2d');
    window.userGrowthChart = new Chart(userCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'New Users',
                data: [],
                borderColor: '#3498db',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Workout Engagement Chart
    const workoutCtx = document.getElementById('workoutEngagementChart').getContext('2d');
    window.workoutEngagementChart = new Chart(workoutCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Workouts Completed',
                data: [],
                backgroundColor: '#2ecc71'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function loadReportData(range = '7', startDate = null, endDate = null) {
    $.ajax({
        url: '../php/admin/get_report_data.php',
        method: 'GET',
        data: {
            range: range,
            start_date: startDate,
            end_date: endDate
        },
        success: function(response) {
            if (response.status === 'success') {
                updateCharts(response.data);
                updateStatistics(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading report data:', error);
            showAlert('Error loading report data', 'danger');
        }
    });
}

function updateCharts(data) {
    // Update User Growth Chart
    window.userGrowthChart.data.labels = data.user_growth.labels;
    window.userGrowthChart.data.datasets[0].data = data.user_growth.values;
    window.userGrowthChart.update();

    // Update Workout Engagement Chart
    window.workoutEngagementChart.data.labels = data.workout_engagement.labels;
    window.workoutEngagementChart.data.datasets[0].data = data.workout_engagement.values;
    window.workoutEngagementChart.update();
}

function updateStatistics(data) {
    const statsTable = $('#stats-table-body');
    statsTable.empty();

    data.statistics.forEach(stat => {
        const changeClass = stat.change >= 0 ? 'text-success' : 'text-danger';
        const changeIcon = stat.change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
        
        statsTable.append(`
            <tr>
                <td>${stat.metric}</td>
                <td>${stat.current}</td>
                <td>${stat.previous}</td>
                <td class="${changeClass}">
                    <i class="fas ${changeIcon}"></i>
                    ${Math.abs(stat.change)}%
                </td>
            </tr>
        `);
    });
}

function exportReport(type) {
    const range = $('#date-range').val();
    const startDate = $('#start-date').val();
    const endDate = $('#end-date').val();

    $.ajax({
        url: '../php/admin/export_report.php',
        method: 'POST',
        data: {
            type: type,
            range: range,
            start_date: startDate,
            end_date: endDate
        },
        xhrFields: {
            responseType: 'blob'
        },
        success: function(response) {
            const url = window.URL.createObjectURL(response);
            const a = document.createElement('a');
            a.href = url;
            a.download = `report.${type}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        },
        error: function(xhr, status, error) {
            console.error('Error exporting report:', error);
            showAlert('Error exporting report', 'danger');
        }
    });
}

function showAlert(message, type) {
    const alert = $(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('.admin-content').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
}
