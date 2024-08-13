<?php
session_start();
include '../database/db_connection.php';

$title = $_POST['title'];
$user_id = $_SESSION['id'];

try {
    $list = new toDoList($title, $user_id);
    $list->add($conn);
    echo "List added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>