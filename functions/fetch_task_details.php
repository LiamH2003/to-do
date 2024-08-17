<?php
header('Content-Type: application/json');
require_once '../database/db_connection.php'; // Include your database connection file

$taskId = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if ($taskId > 0) {
    // Query to fetch task details and associated files
    $sql = "SELECT tasks.title, tasks.deadline, tasks.description, tasks.status, 
            task_files.id AS file_id, task_files.filename, task_files.filepath
            FROM tasks
            LEFT JOIN task_files ON tasks.id = task_files.task_id
            WHERE tasks.id = ?";

    $stmt = $conn->prepare($sql);

    // Check if statement preparation failed
    if ($stmt === false) {
        echo json_encode(['error' => 'Failed to prepare task details statement: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        echo json_encode(['error' => 'Failed to execute task details query: ' . $stmt->error]);
        exit;
    }

    $task = [];
    $files = [];

    while ($row = $result->fetch_assoc()) {
        if (empty($task)) {
            // Populate task details
            $task['title'] = $row['title'];
            $task['deadline'] = $row['deadline'];
            $task['description'] = $row['description'];
            $task['status'] = $row['status'];
        }
        if ($row['file_id']) {
            // Collect files information
            $files[] = [
                'id' => $row['file_id'],
                'filename' => $row['filename'],
                'filepath' => $row['filepath']
            ];
        }
    }

    $task['files'] = $files;

    // Query to fetch comments associated with the task
    $comment_sql = "SELECT comments.comment, comments.created_at, account.username 
                    FROM comments 
                    JOIN account ON comments.user_id = account.id 
                    WHERE comments.task_id = ?";
    
    $comment_stmt = $conn->prepare($comment_sql);

    // Check if statement preparation failed for comments query
    if ($comment_stmt === false) {
        echo json_encode(['error' => 'Failed to prepare comments statement: ' . $conn->error]);
        exit;
    }

    $comment_stmt->bind_param('i', $taskId);
    $comment_stmt->execute();
    $comment_result = $comment_stmt->get_result();

    if ($comment_result === false) {
        echo json_encode(['error' => 'Failed to execute comments query: ' . $comment_stmt->error]);
        exit;
    }

    $comments = [];
    while ($comment_row = $comment_result->fetch_assoc()) {
        $comments[] = [
            'comment' => $comment_row['comment'],
            'created_at' => $comment_row['created_at'],
            'username' => $comment_row['username']
        ];
    }

    $task['comments'] = $comments;

    echo json_encode($task);

    $stmt->close();
    $comment_stmt->close();
} else {
    echo json_encode(['error' => 'Invalid task ID']);
}

$conn->close();
?>
