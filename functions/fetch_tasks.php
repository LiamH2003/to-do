<?php
session_start();
include '../database/db_connection.php';

if (isset($_GET['list_id'])) {
    $list_id = intval($_GET['list_id']);
    $user_id = $_SESSION['id']; // Assuming user ID is stored in session

    // Fetch sorting option from query parameters
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'deadline-ascending';

    // Determine sorting column and order
    switch ($sort) {
        case 'title-ascending':
            $orderBy = 'title ASC';
            break;
        case 'title-descending':
            $orderBy = 'title DESC';
            break;
        case 'deadline-ascending':
            $orderBy = 'deadline ASC';
            break;
        case 'deadline-descending':
            $orderBy = 'deadline DESC';
            break;
        default:
            $orderBy = 'deadline ASC';
            break;
    }

    $sql = "SELECT id, title, deadline, status FROM tasks WHERE list_id = ? AND list_id IN (SELECT id FROM lists WHERE user_id = ?) ORDER BY $orderBy";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $list_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    echo json_encode($tasks);
}

?>
