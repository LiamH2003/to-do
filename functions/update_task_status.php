<?php
require_once('../database/db_connection.php');
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents('php://input'), true);
$task_id = $data['task_id'] ?? '';
$status = $data['status'] ?? '';

if ($task_id && in_array($status, ['todo', 'done'])) {
    // Update the status in the database
    $update_stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param('si', $status, $task_id); // Bind string (s) and integer (i)
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'new_status' => $status]);
        } else {
            echo json_encode(['error' => 'No rows updated']);
        }
        $update_stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare update statement']);
    }
} else {
    echo json_encode(['error' => 'Invalid task ID or status']);
}

$conn->close();
?>
