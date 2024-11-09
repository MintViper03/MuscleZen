$(document).ready(function () {
  loadContent();

  // Handle manage buttons
  $(".manage-posts").click(function () {
    $('#contentTabs a[href="#posts"]').tab("show");
    loadContentByType("post");
  });

  $(".manage-videos").click(function () {
    $('#contentTabs a[href="#videos"]').tab("show");
    loadContentByType("video");
  });

  $(".manage-images").click(function () {
    $('#contentTabs a[href="#images"]').tab("show");
    loadContentByType("image");
  });

  $(".manage-reported").click(function () {
    loadReportedContent();
  });

  // Handle content type change
  $("select[name='content_type']").change(function () {
    toggleContentFields($(this).val());
  });

  // Handle save content
  $("#save-content").click(function () {
    const form = $("#add-content-form");
    if (validateContentForm(form)) {
      saveContent(form);
    }
  });

  // Handle edit content
  $(document).on("click", ".edit-content", function () {
    const contentId = $(this).data("contentid");
    loadContentDetails(contentId);
  });

  // Handle delete content
  $(document).on("click", ".delete-content", function () {
    const contentId = $(this).data("contentid");
    if (confirm("Are you sure you want to delete this content?")) {
      deleteContent(contentId);
    }
  });

  // Handle tab switching
  $("#contentTabs a").on("shown.bs.tab", function (e) {
    const target = $(e.target).attr("href").substring(1);
    if (target !== "all") {
      loadContentByType(target.slice(0, -1)); // Remove 's' from end
    } else {
      loadAllContent();
    }
  });

  // Handle content search
  $("#content-search").on(
    "input",
    debounce(function () {
      const searchTerm = $(this).val();
      const activeTab = $("#contentTabs .active").attr("href");
      if (activeTab === "#all") {
        loadAllContent(searchTerm);
      } else {
        loadContentByType(activeTab.slice(1, -1), searchTerm);
      }
    }, 300)
  );
});

function loadContent() {
  $.ajax({
    url: "../php/admin/get_content_stats.php",
    method: "GET",
    success: function (response) {
      if (response.status === "success") {
        updateContentStats(response.stats);
        loadAllContent();
      } else {
        showAlert(response.message || "Error loading content stats", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading content stats:", error);
      showAlert("Error loading content stats", "danger");
    },
  });
}

function loadAllContent(search = "") {
  $.ajax({
    url: "../php/admin/get_content_list.php",
    method: "GET",
    data: { search: search },
    success: function (response) {
      if (response.status === "success") {
        updateContentTable(response.data);
      } else {
        showAlert(response.message || "Error loading content", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading content:", error);
      showAlert("Error loading content", "danger");
    },
  });
}
// ... (previous code remains the same)

function loadContentByType(type, search = "") {
  $.ajax({
    url: "../php/admin/get_content_list.php",
    method: "GET",
    data: {
      type: type,
      search: search,
    },
    success: function (response) {
      if (response.status === "success") {
        updateContentTable(response.data);
      } else {
        showAlert(response.message || "Error loading content", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading content:", error);
      showAlert("Error loading content", "danger");
    },
  });
}

function loadReportedContent() {
  $.ajax({
    url: "../php/admin/get_content_list.php",
    method: "GET",
    data: { reported: true },
    success: function (response) {
      if (response.status === "success") {
        updateContentTable(response.data, true);
      } else {
        showAlert(
          response.message || "Error loading reported content",
          "danger"
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading reported content:", error);
      showAlert("Error loading reported content", "danger");
    },
  });
}

function updateContentStats(stats) {
  $("#posts-count").text(stats.posts || 0);
  $("#videos-count").text(stats.videos || 0);
  $("#images-count").text(stats.images || 0);
  $("#reported-count").text(stats.reported || 0);
}

function updateContentTable(content, isReported = false) {
  const tbody = $("#content-table-body");
  tbody.empty();

  if (!content || content.length === 0) {
    tbody.append(`
            <tr>
                <td colspan="7" class="text-center">No content found</td>
            </tr>
        `);
    return;
  }

  content.forEach((item) => {
    const reportedBadge = item.is_reported
      ? '<span class="badge badge-danger ml-2">Reported</span>'
      : "";

    tbody.append(`
            <tr>
                <td>
                    ${item.title}
                    ${reportedBadge}
                </td>
                <td><span class="badge badge-info">${item.type}</span></td>
                <td>${item.category}</td>
                <td>
                    <span class="badge badge-${getStatusBadgeClass(
                      item.status
                    )}">
                        ${item.status}
                    </span>
                </td>
                <td>${item.views}</td>
                <td>${formatDate(item.created_at)}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-content" data-contentid="${
                          item.id
                        }">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-content" data-contentid="${
                          item.id
                        }">
                            <i class="fas fa-trash"></i>
                        </button>
                        ${
                          item.is_reported
                            ? `
                            <button class="btn btn-sm btn-warning review-report" data-contentid="${item.id}">
                                <i class="fas fa-exclamation-triangle"></i>
                            </button>
                        `
                            : ""
                        }
                    </div>
                </td>
            </tr>
        `);
  });
}

function saveContent(form) {
  const formData = new FormData(form[0]);

  $.ajax({
    url: "../php/admin/save_content.php",
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      if (response.status === "success") {
        $("#addContentModal").modal("hide");
        form[0].reset();
        loadContent();
        showAlert("Content saved successfully", "success");
      } else {
        showAlert(response.message || "Error saving content", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error saving content:", error);
      showAlert("Error saving content", "danger");
    },
  });
}

function loadContentDetails(contentId) {
  $.ajax({
    url: "../php/admin/get_content_list.php",
    method: "GET",
    data: { id: contentId },
    success: function (response) {
      if (response.status === "success") {
        populateEditForm(response.data);
        $("#editContentModal").modal("show");
      } else {
        showAlert(
          response.message || "Error loading content details",
          "danger"
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading content details:", error);
      showAlert("Error loading content details", "danger");
    },
  });
}

function populateEditForm(content) {
  const form = $("#edit-content-form");
  form.find("input[name='content_id']").val(content.id);
  form.find("input[name='title']").val(content.title);
  form.find("select[name='category']").val(content.category);
  form.find("select[name='status']").val(content.status);
  form.find("textarea[name='content']").val(content.content);

  // Show current media if exists
  const mediaContainer = form.find("#current-media");
  mediaContainer.empty();
  if (content.media_url) {
    if (content.type === "image") {
      mediaContainer.html(`
                <img src="${content.media_url}" class="img-thumbnail" style="max-height: 200px">
            `);
    } else if (content.type === "video") {
      mediaContainer.html(`
                <video controls class="img-thumbnail" style="max-height: 200px">
                    <source src="${content.media_url}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            `);
    }
  }
}

function deleteContent(contentId) {
  $.ajax({
    url: "../php/admin/delete_content.php",
    method: "POST",
    data: { id: contentId },
    success: function (response) {
      if (response.status === "success") {
        loadContent();
        showAlert("Content deleted successfully", "success");
      } else {
        showAlert(response.message || "Error deleting content", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error deleting content:", error);
      showAlert("Error deleting content", "danger");
    },
  });
}

function validateContentForm(form) {
  const title = form.find("input[name='title']").val();
  const type = form.find("select[name='content_type']").val();
  const category = form.find("select[name='category']").val();

  if (!title || !category) {
    showAlert("Please fill all required fields", "danger");
    return false;
  }

  return true;
}

function toggleContentFields(contentType) {
  $(".post-fields").toggle(contentType === "post");
}

function getStatusBadgeClass(status) {
  const classes = {
    draft: "warning",
    published: "success",
    archived: "secondary",
  };
  return classes[status] || "secondary";
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
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
