$(document).ready(function() {
    // Initialize workouts management
    loadWorkoutStats();
    loadWorkoutsList();

    // Handle workout form submission
    $('#add-workout-form').on('submit', function(e) {
        e.preventDefault();
        saveWorkout();
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

function loadWorkoutStats() {
    $.ajax({
        url: '../php/admin/get_workout_stats.php',
        method: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                updateWorkoutStats(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading workout stats:', error);
        }
    });
}

function loadWorkoutsList() {
    $.ajax({
        url: '../php/admin/get_workouts.php',
        method: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                displayWorkoutsList(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading workouts:', error);
        }
    });
}

function saveWorkout() {
    const formData = new FormData($('#add-workout-form')[0]);
    
    $.ajax({
        url: '../php/admin/save_workout.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                $('#addWorkoutModal').modal('hide');
                loadWorkoutsList();
                showAlert('Workout saved successfully', 'success');
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving workout:', error);
            showAlert('Error saving workout', 'danger');
        }
    });
}

function displayWorkoutsList(workouts) {
    const tableBody = $('#workouts-table-body');
    tableBody.empty();
    
    workouts.forEach(workout => {
        tableBody.append(`
            <tr>
                <td>${workout.name}</td>
                <td>${workout.category}</td>
                <td>${workout.duration} min</td>
                <td>
                    <span class="badge badge-${getDifficultyBadge(workout.difficulty)}">
                        ${workout.difficulty}
                    </span>
                </td>
                <td>
                    <span class="badge badge-${getStatusBadge(workout.status)}">
                        ${workout.status}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editWorkout(${workout.id})"
                            data-toggle="tooltip" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteWorkout(${workout.id})"
                            data-toggle="tooltip" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="previewWorkout(${workout.id})"
                            data-toggle="tooltip" title="Preview">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `);
    });
    
    // Reinitialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
}

function getDifficultyBadge(difficulty) {
    const badges = {
        'beginner': 'success',
        'intermediate': 'warning',
        'advanced': 'danger'
    };
    return badges[difficulty] || 'secondary';
}

function getStatusBadge(status) {
    const badges = {
        'active': 'success',
        'draft': 'warning',
        'archived': 'secondary'
    };
    return badges[status] || 'secondary';
}

function editWorkout(id) {
    $.ajax({
        url: '../php/admin/get_workout.php',
        method: 'GET',
        data: { id: id },
        success: function(response) {
            if (response.status === 'success') {
                populateWorkoutForm(response.data);
                $('#addWorkoutModal').modal('show');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading workout:', error);
        }
    });
}

function deleteWorkout(id) {
    if (confirm('Are you sure you want to delete this workout?')) {
        $.ajax({
            url: '../php/admin/delete_workout.php',
            method: 'POST',
            data: { id: id },
            success: function(response) {
                if (response.status === 'success') {
                    loadWorkoutsList();
                    showAlert('Workout deleted successfully', 'success');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error deleting workout:', error);
                showAlert('Error deleting workout', 'danger');
            }
        });
    }
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
    
    $('.admin-content').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
}
