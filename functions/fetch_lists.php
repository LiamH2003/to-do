<?php
session_start();
require_once '../database/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['id']; // Ensure this is the correct session variable for the user ID

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the lists for the logged-in user
$sql = "SELECT id, title FROM lists WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all lists as an associative array
$lists = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

// Output lists in JSON format
header('Content-Type: application/json');
echo json_encode($lists);
?>
