$(document).ready(function () {
  const loginForm = $("#admin-login-form");
  const loginButton = $("#login-button");
  const errorContainer = $("#error-container");

  loginForm.on("submit", function (e) {
    e.preventDefault();

    // Clear previous errors
    errorContainer.empty().hide();

    // Disable login button and show loading state
    loginButton
      .prop("disabled", true)
      .html(
        '<span class="spinner-border spinner-border-sm"></span> Logging in...'
      );

    const formData = {
      username: $("#admin-username").val().trim(),
      password: $("#admin-password").val(),
    };

    $.ajax({
      url: "../php/admin/login.php",
      method: "POST",
      data: formData,
      dataType: "json",
    })
      .done(function (response) {
        console.log("Login response:", response);

        if (response.status === "success") {
          // Show success message briefly before redirect
          showMessage("Login successful! Redirecting...", "success");
          setTimeout(() => {
            window.location.href = "dashboard.html";
          }, 1000);
        } else {
          showMessage(response.message || "Login failed", "danger");
        }
      })
      .fail(function (xhr, status, error) {
        console.error("Login error:", { xhr, status, error });
        let errorMessage = "Connection error occurred";

        try {
          const response = JSON.parse(xhr.responseText);
          errorMessage = response.message || errorMessage;
        } catch (e) {
          console.error("Error parsing response:", e);
        }

        showMessage(errorMessage, "danger");
      })
      .always(function () {
        // Reset button state
        loginButton.prop("disabled", false).html("Login");
      });
  });

  function showMessage(message, type = "danger") {
    errorContainer
      .removeClass()
      .addClass(`alert alert-${type}`)
      .html(message)
      .show();
  }

  // Add input validation
  $("#admin-username, #admin-password").on("input", function () {
    const username = $("#admin-username").val().trim();
    const password = $("#admin-password").val();

    loginButton.prop("disabled", !username || !password);
  });

  // Initialize form state
  loginButton.prop("disabled", true);
});
