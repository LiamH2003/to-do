<?php
header('Content-Type: application/json');
require_once '../database/db_connection.php'; // Include your database connection file

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$taskId = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0; // User ID should be securely managed
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($taskId > 0 && !empty($comment)) {
    $sql = "INSERT INTO comments (task_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param('iis', $taskId, $userId, $comment);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to add comment']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid task ID or empty comment']);
}

$conn->close();
?>
