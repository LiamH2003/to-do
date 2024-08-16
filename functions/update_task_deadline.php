<?php
require_once '../database/db_connection.php'; // Include your DB connection

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (isset($input['task_id']) && isset($input['deadline'])) {
    $task_id = $input['task_id'];
    $deadline = $input['deadline'];

    // Prepare and execute SQL query
    $query = "UPDATE tasks SET deadline = ? WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('si', $deadline, $task_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Query preparation error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}

$conn->close();
?>
