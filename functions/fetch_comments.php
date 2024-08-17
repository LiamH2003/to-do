<?php
require_once('../database/db_connection.php');

$task_id = $_GET['task_id'] ?? '';

if ($task_id) {
    $stmt = $pdo->prepare("SELECT comment FROM comments WHERE task_id = ?");
    $stmt->execute([$task_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($comments);
} else {
    echo json_encode(['error' => 'No task ID provided.']);
}
?>
