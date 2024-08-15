<?php
session_start();
include '../database/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['title']) && !empty($data['title'])) {
        $title = htmlspecialchars($data['title']);
        $user_id = $_SESSION['id']; // Assuming user ID is stored in session

        // Insert the new list into the database
        $sql = "INSERT INTO lists (user_id, title) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $user_id, $title);

        if ($stmt->execute()) {
            $list_id = $stmt->insert_id;
            echo json_encode(['success' => true, 'list_id' => $list_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();
?>
