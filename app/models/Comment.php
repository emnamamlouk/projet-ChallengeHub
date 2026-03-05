<?php
require_once __DIR__ . '/../../config/Database.php';

class Comment {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function addComment($user_id, $submission_id, $content, $parent_id = null) {
        try {
            if(empty(trim($content))) return false;
            if(strlen($content) > 500) return false;
            $query = "INSERT INTO comments (user_id, submission_id, content, parent_id) 
                      VALUES (:user_id, :submission_id, :content, :parent_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':parent_id', $parent_id);
            if($stmt->execute()) return $this->conn->lastInsertId();
            return false;
        } catch(PDOException $e) { return false; }
    }

    public function updateComment($comment_id, $user_id, $content) {
        try {
            if(empty(trim($content))) return false;
            if(strlen($content) > 500) return false;
            if(!$this->isOwner($comment_id, $user_id)) return false;
            $query = "UPDATE comments SET content = :content WHERE id = :id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':id', $comment_id);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    public function getCommentsBySubmission($submission_id) {
        try {
            $query = "SELECT c.*, u.username, u.avatar
                      FROM comments c JOIN users u ON c.user_id = u.id
                      WHERE c.submission_id = :submission_id AND c.parent_id IS NULL
                      ORDER BY c.created_at ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->execute();
            $comments = $stmt->fetchAll();
            foreach ($comments as &$comment) {
                $comment['replies'] = $this->getReplies($comment['id']);
            }
            return $comments;
        } catch(PDOException $e) { return []; }
    }

    public function getReplies($parent_id) {
        try {
            $query = "SELECT c.*, u.username, u.avatar
                      FROM comments c JOIN users u ON c.user_id = u.id
                      WHERE c.parent_id = :parent_id
                      ORDER BY c.created_at ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':parent_id', $parent_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) { return []; }
    }

    public function getCommentById($id) {
        try {
            $query = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch(PDOException $e) { return null; }
    }

    public function deleteComment($id, $user_id) {
        try {
            if(!$this->isOwner($id, $user_id)) return false;
            $stmt = $this->conn->prepare("DELETE FROM comments WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    public function deleteCommentsBySubmission($submission_id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM comments WHERE submission_id = :submission_id");
            $stmt->bindParam(':submission_id', $submission_id);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    public function isOwner($comment_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM comments WHERE id = :comment_id AND user_id = :user_id");
            $stmt->bindParam(':comment_id', $comment_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) { return false; }
    }

    public function countComments($submission_id) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM comments WHERE submission_id = :submission_id");
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'];
        } catch(PDOException $e) { return 0; }
    }
}
?>