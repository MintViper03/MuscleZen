$(document).ready(function () {
  loadUsers();

  // Handle user search
  $("#user-search").on("input", function () {
    loadUsers(1, $(this).val());
  });

  // Handle add user form submission
  $("#save-user").click(function () {
    const form = $("#add-user-form");

    // Clear previous error messages
    $(".alert").remove();

    // Basic form validation
    if (!validateUserForm(form)) {
      return;
    }

    saveUser();
  });

  // Handle edit user
  $(document).on("click", ".edit-user", function () {
    const userId = $(this).data("userid");
    loadUserDetails(userId);
  });

  // Handle update user
  $("#update-user").click(function () {
    const form = $("#edit-user-form");
    if (!validateEditForm(form)) {
      return;
    }
    updateUser(form);
  });

  // Handle delete user
  $(document).on("click", ".delete-user", function () {
    const userId = $(this).data("userid");
    if (confirm("Are you sure you want to delete this user?")) {
      deleteUser(userId);
    }
  });
});

// Form validation for new user
function validateUserForm(form) {
  const username = form.find("input[name='username']").val();
  const email = form.find("input[name='email']").val();
  const password = form.find("input[name='password']").val();

  if (!username || !email || !password) {
    showAlert("All fields are required", "danger");
    return false;
  }

  if (!isValidEmail(email)) {
    showAlert("Please enter a valid email address", "danger");
    return false;
  }

  if (password.length < 6) {
    showAlert("Password must be at least 6 characters long", "danger");
    return false;
  }

  return true;
}

// Form validation for edit user
function validateEditForm(form) {
  const username = form.find("input[name='username']").val();
  const email = form.find("input[name='email']").val();
  const password = form.find("input[name='password']").val();

  if (!username || !email) {
    showAlert("Username and email are required", "danger");
    return false;
  }

  if (!isValidEmail(email)) {
    showAlert("Please enter a valid email address", "danger");
    return false;
  }

  // Only validate password if it's provided
  if (password && password.length < 6) {
    showAlert("Password must be at least 6 characters long", "danger");
    return false;
  }

  return true;
}

function loadUsers(page = 1, search = "") {
  const filters = {
    page: page,
    limit: 10,
    sort: $("#sort-by").val(),
    search: search || $("#user-search").val(),
  };

  $.ajax({
    url: "../php/admin/get_users.php",
    method: "GET",
    data: filters,
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        updateUsersTable(response.data);
        updatePagination(response.pagination);
        updateShowingEntries(response.pagination);
      } else {
        showAlert(response.message || "Error loading users", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading users:", error);
      showAlert("Error loading users", "danger");
    },
  });
}

function saveUser() {
  const userData = $("#add-user-form").serialize();

  $.ajax({
    url: "../php/admin/add_user.php",
    method: "POST",
    data: userData,
    dataType: "json",
    beforeSend: function () {
      $("#save-user")
        .prop("disabled", true)
        .html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    },
    success: function (response) {
      if (response.status === "success") {
        $("#addUserModal").modal("hide");
        $("#add-user-form")[0].reset();
        loadUsers();
        showAlert("User added successfully", "success");
      } else {
        showAlert(response.message || "Error adding user", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error response:", xhr.responseText);
      let errorMessage = "Error adding user";
      try {
        const response = JSON.parse(xhr.responseText);
        errorMessage = response.message || errorMessage;
      } catch (e) {
        console.error("Error parsing error response:", e);
      }
      showAlert(errorMessage, "danger");
    },
    complete: function () {
      $("#save-user").prop("disabled", false).html("Add User");
    },
  });
}

function updateUser(form) {
  const userData = form.serialize();

  $.ajax({
    url: "../php/admin/update_user.php",
    method: "POST",
    data: userData,
    dataType: "json",
    beforeSend: function () {
      $("#update-user")
        .prop("disabled", true)
        .html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    },
    success: function (response) {
      if (response.status === "success") {
        $("#editUserModal").modal("hide");
        form[0].reset();
        loadUsers();
        showAlert("User updated successfully", "success");
      } else {
        showAlert(response.message || "Error updating user", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error response:", xhr.responseText);
      let errorMessage = "Error updating user";
      try {
        const response = JSON.parse(xhr.responseText);
        errorMessage = response.message || errorMessage;
      } catch (e) {
        console.error("Error parsing error response:", e);
      }
      showAlert(errorMessage, "danger");
    },
    complete: function () {
      $("#update-user").prop("disabled", false).html("Update User");
    },
  });
}

function deleteUser(userId) {
  $.ajax({
    url: "../php/admin/delete_user.php",
    method: "POST",
    data: { id: userId },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        loadUsers();
        showAlert("User deleted successfully", "success");
      } else {
        showAlert(response.message || "Error deleting user", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error deleting user:", error);
      showAlert("Error deleting user", "danger");
    },
  });
}

function loadUserDetails(userId) {
  $.ajax({
    url: "../php/admin/get_user_details.php",
    method: "GET",
    data: { id: userId },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        populateEditForm(response.data);
        $("#editUserModal").modal("show");
      } else {
        showAlert(response.message || "Error loading user details", "danger");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading user details:", error);
      showAlert("Error loading user details", "danger");
    },
  });
}

function populateEditForm(user) {
  const form = $("#edit-user-form");
  form.find("input[name='user_id']").val(user.id);
  form.find("input[name='username']").val(user.username);
  form.find("input[name='email']").val(user.email);
}

function updateUsersTable(users) {
  const tbody = $("#users-table-body");
  tbody.empty();

  if (users.length === 0) {
    tbody.append(`
            <tr>
                <td colspan="5" class="text-center">No users found</td>
            </tr>
        `);
    return;
  }

  users.forEach((user) => {
    tbody.append(`
            <tr>
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${formatDate(user.created_at)}</td>
                <td>
                    <button class="btn btn-sm btn-primary edit-user" data-userid="${
                      user.id
                    }">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-user" data-userid="${
                      user.id
                    }">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
  });
}

function isValidEmail(email) {
  const re =
    /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(String(email).toLowerCase());
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
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

function updatePagination(pagination) {
  const paginationContainer = $(".pagination-controls");
  paginationContainer.empty();

  if (pagination.total_pages <= 1) {
    return;
  }

  let html = '<ul class="pagination">';

  // Previous button
  html += `
        <li class="page-item ${pagination.page <= 1 ? "disabled" : ""}">
            <a class="page-link" href="#" data-page="${pagination.page - 1}" ${
    pagination.page <= 1 ? 'tabindex="-1"' : ""
  }>
                Previous
            </a>
        </li>
    `;

  // Page numbers
  for (let i = 1; i <= pagination.total_pages; i++) {
    if (
      i === 1 ||
      i === pagination.total_pages ||
      (i >= pagination.page - 2 && i <= pagination.page + 2)
    ) {
      html += `
                <li class="page-item ${i === pagination.page ? "active" : ""}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
    } else if (i === pagination.page - 3 || i === pagination.page + 3) {
      html +=
        '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
  }

  // Next button
  html += `
        <li class="page-item ${
          pagination.page >= pagination.total_pages ? "disabled" : ""
        }">
            <a class="page-link" href="#" data-page="${pagination.page + 1}" ${
    pagination.page >= pagination.total_pages ? 'tabindex="-1"' : ""
  }>
                Next
            </a>
        </li>
    `;

  html += "</ul>";
  paginationContainer.html(html);
}

function updateShowingEntries(pagination) {
  const start = (pagination.page - 1) * pagination.limit + 1;
  const end = Math.min(start + pagination.limit - 1, pagination.total);

  $("#showing-start").text(start);
  $("#showing-end").text(end);
  $("#total-entries").text(pagination.total);
}

