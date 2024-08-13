<?php
include('../database/db_connection.php'); 

$task_id = $_POST['task_id'];

// Get current status
$sql = "SELECT status FROM tasks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $task_id);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();

// Toggle status
$new_status = ($status === 'todo') ? 'done' : 'todo';
$sql = "UPDATE tasks SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $new_status, $task_id);
$stmt->execute();
?>
