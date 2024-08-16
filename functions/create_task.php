<?php
header('Content-Type: application/json');
include '../database/db_connection.php'; // Include your database connection file

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['title']) && isset($data['list_id'])) {
    $title = $data['title'];
    $list_id = (int) $data['list_id'];

    // Prepare the SQL query
    $query = $conn->prepare('INSERT INTO tasks (list_id, title, status, deadline) VALUES (?, ?, "Pending", NULL)');

    // Check if the statement preparation was successful
    if ($query === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare SQL statement']);
        exit;
    }

    // Bind parameters
    $query->bind_param('is', $list_id, $title);

    // Execute the statement
    if ($query->execute()) {
        // Fetch the last inserted ID
        $task_id = $conn->insert_id;
        echo json_encode(['success' => true, 'task_id' => $task_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create task']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>
