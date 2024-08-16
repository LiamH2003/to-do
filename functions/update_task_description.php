<?php
// update_task_description.php

// Database connection
require '../database/db_connection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['task_id']) && isset($data['description'])) {
    $task_id = intval($data['task_id']);
    $description = $data['description'];

    // Prepare and execute SQL statement to update task description
    $sql = "UPDATE tasks SET description = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('si', $description, $task_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update task description']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare SQL statement']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}

$conn->close();
?>
