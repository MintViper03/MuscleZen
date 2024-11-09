$(document).ready(function () {
  // Check admin session
  checkAdminSession();

  // Load dashboard stats
  loadDashboardStats();

  // Load activity log
  loadActivityLog();

  // Logout handler - Remove the nested $(document).ready
  $("#admin-logout").on("click", function (e) {
    e.preventDefault();
    console.log("Logout clicked"); // Debug log

    if (confirm("Are you sure you want to logout?")) {
      $.ajax({
        url: "../php/admin/logout.php",
        method: "POST",
        dataType: "json",
        success: function (response) {
          console.log("Logout response:", response); // Debug log
          if (response.status === "success") {
            window.location.href = "../index.html";
          } else {
            console.error("Logout failed:", response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("Logout error:", error);
          window.location.href = "../index.html";
        },
      });
    }
  });

  // Rest of your code...
});

function checkAdminSession() {
  $.ajax({
    url: "../php/admin/check_session.php",
    method: "GET",
    success: function (response) {
      if (!response.logged_in) {
        window.location.href = "../index.html"; // Changed from login.html to ../index.html
      } else {
        $("#admin-username").text(response.username);
        if (response.avatar) {
          $("#admin-avatar").attr("src", response.avatar);
        }
      }
    },
    error: function () {
      window.location.href = "../index.html"; // Changed from login.html to ../index.html
    },
  });
}

function loadDashboardStats() {
  $.ajax({
    url: "../php/admin/get_dashboard_stats.php",
    method: "GET",
    success: function (response) {
      if (response.status === "success") {
        $("#total-users").text(response.data.total_users);
        $("#total-workouts").text(response.data.total_workouts);
        $("#active-users").text(response.data.active_users);
        $("#reported-content").text(response.data.reported_content);

        // Update charts if they exist
        if (typeof updateUserChart === "function") {
          updateUserChart(response.data.user_stats);
        }
        if (typeof updateActivityChart === "function") {
          updateActivityChart(response.data.activity_stats);
        }
      }
    },
  });
}

function loadActivityLog() {
  $.ajax({
    url: "../php/admin/get_activity_log.php",
    method: "GET",
    success: function (response) {
      if (response.status === "success") {
        const activityLog = $("#activity-log");
        activityLog.empty();

        response.data.forEach((activity) => {
          activityLog.append(`
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas ${getActivityIcon(
                                  activity.type
                                )}"></i>
                            </div>
                            <div class="activity-details">
                                <p>${activity.description}</p>
                                <small>${formatDate(
                                  activity.created_at
                                )}</small>
                            </div>
                        </div>
                    `);
        });
      }
    },
  });
}

function getActivityIcon(type) {
  const icons = {
    user: "fa-user",
    workout: "fa-dumbbell",
    content: "fa-file-alt",
    security: "fa-shield-alt",
    settings: "fa-cog",
    login: "fa-sign-in-alt",
    logout: "fa-sign-out-alt",
    delete: "fa-trash-alt",
    update: "fa-edit",
    create: "fa-plus-circle",
  };
  return icons[type] || "fa-info-circle";
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diff = now - date;

  // Less than 24 hours
  if (diff < 86400000) {
    const hours = Math.floor(diff / 3600000);
    if (hours < 1) {
      const minutes = Math.floor(diff / 60000);
      return `${minutes} minute${minutes !== 1 ? "s" : ""} ago`;
    }
    return `${hours} hour${hours !== 1 ? "s" : ""} ago`;
  }

  // Less than 7 days
  if (diff < 604800000) {
    const days = Math.floor(diff / 86400000);
    return `${days} day${days !== 1 ? "s" : ""} ago`;
  }

  // Default format
  const options = {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  return date.toLocaleDateString(undefined, options);
}

// Chart initialization functions (if using charts)
function initializeCharts() {
  if (typeof Chart !== "undefined") {
    initializeUserChart();
    initializeActivityChart();
  }
}

function initializeUserChart() {
  const ctx = document.getElementById("userChart");
  if (ctx) {
    window.userChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: [],
        datasets: [
          {
            label: "New Users",
            data: [],
            borderColor: "#3498db",
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      },
    });
  }
}

function initializeActivityChart() {
  const ctx = document.getElementById("activityChart");
  if (ctx) {
    window.activityChart = new Chart(ctx, {
      type: "bar",
      data: {
        labels: [],
        datasets: [
          {
            label: "User Activity",
            data: [],
            backgroundColor: "#2ecc71",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      },
    });
  }
}

// Update chart data
function updateUserChart(data) {
  if (window.userChart) {
    window.userChart.data.labels = data.labels;
    window.userChart.data.datasets[0].data = data.values;
    window.userChart.update();
  }
}

function updateActivityChart(data) {
  if (window.activityChart) {
    window.activityChart.data.labels = data.labels;
    window.activityChart.data.datasets[0].data = data.values;
    window.activityChart.update();
  }
}

// Initialize everything when document is ready
$(document).ready(function () {
  initializeCharts();
  // Set up auto-refresh for dashboard data
  setInterval(loadDashboardStats, 300000); // Refresh every 5 minutes
  setInterval(loadActivityLog, 60000); // Refresh every minute
});
