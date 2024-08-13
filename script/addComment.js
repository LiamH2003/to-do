function addComment(taskId, comment) {
    $.ajax({
        url: 'add_comment.php',
        type: 'POST',
        data: { task_id: taskId, comment: comment },
        success: function(response) {
            // Update UI with new comment
        },
        error: function() {
            alert("An error occurred, please try again.");
        }
    });
}
