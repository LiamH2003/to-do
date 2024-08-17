<?php
// upload_task_file.php
header('Content-Type: application/json');
require_once '../database/db_connection.php';

$taskId = $_POST['task_id'] ?? '';
$file = $_FILES['file'] ?? null;

if ($taskId && $file && $file['error'] === UPLOAD_ERR_OK) {
    $filename = basename($file['name']);
    $filepath = '../uploads/' . $filename;

    // Move uploaded file to the server directory
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Insert file information into the database
        $stmt = $conn->prepare("INSERT INTO task_files (task_id, filename, filepath, uploaded_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iss', $taskId, $filename, $filepath);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'file_id' => $conn->insert_id]);
        } else {
            echo json_encode(['error' => 'Failed to insert file record']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to move uploaded file']);
    }
} else {
    echo json_encode(['error' => 'Invalid input']);
}

$conn->close();

?>