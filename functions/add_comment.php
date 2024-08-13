<?php
session_start();
include('../database/db_connection.php'); 

$task_id = $_POST['task_id'];
$comment = $_POST['comment'];
$user_id = $_SESSION['id'];

$sql = "INSERT INTO comments (task_id, user_id, comment) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iis', $task_id, $user_id, $comment);
$stmt->execute();
?>
