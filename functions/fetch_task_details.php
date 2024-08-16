<?php
header('Content-Type: application/json');
require_once '../database/db_connection.php'; // Include your database connection file

$taskId = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if ($taskId > 0) {
    $sql = "SELECT title, deadline, description FROM tasks WHERE id = ?";
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

    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
        echo json_encode($task);  // This should return the task details as JSON
    } else {
        echo json_encode(['error' => 'Task not found']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid task ID']);
}

$conn->close();
?>
