<?php
class TodoList {
    private $id;
    private $title;
    private $user_id;

    public function __construct($title, $user_id) {
        $this->setTitle($title);
        $this->user_id = $user_id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        if(empty($title)) {
            throw new Exception("Title cannot be empty.");
        }
        $this->title = $title;
    }

    public function add() {
        global $conn;
        $sql = "INSERT INTO lists (user_id, title) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $this->user_id, $this->title);
        $stmt->execute();
    }

    public function update() {
        global $conn;
        $sql = "UPDATE lists SET title = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $this->title, $this->id);
        $stmt->execute();
    }

    // Method to retrieve a list from the database
    public static function getById($id) {
        global $conn;
        $sql = "SELECT * FROM lists WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_object('TodoList');
    }

    // Static method for deletion
    public static function delete($id) {
        global $conn;
        $sql = "DELETE FROM lists WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
?>
