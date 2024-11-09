$(document).ready(function () {
  console.log("Dashboard initialization started");

  // Initialize dashboard
  loadDashboardStats();
  loadActivityLog();
  loadLatestEnquiries();

  // Refresh data periodically
  setInterval(loadDashboardStats, 60000); // Every minute
  setInterval(loadActivityLog, 60000); // Every minute
  setInterval(loadLatestEnquiries, 60000); // Every minute
});

function loadDashboardStats() {
  console.log("Loading dashboard stats...");
  $.ajax({
    url: "../php/admin/get_dashboard_stats.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Stats response:", response);
      if (response.status === "success") {
        $("#total-users").text(response.data.total_users || 0);
        $("#total-workouts").text(response.data.total_workouts || 0);
        $("#active-users").text(response.data.active_users || 0);
        $("#new-enquiries").text(response.data.new_enquiries || 0);

        if (response.data.new_enquiries > 0) {
          $("#new-enquiries").append(`
                        <span class="badge badge-danger ml-2">New</span>
                    `);
        }
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading stats:", error);
      console.error("Response:", xhr.responseText);
    },
  });
}

function loadActivityLog() {
  console.log("Loading activity log...");
  $.ajax({
    url: "../php/admin/get_activity_log.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Activity log response:", response);
      if (response.status === "success") {
        updateActivityLog(response.data);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading activity log:", error);
      console.error("Response:", xhr.responseText);
    },
  });
}

function loadLatestEnquiries() {
  console.log("Loading latest enquiries...");
  $.ajax({
    url: "../php/admin/get_latest_enquiries.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Enquiries response:", response);
      if (response.status === "success") {
        updateEnquiriesList(response.data);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading enquiries:", error);
      console.error("Response:", xhr.responseText);
    },
  });
}

function updateActivityLog(activities) {
  const activityLog = $("#activity-log");
  activityLog.empty();

  if (!activities || activities.length === 0) {
    activityLog.append(`
            <div class="text-center py-3">
                <p class="text-muted mb-0">No recent activities</p>
            </div>
        `);
    return;
  }

  activities.forEach((activity) => {
    activityLog.append(`
            <div class="activity-item d-flex align-items-start mb-3">
                <div class="activity-icon me-3">
                    <i class="fas ${getActivityIcon(
                      activity.action
                    )} fa-fw"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-description">
                        ${activity.details || activity.description}
                    </div>
                    <small class="text-muted">
                        ${formatDate(activity.created_at)}
                    </small>
                </div>
            </div>
        `);
  });
}

function updateEnquiriesList(enquiries) {
  console.log("Updating enquiries list with:", enquiries);
  const container = $("#latest-enquiries");
  container.empty();

  if (!enquiries || enquiries.length === 0) {
    container.append(`
            <div class="text-center py-3">
                <p class="text-muted mb-0">No new enquiries</p>
            </div>
        `);
    return;
  }

  enquiries.forEach((enquiry) => {
    container.append(`
            <div class="enquiry-item ${
              enquiry.status === "new" ? "new" : ""
            } mb-3">
                <div class="d-flex justify-content-between">
                    <strong>${enquiry.name}</strong>
                    <small class="text-muted">${formatDate(
                      enquiry.created_at
                    )}</small>
                </div>
                <div>${enquiry.email}</div>
                <div class="text-truncate">${
                  enquiry.message || "No message"
                }</div>
                <div class="mt-1">
                    <span class="badge badge-${
                      enquiry.status === "new" ? "danger" : "success"
                    }">
                        ${enquiry.status}
                    </span>
                </div>
            </div>
        `);
  });
}

function getActivityIcon(type) {
  const icons = {
    login: "fa-sign-in-alt",
    logout: "fa-sign-out-alt",
    create: "fa-plus-circle",
    update: "fa-edit",
    delete: "fa-trash",
    new_enquiry: "fa-envelope",
  };
  return icons[type] || "fa-circle";
}

function formatDate(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  const now = new Date();
  const diff = now - date;

  if (diff < 60000) return "just now";
  if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
  if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;
  if (diff < 604800000) return `${Math.floor(diff / 86400000)}d ago`;

  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function showAlert(message, type) {
  const alert = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;

  $(".admin-content").prepend(alert);
  setTimeout(() => {
    $(".alert").alert("close");
  }, 5000);
}
