<?php
header('Content-Type: application/json');
require_once '../db_connection.php'; // Include your database connection file

$data = json_decode(file_get_contents('php://input'), true);
$taskId = isset($data['task_id']) ? intval($data['task_id']) : 0;
$deadline = isset($data['deadline']) ? $data['deadline'] : '';

if ($taskId > 0 && !empty($deadline)) {
    $sql = "UPDATE tasks SET deadline = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $deadline, $taskId);
    $success = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>
