function toggleTaskStatus(taskId) {
    $.ajax({
        url: 'toggle_status.php',
        type: 'POST',
        data: { task_id: taskId },
        success: function(response) {
            // Update UI to reflect new status
        },
        error: function() {
            alert("An error occurred, please try again.");
        }
    });
}
