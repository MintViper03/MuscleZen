$(document).ready(function() {
    // Initial load
    loadUsers();

    // Search handler
    let searchTimeout;
    $('#user-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadUsers();
        }, 500);
    });

    // Filter handlers
    $('#apply-filters').click(loadUsers);

    // Add user handler
    $('#save-user').click(function() {
        const formData = new FormData($('#add-user-form')[0]);
        
        $.ajax({
            url: '../php/admin/add_user.php',
            method: 'POST',
            data: Object.fromEntries(formData),
            success: function(response) {
                if (response.status === 'success') {
                    $('#addUserModal').modal('hide');
                    loadUsers();
                    alert('User added successfully');
                } else {
                    alert(response.message || 'Error adding user');
                }
            }
        });
    });

    // Edit user handler
 $('#update-user').click(function() {
    const formData = new FormData($('#edit-user-form')[0]);
    
    $.ajax({
        url: '../php/admin/update_user.php', // Ensure this path is correct
        method: 'POST',
        data: Object.fromEntries(formData),
        success: function(response) {
            if (response.status === 'success') {
                $('#editUserModal').modal('hide');
                loadUsers();
                alert('User updated successfully');
            } else {
                alert(response.message || 'Error updating user');
            }
        }
    });
});


function loadUsers(page = 1) {
    const filters = {
        search: $('#user-search').val(),
        status: $('#status-filter').val(),
        role: $('#role-filter').val(),
        sort: $('#sort-by').val(),
        page: page
    };

    $.ajax({
        url: '../php/admin/get_users.php',
        method: 'GET',
        data: filters,
        success: function(response) {
            if (response.status === 'success') {
                displayUsers(response.data.users);
                updatePagination(response.data.pagination);
            }
        }
    });
}

function displayUsers(users) {
    const tbody = $('#users-table-body');
    tbody.empty();

    users.forEach(user => {
        tbody.append(`
            <tr>
                <td>${user.id}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${user.profile_image || '../images/default-avatar.png'}" 
                             alt="${user.username}"
                             class="rounded-circle mr-2"
                             style="width: 30px; height: 30px;">
                        ${user.username}
                    </div>
                </td>
                <td>${user.email}</td>
                <td>
                    <span class="badge badge-${getStatusBadgeClass(user.status)}">
                        ${user.status}
                    </span>
                </td>
                <td>${formatDate(user.created_at)}</td>
                <td>
                    <button class="btn btn-sm btn-info mr-1" onclick="editUser(${user.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function updatePagination(pagination) {
    const controls = $('.pagination-controls');
    controls.empty();

    let html = '<ul class="pagination">';
    
    // Previous button
    html += `
        <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadUsers(${pagination.current_page - 1})">
                Previous
            </a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        html += `
            <li class="page-item ${pagination.current_page === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadUsers(${i})">${i}</a>
            </li>
        `;
    }

    // Next button
    html += `
        <li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadUsers(${pagination.current_page + 1})">
                Next
            </a>
        </li>
    `;

    html += '</ul>';
    controls.html(html);

    // Update showing entries text
    $('#showing-start').text(pagination.showing_start);
    $('#showing-end').text(pagination.showing_end);
    $('#total-entries').text(pagination.total_entries);
}

function editUser(userId) {
    $.ajax({
        url: '../php/admin/get_user.php',
        method: 'GET',
        data: { id: userId },
        success: function(response) {
            if (response.status === 'success') {
                const form = $('#edit-user-form');
                const user = response.data;

                form.find('[name="user_id"]').val(user.id);
                form.find('[name="username"]').val(user.username);
                form.find('[name="email"]').val(user.email);
                form.find('[name="status"]').val(user.status);
                form.find('[name="role"]').val(user.role);

                $('#editUserModal').modal('show');
            }
        }
    });
}
// ... (previous code remains the same until deleteUser function)

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        $.ajax({
            url: '../php/admin/delete_user.php',
            method: 'POST',
            data: { id: userId },
            success: function(response) {
                if (response.status === 'success') {
                    loadUsers();
                    alert('User deleted successfully');
                } else {
                    alert(response.message || 'Error deleting user');
                }
            }
        });
    }
}

function getStatusBadgeClass(status) {
    const classes = {
        'active': 'success',
        'inactive': 'warning',
        'suspended': 'danger'
    };
    return classes[status] || 'secondary';
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}


