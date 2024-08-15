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

    // Static method for deletion, now with cascading deletions
    public static function delete($id, $user_id) {
        global $conn;

        // Start a transaction
        $conn->begin_transaction();

        try {
            // First, delete any comments related to tasks in this list
            $sql = "DELETE comments FROM comments 
                    INNER JOIN tasks ON comments.task_id = tasks.id 
                    WHERE tasks.list_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Second, delete any files related to tasks in this list
            $sql = "DELETE task_files FROM task_files 
                    INNER JOIN tasks ON task_files.task_id = tasks.id 
                    WHERE tasks.list_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Third, delete tasks related to this list
            $sql = "DELETE FROM tasks WHERE list_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Finally, delete the list itself, ensuring it belongs to the user
            $sql = "DELETE FROM lists WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();

            // Commit the transaction if all queries succeed
            $conn->commit();
        } catch (Exception $e) {
            // Rollback the transaction if any query fails
            $conn->rollback();
            throw new Exception("Deletion failed: " . $e->getMessage());
        }
    }
}
?>
