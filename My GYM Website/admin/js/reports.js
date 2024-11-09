// Global chart variables
let userGrowthChart = null;
let workoutEngagementChart = null;

$(document).ready(function () {
  // Initialize charts only once
  initializeCharts();
  loadReportData();

  // Date range handler
  $("#date-range").change(function () {
    if ($(this).val() === "custom") {
      $("#custom-range").addClass("show");
    } else {
      $("#custom-range").removeClass("show");
      loadReportData();
    }
  });

  // Apply custom date range
  $("#apply-range").click(function () {
    loadReportData();
  });

  // Export handlers
  $("#export-pdf").click(function () {
    exportReport("pdf");
  });

  $("#export-excel").click(function () {
    exportReport("excel");
  });
});

function initializeCharts() {
  // Destroy existing charts if they exist
  if (userGrowthChart) {
    userGrowthChart.destroy();
  }
  if (workoutEngagementChart) {
    workoutEngagementChart.destroy();
  }

  // User Growth Chart
  const userCtx = document.getElementById("userGrowthChart").getContext("2d");
  userGrowthChart = new Chart(userCtx, {
    type: "line",
    data: {
      labels: [],
      datasets: [
        {
          label: "New Users",
          data: [],
          borderColor: "#3498db",
          tension: 0.4,
          fill: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "top",
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
        },
      },
    },
  });

  // Workout Engagement Chart
  const workoutCtx = document
    .getElementById("workoutEngagementChart")
    .getContext("2d");
  workoutEngagementChart = new Chart(workoutCtx, {
    type: "bar",
    data: {
      labels: [],
      datasets: [
        {
          label: "Workout Completions",
          data: [],
          backgroundColor: "#2ecc71",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "top",
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
        },
      },
    },
  });
}

function loadReportData() {
  const dateRange = $("#date-range").val();
  let data = {
    range: dateRange,
  };

  if (dateRange === "custom") {
    data.start_date = $("#start-date").val();
    data.end_date = $("#end-date").val();
  }

  $.ajax({
    url: "../php/admin/get_report_data.php",
    method: "GET",
    data: data,
    success: function (response) {
      if (response.status === "success") {
        updateCharts(response.data);
        updateStatisticsTable(response.data.statistics);
      } else {
        showAlert(response.message || "Error loading report data", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading report data:", error);
      showAlert("Error loading report data", "danger");
    },
  });
}

function updateCharts(data) {
  // Update User Growth Chart
  if (data.user_growth && userGrowthChart) {
    userGrowthChart.data.labels = data.user_growth.labels;
    userGrowthChart.data.datasets[0].data = data.user_growth.values;
    userGrowthChart.update("none"); // Use 'none' for smoother updates
  }

  // Update Workout Engagement Chart
  if (data.workout_engagement && workoutEngagementChart) {
    workoutEngagementChart.data.labels = data.workout_engagement.labels;
    workoutEngagementChart.data.datasets[0].data =
      data.workout_engagement.values;
    workoutEngagementChart.update("none"); // Use 'none' for smoother updates
  }
}

function updateStatisticsTable(statistics) {
  const tbody = $("#stats-table-body");
  tbody.empty();

  if (!statistics || statistics.length === 0) {
    tbody.append(`
            <tr>
                <td colspan="4" class="text-center">No statistics available</td>
            </tr>
        `);
    return;
  }

  statistics.forEach((stat) => {
    const percentChange = calculatePercentChange(stat.current, stat.previous);
    const changeClass = percentChange >= 0 ? "text-success" : "text-danger";
    const changeIcon = percentChange >= 0 ? "fa-arrow-up" : "fa-arrow-down";

    tbody.append(`
            <tr>
                <td>${stat.metric}</td>
                <td>${stat.current}</td>
                <td>${stat.previous}</td>
                <td class="${changeClass}">
                    <i class="fas ${changeIcon}"></i>
                    ${Math.abs(percentChange)}%
                </td>
            </tr>
        `);
  });
}

function calculatePercentChange(current, previous) {
  if (previous === 0) return 0;
  return Math.round(((current - previous) / previous) * 100);
}

function exportReport(type) {
  const dateRange = $("#date-range").val();
  let data = {
    type: type,
    range: dateRange,
  };

  if (dateRange === "custom") {
    data.start_date = $("#start-date").val();
    data.end_date = $("#end-date").val();
  }

  $.ajax({
    url: "../php/admin/export_report.php",
    method: "POST",
    data: data,
    xhrFields: {
      responseType: "blob",
    },
    success: function (response) {
      const blob = new Blob([response], {
        type: type === "pdf" ? "application/pdf" : "application/vnd.ms-excel",
      });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = `report.${type}`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
    },
    error: function (xhr, status, error) {
      console.error("Error exporting report:", error);
      showAlert("Error exporting report", "danger");
    },
  });
}

function showAlert(message, type) {
  const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;

  $(".admin-content").prepend(alertHtml);
  setTimeout(() => {
    $(".alert").alert("close");
  }, 5000);
}
