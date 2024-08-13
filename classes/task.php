<?php
// classes/task.php

class Task {
    private $id;
    private $title;
    private $deadline;
    private $status;
    private $list_id;

    public function __construct($title, $list_id, $deadline = null) {
        $this->setTitle($title);
        $this->setDeadline($deadline);
        $this->list_id = $list_id;
        $this->status = 'todo'; // Default status
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        if (empty($title)) {
            throw new Exception("Title cannot be empty.");
        }
        $this->title = $title;
    }

    public function getDeadline() {
        return $this->deadline;
    }

    public function setDeadline($deadline) {
        if (!empty($deadline) && !strtotime($deadline)) {
            throw new Exception("Invalid deadline format.");
        }
        $this->deadline = $deadline;
    }

    public function addTask($conn) {
        $sql = "INSERT INTO tasks (list_id, title, deadline, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isss', $this->list_id, $this->title, $this->deadline, $this->status);
        $stmt->execute();
    }

    public static function deleteTask($conn, $task_id) {
        $sql = "DELETE FROM tasks WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $task_id);
        $stmt->execute();
    }

    public function getDaysRemaining() {
        if (empty($this->deadline)) {
            return "No deadline set";
        }
        $currentDate = new DateTime();
        $deadlineDate = new DateTime($this->deadline);
        $interval = $currentDate->diff($deadlineDate);
        return $currentDate > $deadlineDate ? "Task overdue" : $interval->days . " days remaining";
    }

    public static function updateTaskStatus($conn, $task_id, $status) {
        $sql = "UPDATE tasks SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $status, $task_id);
        $stmt->execute();
    }

    public static function checkDuplicateTask($conn, $list_id, $title) {
        $count = 0;
        $sql = "SELECT COUNT(*) FROM tasks WHERE list_id = ? AND title = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Failed to prepare the SQL statement.");
        }
        
        $stmt->bind_param('is', $list_id, $title);
        $stmt->execute();
        
        $stmt->bind_result($count);
        $stmt->fetch();
        
        $stmt->close();
        
        return $count > 0;
    }
    
}
?>
