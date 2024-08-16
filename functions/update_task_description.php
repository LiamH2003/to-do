<?php
header('Content-Type: application/json');
require_once '../db_connection.php'; // Include your database connection file

$data = json_decode(file_get_contents('php://input'), true);
$taskId = isset($data['task_id']) ? intval($data['task_id']) : 0;
$description = isset($data['description']) ? $data['description'] : '';

if ($taskId > 0 && !empty($description)) {
    $sql = "UPDATE tasks SET description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $description, $taskId);
    $success = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>
