<?php
session_start();
include '../database/db_connection.php';

if (isset($_GET['task_id'])) {
    $task_id = intval($_GET['task_id']);
    $user_id = $_SESSION['id']; // Assuming user ID is stored in session

    // Query to delete the task
    $sql = "DELETE FROM tasks WHERE id = ? AND list_id IN (SELECT id FROM lists WHERE user_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $task_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete task.']);
    }
}
?>
