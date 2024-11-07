$(document).ready(function() {
    // Initialize content management
    loadContentStats();
    loadContentList();

    // Handle tab switching
    $('.nav-tabs a').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
        const target = $(this).attr('href').replace('#', '');
        loadContentByType(target);
    });

    // Handle content form submission
    $('#add-content-form').on('submit', function(e) {
        e.preventDefault();
        saveContent();
    });

    // Content type change handler
    $('select[name="content_type"]').on('change', function() {
        updateFormFields($(this).val());
    });
});

function loadContentStats() {
    $.ajax({
        url: '../php/admin/get_content_stats.php',
        method: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                updateContentStats(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading content stats:', error);
        }
    });
}

function loadContentList(type = 'posts') {
    $.ajax({
        url: '../php/admin/get_content_list.php',
        method: 'GET',
        data: { type: type },
        success: function(response) {
            if (response.status === 'success') {
                displayContentList(response.data, type);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading content list:', error);
        }
    });
}

function saveContent() {
    const formData = new FormData($('#add-content-form')[0]);
    
    $.ajax({
        url: '../php/admin/save_content.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                $('#addContentModal').modal('hide');
                loadContentList();
                showAlert('Content saved successfully', 'success');
            } else {
                showAlert(response.message, 'danger');
            }
            } else {
                console.error('Error:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving content:', error);
            showAlert('Error saving content', 'danger');
            console.error('Error loading content list:', error);
        }
}

function updateFormFields(contentType) {
    const contentField = $('.form-group:has(textarea[name="content"])');
    const mediaField = $('.form-group:has(input[name="media"])');
function saveContent() {
    const formData = new FormData($('#add-content-form')[0]);
    
    switch(contentType) {
        case 'blog':
            contentField.show();
            mediaField.show();
            break;
        case 'video':
            contentField.hide();
            mediaField.show();
            break;
        case 'image':
            contentField.hide();
            mediaField.show();
            break;
    }
}

function displayContentList(data, type) {
    const tableBody = $(`#${type}-table-body`);
    tableBody.empty();
    $.ajax({
        url: '../php/admin/save_content.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                $('#addContentModal').modal('hide');
                loadContentList();
                showAlert('Content saved successfully', 'success');
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving content:', error);
            showAlert('Error saving content', 'danger');
        }
                    </button>
                </td>
            </tr>
        `);
    });
}

function getStatusBadge(status) {
    const badges = {
        'published': 'success',
        'draft': 'warning',
        'archived': 'secondary',
        'pending': 'info'
    };
    return badges[status] || 'secondary';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
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
    switch(contentType) {
        case 'blog':
            contentField.show();
            mediaField.show();
            break;
        case 'video':
            contentField.hide();
            mediaField.show();
            break;
        case 'image':
            contentField.hide();
            mediaField.show();
            break;
    }
}

function displayContentList(data, type) {
    const tableBody = $(`#${type}-table-body`);
    tableBody.empty();
    
    $('.admin-content').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
    data.forEach(item => {
        tableBody.append(`
