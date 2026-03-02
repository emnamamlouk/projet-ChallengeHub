<?php
require_once __DIR__ . '/../../config/Database.php';

class Vote {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function addVote($user_id, $submission_id) {
        try {
            if($this->hasVoted($user_id, $submission_id)) {
                return "already_voted";
            }
            if($this->isOwnSubmission($user_id, $submission_id)) {
                return "own_submission";
            }
            $query = "INSERT INTO votes (user_id, submission_id) VALUES (:user_id, :submission_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':submission_id', $submission_id);
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            if($e->errorInfo[1] == 1062) {
                return "already_voted";
            }
            return false;
        }
    }

    public function removeVote($user_id, $submission_id) {
        try {
            $query = "DELETE FROM votes WHERE user_id = :user_id AND submission_id = :submission_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':submission_id', $submission_id);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function hasVoted($user_id, $submission_id) {
        try {
            $query = "SELECT id FROM votes WHERE user_id = :user_id AND submission_id = :submission_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function isOwnSubmission($user_id, $submission_id) {
        try {
            $query = "SELECT id FROM submissions WHERE id = :submission_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getVoteCount($submission_id) {
        try {
            $query = "SELECT COUNT(*) as total FROM votes WHERE submission_id = :submission_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'];
        } catch(PDOException $e) {
            return 0;
        }
    }

    // Vote UNE SEULE FOIS - pas de retrait possible
    public function toggleVote($user_id, $submission_id) {
        try {
            if($this->hasVoted($user_id, $submission_id)) {
                return [
                    'action' => 'already_voted',
                    'success' => false,
                    'already_voted' => true,
                    'message' => 'Vous avez déjà voté pour cette participation',
                    'new_count' => $this->getVoteCount($submission_id)
                ];
            }
            if($this->isOwnSubmission($user_id, $submission_id)) {
                return [
                    'action' => 'error',
                    'success' => false,
                    'message' => 'Vous ne pouvez pas voter pour votre propre participation'
                ];
            }
            $result = $this->addVote($user_id, $submission_id);
            if($result === true) {
                return [
                    'action' => 'added',
                    'success' => true,
                    'new_count' => $this->getVoteCount($submission_id)
                ];
            } else {
                return [
                    'action' => 'error',
                    'success' => false,
                    'message' => is_string($result) ? $result : 'Erreur lors du vote'
                ];
            }
        } catch(PDOException $e) {
            return [
                'action' => 'error',
                'success' => false,
                'message' => 'Erreur technique'
            ];
        }
    }

    // Récupérer la liste des votants d'une participation
    public function getVotersForSubmission($submission_id) {
        try {
            $query = "SELECT u.id, u.username, u.avatar, v.created_at as voted_at
                      FROM votes v
                      JOIN users u ON v.user_id = u.id
                      WHERE v.submission_id = :submission_id
                      ORDER BY v.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }
    

    public function getUserVotes($user_id) {
    try {
        $query = "SELECT v.*, s.description, c.title as challenge_title, u.username
                  FROM votes v
                  JOIN submissions s ON v.submission_id = s.id
                  JOIN challenges c ON s.challenge_id = c.id
                  JOIN users u ON s.user_id = u.id
                  WHERE v.user_id = :user_id
                  ORDER BY v.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}
}
?>