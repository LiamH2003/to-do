<?php
// Database connection
$servername = "localhost:3306";
$username = "root";  // Replace with your database username
$password = "track22";  // Replace with your database password
$dbname = "to-do";  // The name of your database

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);
$task_id = isset($data['task_id']) ? $data['task_id'] : null;
$title = isset($data['title']) ? $data['title'] : null;

$response = array('success' => false);

if ($task_id && $title) {
    $stmt = $conn->prepare("UPDATE tasks SET title = ? WHERE id = ?");
    $stmt->bind_param("si", $title, $task_id);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = "Error updating task title.";
    }

    $stmt->close();
} else {
    $response['error'] = "Invalid input.";
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
