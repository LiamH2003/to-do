<?php
header('Content-Type: application/json');
require_once '../database/db_connection.php'; // Include your database connection file

$fileId = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;

if ($fileId > 0) {
    // Query to delete the file
    $sql = "DELETE FROM task_files WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param('i', $fileId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete file']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid file ID']);
}

$conn->close();
?>
