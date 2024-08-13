<?php
session_start();
include '../database/db_connection.php';

// Check if list_id is provided
if (isset($_GET['list_id'])) {
    $list_id = intval($_GET['list_id']);
    $user_id = $_SESSION['id']; // Assuming user ID is stored in session

    // Query to fetch tasks for the provided list ID
    $sql = "SELECT id, title, deadline, status FROM tasks WHERE list_id = ? AND list_id IN (SELECT id FROM lists WHERE user_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $list_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    // Return tasks as JSON
    echo json_encode($tasks);
}
?>
