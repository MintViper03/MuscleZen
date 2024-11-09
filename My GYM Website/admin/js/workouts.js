$(document).ready(function() {
    loadWorkouts();
    
    // Handle workout search
    $("#workout-search").on("input", function() {
        loadWorkouts($(this).val());
    });
    
    // Handle add workout
    $("#save-workout").click(function(e) {
        e.preventDefault();
        const form = $("#add-workout-form");
        if (validateWorkoutForm(form)) {
            saveWorkout(form);
        }
    });

    // Handle edit workout button click
    $(document).on('click', '.edit-workout', function() {
        const workoutId = $(this).data('workoutid');
        loadWorkoutDetails(workoutId);
    });

    // Handle update workout
    $("#update-workout").click(function(e) {
        e.preventDefault();
        const form = $("#edit-workout-form");
        if (validateWorkoutForm(form)) {
            updateWorkout(form);
        }
    });

    // Handle delete workout
    $(document).on('click', '.delete-workout', function() {
        const workoutId = $(this).data('workoutid');
        if(confirm('Are you sure you want to delete this workout?')) {
            deleteWorkout(workoutId);
        }
    });
});

function loadWorkouts(search = "") {
    $.ajax({
        url: "../php/admin/get_workout.php",
        method: "GET",
        data: { search: search },
        dataType: 'json',
        success: function(response) {
            if(response.status === "success") {
                updateWorkoutsTable(response.data);
                updateWorkoutStats(response.stats);
            } else {
                showAlert(response.message || "Error loading workouts", "danger");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error loading workouts:", error);
            showAlert("Error loading workouts", "danger");
        }
    });
}

function saveWorkout(form) {
    const formData = new FormData(form[0]);
    
    $.ajax({
        url: "../php/admin/save_workout.php",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.status === "success") {
                $("#addWorkoutModal").modal("hide");
                form[0].reset();
                loadWorkouts();
                showAlert("Workout added successfully", "success");
            } else {
                showAlert(response.message || "Error adding workout", "danger");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error adding workout:", error);
            showAlert("Error adding workout", "danger");
        }
    });
}

function updateWorkout(form) {
    const formData = new FormData(form[0]);
    
    $.ajax({
        url: "../php/admin/update_workout.php",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.status === "success") {
                $("#editWorkoutModal").modal("hide");
                loadWorkouts();
                showAlert("Workout updated successfully", "success");
            } else {
                showAlert(response.message || "Error updating workout", "danger");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error updating workout:", error);
            showAlert("Error updating workout", "danger");
        }
    });
}

function deleteWorkout(workoutId) {
    $.ajax({
        url: "../php/admin/delete_workout.php",
        method: "POST",
        data: { id: workoutId },
        dataType: 'json',
        success: function(response) {
            if(response.status === "success") {
                loadWorkouts();
                showAlert("Workout deleted successfully", "success");
            } else {
                showAlert(response.message || "Error deleting workout", "danger");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error deleting workout:", error);
            showAlert("Error deleting workout", "danger");
        }
    });
}

function loadWorkoutDetails(workoutId) {
    $.ajax({
        url: "../php/admin/get_workout.php",
        method: "GET",
        data: { id: workoutId },
        success: function(response) {
            if(response.status === "success") {
                populateEditForm(response.data);
                $("#editWorkoutModal").modal("show");
            } else {
                showAlert(response.message || "Error loading workout details", "danger");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error loading workout details:", error);
            showAlert("Error loading workout details", "danger");
        }
    });
}

function populateEditForm(workout) {
    const form = $("#edit-workout-form");
    form.find("input[name='workout_id']").val(workout.id);
    form.find("input[name='workout_name']").val(workout.name);
    form.find("textarea[name='description']").val(workout.description);
    form.find("select[name='category']").val(workout.category);
    form.find("select[name='difficulty']").val(workout.difficulty);
    form.find("input[name='duration']").val(workout.duration);
    form.find("input[name='calories_burn']").val(workout.calories_burn);
}

function validateWorkoutForm(form) {
    const name = form.find("[name='workout_name']").val();
    const category = form.find("[name='category']").val();
    const difficulty = form.find("[name='difficulty']").val();
    const duration = form.find("[name='duration']").val();
    
    if (!name || !category || !difficulty || !duration) {
        showAlert("All required fields must be filled out", "danger");
        return false;
    }
    
    if (isNaN(duration) || duration <= 0) {
        showAlert("Duration must be a positive number", "danger");
        return false;
    }
    
    return true;
}

function updateWorkoutsTable(workouts) {
    const tbody = $("#workouts-table-body");
    tbody.empty();
    
    if (!workouts || workouts.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center">No workouts found</td>
            </tr>
        `);
        return;
    }
    
    workouts.forEach(workout => {
        tbody.append(`
            <tr>
                <td>${workout.name}</td>
                <td>${workout.category}</td>
                <td>${workout.duration} mins</td>
                <td>${workout.difficulty}</td>
                <td>
                    <span class="badge badge-${workout.status === 'active' ? 'success' : 'warning'}">
                        ${workout.status}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary edit-workout" data-workoutid="${workout.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-workout" data-workoutid="${workout.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function updateWorkoutStats(stats) {
    $("#strength-count").text(stats.strength || 0);
    $("#cardio-count").text(stats.cardio || 0);
    $("#flexibility-count").text(stats.flexibility || 0);
    $("#hiit-count").text(stats.hiit || 0);
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
        $(".alert").alert('close');
    }, 5000);
}
