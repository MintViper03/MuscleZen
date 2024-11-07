$(document).ready(function () {
  // Load initial settings
  loadSettings();

  // Handle settings form submissions
  $("#general-settings-form").on("submit", function (e) {
    e.preventDefault();
    saveSettings("general", $(this));
  });

  $("#security-settings-form").on("submit", function (e) {
    e.preventDefault();
    saveSettings("security", $(this));
  });

  // Settings navigation
  $(".list-group-item").on("click", function (e) {
    e.preventDefault();

    // Update active state
    $(".list-group-item").removeClass("active");
    $(this).addClass("active");

    // Show corresponding section
    const targetId = $(this).attr("href");
    $(".settings-section").hide();
    $(targetId).show();
  });

  // Maintenance mode toggle handler
  $("#maintenance-mode").on("change", function () {
    if ($(this).is(":checked")) {
      confirmMaintenanceMode();
    }
  });
});

function loadSettings() {
  $.ajax({
    url: "../php/admin/get_settings.php",
    method: "GET",
    success: function (response) {
      if (response.status === "success") {
        populateSettings(response.data);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading settings:", error);
      showAlert("Error loading settings", "danger");
    },
  });
}

function populateSettings(settings) {
  // General Settings
  $('input[name="site_name"]').val(settings.general.site_name);
  $('textarea[name="site_description"]').val(settings.general.site_description);
  $('select[name="timezone"]').val(settings.general.timezone);
  $("#maintenance-mode").prop("checked", settings.general.maintenance_mode);

  // Security Settings
  $('input[name="session_timeout"]').val(settings.security.session_timeout);
  $('input[name="max_login_attempts"]').val(
    settings.security.max_login_attempts
  );
  $("#require-uppercase").prop(
    "checked",
    settings.security.password_requirements.uppercase
  );
  $("#require-numbers").prop(
    "checked",
    settings.security.password_requirements.numbers
  );
  $("#require-special").prop(
    "checked",
    settings.security.password_requirements.special
  );
}

function saveSettings(type, form) {
  const formData = new FormData(form[0]);
  formData.append("type", type);

  $.ajax({
    url: "../php/admin/save_settings.php",
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      if (response.status === "success") {
        showAlert("Settings saved successfully", "success");
      } else {
        showAlert(response.message, "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error saving settings:", error);
      showAlert("Error saving settings", "danger");
    },
  });
}

function confirmMaintenanceMode() {
  if (
    confirm(
      "Enabling maintenance mode will make the site inaccessible to regular users. Continue?"
    )
  ) {
    // Additional confirmation for duration
    const duration = prompt(
      "Enter maintenance duration in minutes (leave empty for indefinite):",
      "60"
    );

    if (duration !== null) {
      enableMaintenanceMode(duration);
    } else {
      $("#maintenance-mode").prop("checked", false);
    }
  } else {
    $("#maintenance-mode").prop("checked", false);
  }
}

function enableMaintenanceMode(duration) {
  $.ajax({
    url: "../php/admin/set_maintenance_mode.php",
    method: "POST",
    data: {
      enabled: true,
      duration: duration,
    },
    success: function (response) {
      if (response.status === "success") {
        showAlert("Maintenance mode enabled", "success");
      } else {
        $("#maintenance-mode").prop("checked", false);
        showAlert(response.message, "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error setting maintenance mode:", error);
      $("#maintenance-mode").prop("checked", false);
      showAlert("Error enabling maintenance mode", "danger");
    },
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

  $(".admin-content").prepend(alert);
  setTimeout(() => alert.alert("close"), 5000);
}
