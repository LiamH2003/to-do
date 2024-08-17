<?php
header('Content-Type: application/json');
require_once '../database/db_connection.php'; // Include your database connection file

$taskId = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;

if ($taskId > 0) {
    // Query to delete the task
    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param('i', $taskId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete task']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid task ID']);
}

$conn->close();
?>
