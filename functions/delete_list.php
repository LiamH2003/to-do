<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../start/index.php");
    exit;
}

require_once '../database/db_connection.php'; // Include your database connection file
require_once '../classes/list.php'; // Include the TodoList class file

// Get the list_id from the request
$list_id = isset($_GET['list_id']) ? intval($_GET['list_id']) : 0;

// Ensure the list_id is valid and the user is authorized to delete this list
if ($list_id > 0) {
    // Get the current user's ID from the session
    $user_id = $_SESSION["id"];
    
    try {
        // Use the TodoList class to delete the list and related data
        TodoList::delete($list_id, $user_id);

        // Return a success message as JSON
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Handle any exceptions that occur during deletion
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    // Invalid list_id or unauthorized request
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
