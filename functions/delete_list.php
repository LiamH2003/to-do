<?php
session_start();
include '../database/db_connection.php';

$list_id = $_POST['list_id'];

try {
    TodoList::delete($conn, $list_id);
    echo "List deleted successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
