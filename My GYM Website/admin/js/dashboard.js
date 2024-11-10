$(document).ready(function () {
  // Initial loads
  loadDashboardStats();
  loadActivityLog();
  loadLatestEnquiries();

  // Quick Action Button Handlers
  $("#add-new-user-btn").click(function () {
    window.location.href = "users.html?action=new";
  });

  $("#create-workout-btn").click(function () {
    window.location.href = "workouts.html?action=new";
  });

  $("#download-report-btn").click(function () {
    downloadReport();
  });

  $("#send-notification-btn").click(function () {
    showNotificationModal();
  });

  // Enquiries Handler
  $("#view-all-enquiries").click(function () {
    showAllEnquiriesModal();
  });

  // Handle mark as read for enquiries
  $(document).on("click", ".mark-as-read", function () {
    const enquiryId = $(this).data("id");
    markEnquiryAsRead(enquiryId);
  });

  // Refresh data periodically
  setInterval(loadDashboardStats, 60000); // Every minute
  setInterval(loadActivityLog, 60000); // Every minute
  setInterval(loadLatestEnquiries, 60000); // Every minute
});

// Dashboard Stats Functions
function loadDashboardStats() {
  $.ajax({
    url: "../php/admin/get_dashboard_stats.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        $("#total-users").text(response.data.total_users || 0);
        $("#total-workouts").text(response.data.total_workouts || 0);
        $("#active-users").text(response.data.active_users || 0);
        $("#new-enquiries").text(response.data.new_enquiries || 0);

        if (response.data.new_enquiries > 0) {
          $("#new-enquiries").append(
            '<span class="badge badge-danger ml-2">New</span>'
          );
        }
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading stats:", error);
    },
  });
}

// Activity Log Functions
function loadActivityLog() {
  $.ajax({
    url: "../php/admin/get_activity_log.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        updateActivityLog(response.data);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading activity log:", error);
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
          <i class="fas ${getActivityIcon(activity.action)} fa-fw"></i>
        </div>
        <div class="activity-content">
          <div class="activity-description">
            ${activity.details || activity.description}
          </div>
          <small class="text-muted">${formatDate(activity.created_at)}</small>
        </div>
      </div>
    `);
  });
}

// Enquiries Functions
function loadLatestEnquiries() {
  $.ajax({
    url: "../php/admin/get_latest_enquiries.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        updateEnquiriesList(response.data);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading enquiries:", error);
    },
  });
}

function updateEnquiriesList(enquiries) {
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
      <div class="enquiry-item ${enquiry.status === "new" ? "new" : ""} mb-3">
        <div class="d-flex justify-content-between">
          <strong>${enquiry.name}</strong>
          <small class="text-muted">${formatDate(enquiry.created_at)}</small>
        </div>
        <div>${enquiry.email}</div>
        <div class="text-truncate">${enquiry.message || "No message"}</div>
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

// Quick Actions Functions
function downloadReport() {
  $.ajax({
    url: "../php/admin/export_report.php",
    method: "GET",
    xhrFields: {
      responseType: "blob",
    },
    success: function (response) {
      const url = window.URL.createObjectURL(new Blob([response]));
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute(
        "download",
        `report-${new Date().toISOString().slice(0, 10)}.pdf`
      );
      document.body.appendChild(link);
      link.click();
      link.remove();
    },
    error: function (xhr, status, error) {
      showAlert("Error downloading report: " + error, "danger");
    },
  });
}

function showNotificationModal() {
  if (!$("#notificationModal").length) {
    $("body").append(`
      <div class="modal fade" id="notificationModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Send Notification</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <form id="notification-form">
                <div class="form-group">
                  <label>Recipient Type</label>
                  <select class="form-control" name="recipient_type" required>
                    <option value="all">All Users</option>
                    <option value="active">Active Users</option>
                    <option value="inactive">Inactive Users</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Title</label>
                  <input type="text" class="form-control" name="title" required>
                </div>
                <div class="form-group">
                  <label>Message</label>
                  <textarea class="form-control" name="message" rows="3" required></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" id="send-notification-submit">Send</button>
            </div>
          </div>
        </div>
      </div>
    `);

    $("#send-notification-submit").click(function () {
      const form = $("#notification-form");
      const formData = {
        recipient_type: form.find('[name="recipient_type"]').val(),
        title: form.find('[name="title"]').val(),
        message: form.find('[name="message"]').val(),
      };

      $.ajax({
        url: "../php/admin/manage_notifications.php",
        method: "POST",
        data: formData,
        success: function (response) {
          if (response.status === "success") {
            $("#notificationModal").modal("hide");
            showAlert("Notification sent successfully", "success");
            form[0].reset();
          } else {
            showAlert(
              response.message || "Error sending notification",
              "danger"
            );
          }
        },
        error: function (xhr, status, error) {
          showAlert("Error sending notification: " + error, "danger");
        },
      });
    });
  }

  $("#notificationModal").modal("show");
}

// Utility Functions
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

// Add these functions after the loadLatestEnquiries function

function showAllEnquiriesModal() {
  // Create modal if it doesn't exist
  if (!$("#allEnquiriesModal").length) {
    $("body").append(`
      <div class="modal fade" id="allEnquiriesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">All Enquiries</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Message</th>
                      <th>Status</th>
                      <th>Date</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="all-enquiries-table">
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    `);
  }

  // Load all enquiries
  loadAllEnquiries();

  // Show modal
  $("#allEnquiriesModal").modal("show");
}

function loadAllEnquiries() {
  $.ajax({
    url: "../php/admin/get_all_enquiries.php",
    method: "GET",
    success: function (response) {
      if (response.status === "success") {
        updateAllEnquiriesTable(response.data);
      } else {
        showAlert(response.message || "Error loading enquiries", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading enquiries:", error);
      showAlert("Error loading enquiries", "danger");
    },
  });
}

function updateAllEnquiriesTable(enquiries) {
  const tbody = $("#all-enquiries-table");
  tbody.empty();

  if (!enquiries || enquiries.length === 0) {
    tbody.append(`
      <tr>
        <td colspan="7" class="text-center">No enquiries found</td>
      </tr>
    `);
    return;
  }

  enquiries.forEach((enquiry) => {
    tbody.append(`
      <tr class="${enquiry.status === "new" ? "table-warning" : ""}">
        <td>${enquiry.name}</td>
        <td>${enquiry.email}</td>
        <td>${enquiry.phone || "-"}</td>
        <td>${enquiry.message || "-"}</td>
        <td>
          <span class="badge badge-${
            enquiry.status === "new" ? "warning" : "success"
          }">
            ${enquiry.status}
          </span>
        </td>
        <td>${formatDate(enquiry.created_at)}</td>
        <td>
          ${
            enquiry.status === "new"
              ? `
            <button class="btn btn-sm btn-success mark-as-read" data-id="${enquiry.id}">
              <i class="fas fa-check"></i> Mark as Read
            </button>
          `
              : ""
          }
        </td>
      </tr>
    `);
  });
}

function markEnquiryAsRead(enquiryId) {
  $.ajax({
    url: "../php/admin/update_enquiry_status.php",
    method: "POST",
    data: {
      id: enquiryId,
      status: "read",
    },
    success: function (response) {
      if (response.status === "success") {
        loadAllEnquiries();
        loadLatestEnquiries();
        loadDashboardStats();
        showAlert("Enquiry marked as read", "success");
      } else {
        showAlert(response.message || "Error updating enquiry", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error updating enquiry:", error);
      showAlert("Error updating enquiry", "danger");
    },
  });
}
// Add this inside $(document).ready(function() { ... })

// Search functionality
$("#admin-search").on("keyup", function (e) {
  if (e.key === "Enter") {
    const searchTerm = $(this).val();
    // Implement your search functionality here
    console.log("Searching for:", searchTerm);
  }
});

// Update notification count
function updateNotificationCount() {
  $.ajax({
    url: "../php/admin/get_notifications.php",
    method: "GET",
    success: function (response) {
      if (response.status === "success") {
        const count = response.data.unread_count || 0;
        $("#notification-count").text(count);
        $("#notification-count").toggle(count > 0);
      }
    },
  });
}

// Load admin profile
function loadAdminProfile() {
  $.ajax({
    url: "../php/admin/get_admin_profile.php",
    method: "GET",
    success: function (response) {
      if (response.status === "success") {
        $("#admin-name").text(response.data.name);
        if (response.data.avatar) {
          $("#admin-avatar").attr("src", response.data.avatar);
        }
      }
    },
  });
}

// Initialize header components
updateNotificationCount();
loadAdminProfile();

// Refresh notification count periodically
setInterval(updateNotificationCount, 60000); // Every minute
