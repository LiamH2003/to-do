<?php
require_once('../database/db_connection.php');

$data = json_decode(file_get_contents('php://input'), true);
$task_id = $data['task_id'] ?? '';
$comment = $data['comment'] ?? '';

if ($task_id && $comment) {
    $stmt = $pdo->prepare("INSERT INTO comments (task_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
    $user_id = $_SESSION['id']; // Assuming you have a session with user ID
    $stmt->execute([$task_id, $user_id, $comment]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Invalid data.']);
}
?>
