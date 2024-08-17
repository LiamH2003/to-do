<?php
header('Content-Type: application/json');
include '../database/db_connection.php'; // Include your database connection file

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['title']) && isset($data['list_id'])) {
    $title = $data['title'];
    $list_id = (int) $data['list_id'];

    // Check if a task with the same title already exists in the specified list
    $checkQuery = $conn->prepare('SELECT COUNT(*) FROM tasks WHERE list_id = ? AND title = ?');
    $checkQuery->bind_param('is', $list_id, $title);
    $checkQuery->execute();
    $checkQuery->bind_result($count);
    $checkQuery->fetch();
    $checkQuery->close();

    if ($count > 0) {
        echo json_encode(['success' => false, 'error' => 'A task with this name already exists in the selected list.']);
        exit;
    }

    // Prepare the SQL query to insert the new task
    $query = $conn->prepare('INSERT INTO tasks (list_id, title, status, deadline) VALUES (?, ?, "todo", NULL)');

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
