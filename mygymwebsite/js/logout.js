function handleLogout() {
  if (confirm("Are you sure you want to logout?")) {
    $.ajax({
      url: "php/logout.php",
      method: "POST",
      dataType: "json",
      cache: false,
      success: function (response) {
        // Clear all stored data
        localStorage.clear();
        sessionStorage.clear();

        // Remove any authentication cookies
        document.cookie.split(";").forEach(function (c) {
          document.cookie = c
            .replace(/^ +/, "")
            .replace(
              /=.*/,
              "=;expires=" + new Date().toUTCString() + ";path=/"
            );
        });

        // Force redirect to index page
        window.location.replace("index.html");
      },
      error: function () {
        // Fallback redirect
        window.location.replace("index.html");
      },
      complete: function () {
        // Ensure browser doesn't cache the previous page
        if (window.history && window.history.pushState) {
          window.history.pushState("", "", window.location.href);
          window.history.replaceState("", "", window.location.href);
        }
        window.location.replace("index.html");
      },
    });
  }
}

// Update sidebar logout handler
$(document).ready(function () {
  $("#logout-link")
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      handleLogout();
    });
});
