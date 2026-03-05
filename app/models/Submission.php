<?php
require_once __DIR__ . '/../../config/Database.php';

class Submission {
    private $conn;
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    public function create($challenge_id, $user_id, $description, $image = null, $link = null) {
        try {
            if ($this->hasUserParticipated($challenge_id, $user_id)) {
                return "Vous avez déjà participé à ce défi";
            }
            $query = "INSERT INTO submissions (challenge_id, user_id, description, image, link) 
                      VALUES (:challenge_id, :user_id, :description, :image, :link)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':challenge_id', $challenge_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':link', $link);
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    public function getSubmissionsByChallenge($challenge_id) {
        try {
            $query = "SELECT s.*, u.username, u.avatar 
                      FROM submissions s JOIN users u ON s.user_id = u.id
                      WHERE s.challenge_id = :challenge_id ORDER BY s.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':challenge_id', $challenge_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
public function getSubmissionById($id) {
    try {
        $query = "SELECT s.*, u.username, u.avatar
                  FROM submissions s
                  JOIN users u ON s.user_id = u.id
                  WHERE s.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}
public function getSubmissionsByUser($user_id) {
        try {
            $query = "SELECT s.*, c.title AS challenge_title, c.id AS challenge_id
                      FROM submissions s JOIN challenges c ON s.challenge_id = c.id
                      WHERE s.user_id = :user_id ORDER BY s.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function update($id, $user_id, $description, $image = null, $link = null) {
        try {
            if (!$this->isOwner($id, $user_id)) {
                return false;
            }
            if ($image) {
                $query = "UPDATE submissions SET description = :description, image = :image, link = :link WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':image', $image);
            } else {
                $query = "UPDATE submissions SET description = :description, link = :link WHERE id = :id";
                $stmt = $this->conn->prepare($query);
            }
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':link', $link);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id, $user_id) {
        try {
            if (!$this->isOwner($id, $user_id)) {
                return false;
            }
            $stmt = $this->conn->prepare("DELETE FROM votes WHERE submission_id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $stmt = $this->conn->prepare("DELETE FROM comments WHERE submission_id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $stmt = $this->conn->prepare("DELETE FROM submissions WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function isOwner($submission_id, $user_id) {
        try {
            $query = "SELECT id FROM submissions WHERE id = :submission_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function hasUserParticipated($challenge_id, $user_id) {
        try {
            $query = "SELECT id FROM submissions WHERE challenge_id = :challenge_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':challenge_id', $challenge_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getVoteCount($submission_id) {
        try {
            $query = "SELECT COUNT(*) AS total FROM votes WHERE submission_id = :submission_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getRanking($category = null, $limit = 10) {
        try {
            $query = "SELECT s.id, s.description, s.image, s.created_at,
                             u.username, u.avatar,
                             c.title AS challenge_title, c.id AS challenge_id,
                             COUNT(v.id) AS votes_count
                      FROM submissions s
                      JOIN users u ON s.user_id = u.id
                      JOIN challenges c ON s.challenge_id = c.id
                      LEFT JOIN votes v ON s.id = v.submission_id";
            if ($category && $category !== 'all') {
                $query .= " WHERE c.category = :category";
            }
            $query .= " GROUP BY s.id ORDER BY votes_count DESC, s.created_at DESC LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            if ($category && $category !== 'all') {
                $stmt->bindParam(':category', $category);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // ── BONUS : top N participations toutes catégories (utilisé par ApiController) ──
    public function getTopSubmissions(int $limit = 10): array {
        try {
            $query = "SELECT s.id, s.description, s.image, s.link, s.created_at,
                             u.username, u.avatar,
                             c.title AS challenge_title, c.id AS challenge_id, c.category
                      FROM submissions s
                      JOIN users u ON s.user_id = u.id
                      JOIN challenges c ON s.challenge_id = c.id
                      LEFT JOIN votes v ON s.id = v.submission_id
                      GROUP BY s.id
                      ORDER BY COUNT(v.id) DESC, s.created_at DESC
                      LIMIT :lim";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>