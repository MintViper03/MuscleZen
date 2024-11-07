// Reload utility functions for admin panel
let reloadTimer = null;
const RELOAD_INTERVAL = 300000; // 5 minutes

function reloadDataTables() {
  if (typeof loadDashboardStats === "function") {
    loadDashboardStats();
  }
  if (typeof loadActivityLog === "function") {
    loadActivityLog();
  }
  if (typeof loadUsersList === "function") {
    loadUsersList();
  }
}

// Initialize auto reload with HTTP polling
function initializeAutoReload() {
  // Clear existing timer if any
  if (reloadTimer) {
    clearInterval(reloadTimer);
  }

  // Set new timer
  reloadTimer = setInterval(() => {
    reloadDataTables();
  }, RELOAD_INTERVAL);
}

// Manual reload handler
function handleManualReload() {
  $(".reload-btn").on("click", function (e) {
    e.preventDefault();
    const target = $(this).data("reload-target");

    // Show loading spinner
    $(this).find("i").addClass("fa-spin");

    // Perform specific reloads based on target
    switch (target) {
      case "dashboard":
        loadDashboardStats();
        break;
      case "activity":
        loadActivityLog();
        break;
      case "users":
        loadUsersList();
        break;
      default:
        reloadDataTables();
    }

    // Remove spinning after 1 second
    setTimeout(() => {
      $(this).find("i").removeClass("fa-spin");
    }, 1000);
  });
}

// Initialize reload functionality
$(document).ready(function () {
  handleManualReload();
  initializeAutoReload();

  // Add keyboard shortcut (Ctrl/Cmd + R) for reload
  $(document).on("keydown", function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === "r") {
      e.preventDefault();
      reloadDataTables();
    }
  });
});

// Export functions for use in other scripts
window.reloadDataTables = reloadDataTables;
window.initializeAutoReload = initializeAutoReload;
window.handleManualReload = handleManualReload;
