<?php
session_start();
include '../database/db_connection.php';

$task_id = $_POST['task_id'];
$status = $_POST['status']; // Expected to be 'todo' or 'done'

try {
    Task::updateTaskStatus($conn, $task_id, $status);
    echo "Task status updated successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
