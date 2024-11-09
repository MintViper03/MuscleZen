$(document).ready(function () {
  loadSettings();

  // Form submissions
  $("#general-settings-form").submit(function (e) {
    e.preventDefault();
    saveSettings("general", $(this).serialize());
  });

  $("#security-settings-form").submit(function (e) {
    e.preventDefault();
    saveSettings("security", $(this).serialize());
  });

  $("#email-settings-form").submit(function (e) {
    e.preventDefault();
    saveSettings("email", $(this).serialize());
  });

  $("#backup-settings-form").submit(function (e) {
    e.preventDefault();
    saveSettings("backup", $(this).serialize());
  });

  $("#api-settings-form").submit(function (e) {
    e.preventDefault();
    saveSettings("api", $(this).serialize());
  });

  // Test email button
  $("#test-email").click(function () {
    testEmailSettings();
  });

  // Manual backup button
  $("#manual-backup").click(function () {
    createManualBackup();
  });

  // Generate API key
  $("#generate-api-key").click(function () {
    generateApiKey();
  });

  // Navigation
  $(".list-group-item").click(function (e) {
    e.preventDefault();
    $(this).addClass("active").siblings().removeClass("active");
    const section = $(this).attr("href").substring(1);
    showSection(section);
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
  $("#site-name").val(settings.general.site_name);
  $("#site-description").val(settings.general.site_description);
  $("#timezone").val(settings.general.timezone);
  $("#maintenance-mode").prop("checked", settings.general.maintenance_mode);

  // Security Settings
  $("#session-timeout").val(settings.security.session_timeout);
  $("#max-login-attempts").val(settings.security.max_login_attempts);
  $("#require-uppercase").prop("checked", settings.security.require_uppercase);
  $("#require-numbers").prop("checked", settings.security.require_numbers);
  $("#require-special").prop("checked", settings.security.require_special);
  $("#two-factor-auth").prop("checked", settings.security.two_factor_auth);

  // Email Settings
  $("#smtp-host").val(settings.email.smtp_host);
  $("#smtp-port").val(settings.email.smtp_port);
  $("#smtp-username").val(settings.email.smtp_username);
  $("#from-email").val(settings.email.from_email);
  $("#from-name").val(settings.email.from_name);

  // Backup Settings
  $("#auto-backup").prop("checked", settings.backup.auto_backup);
  $("#backup-frequency").val(settings.backup.frequency);
  $("#backup-location").val(settings.backup.location);
  $("#backup-retention").val(settings.backup.retention);

  // API Settings
  $("#enable-api").prop("checked", settings.api.enabled);
  $("#api-key").val(settings.api.api_key);
  $("#rate-limit").val(settings.api.rate_limit);
  $("#allowed-origins").val(settings.api.allowed_origins);
}

function saveSettings(type, data) {
  $.ajax({
    url: "../php/admin/save_settings.php",
    method: "POST",
    data: {
      type: type,
      settings: data,
    },
    success: function (response) {
      if (response.status === "success") {
        showAlert("Settings saved successfully", "success");
      } else {
        showAlert(response.message || "Error saving settings", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error saving settings:", error);
      showAlert("Error saving settings", "danger");
    },
  });
}

function testEmailSettings() {
  const emailSettings = $("#email-settings-form").serialize();

  $.ajax({
    url: "../php/admin/test_email.php",
    method: "POST",
    data: emailSettings,
    success: function (response) {
      if (response.status === "success") {
        showAlert("Test email sent successfully", "success");
      } else {
        showAlert(response.message || "Error sending test email", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error testing email:", error);
      showAlert("Error sending test email", "danger");
    },
  });
}

function createManualBackup() {
  $.ajax({
    url: "../php/admin/manage_backup.php",
    method: "POST",
    data: { action: "create" },
    success: function (response) {
      if (response.status === "success") {
        showAlert("Backup created successfully", "success");
      } else {
        showAlert(response.message || "Error creating backup", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error creating backup:", error);
      showAlert("Error creating backup", "danger");
    },
  });
}

function generateApiKey() {
  $.ajax({
    url: "../php/admin/generate_api_key.php",
    method: "POST",
    success: function (response) {
      if (response.status === "success") {
        $("#api-key").val(response.api_key);
        showAlert("New API key generated", "success");
      } else {
        showAlert(response.message || "Error generating API key", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error generating API key:", error);
      showAlert("Error generating API key", "danger");
    },
  });
}

function showSection(section) {
  $(".settings-section").hide();
  $(`#${section}-settings`).show();
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
