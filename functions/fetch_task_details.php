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

    if ($stmt === false) {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        echo json_encode(['error' => 'Failed to execute query']);
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
    echo json_encode($task);

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid task ID']);
}

$conn->close();
?>
