<?php
require_once __DIR__ . '/../../config/Database.php';

class Challenge {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function create($user_id, $title, $description, $category, $deadline, $image = null) {
        try {
            $query = "INSERT INTO challenges (user_id, title, description, category, deadline, image) 
                      VALUES (:user_id, :title, :description, :category, :deadline, :image)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':deadline', $deadline);
            $stmt->bindParam(':image', $image);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
            
        } catch (PDOException $e) {
            die("Erreur SQL dans create(): " . $e->getMessage());
        }
    }

    public function getChallengeById($id) {
        try {
            $query = "SELECT c.*, u.username AS creator_name, u.avatar AS creator_avatar
                      FROM challenges c 
                      JOIN users u ON c.user_id = u.id 
                      WHERE c.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            echo "Erreur SQL dans getChallengeById(): " . $e->getMessage();
            return null;
        }
    }

    public function getChallengesByUser($user_id) {
        try {
            $query = "SELECT * FROM challenges WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function update($id, $user_id, $title, $description, $category, $deadline, $image = null) {
        if (!$this->isOwner($id, $user_id)) {
            return false;
        }
        try {
            if ($image) {
                $query = "UPDATE challenges 
                          SET title = :title, description = :description, 
                              category = :category, deadline = :deadline, image = :image 
                          WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':image', $image);
            } else {
                $query = "UPDATE challenges 
                          SET title = :title, description = :description, 
                              category = :category, deadline = :deadline 
                          WHERE id = :id";
                $stmt = $this->conn->prepare($query);
            }
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':deadline', $deadline);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            echo "Erreur SQL dans update(): " . $e->getMessage();
            return false;
        }
    }

    public function delete($id, $user_id) {
        if (!$this->isOwner($id, $user_id)) {
            return false;
        }
        try {
            $stmt = $this->conn->prepare("DELETE FROM submissions WHERE challenge_id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $stmt = $this->conn->prepare("DELETE FROM challenges WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            echo "Erreur SQL dans delete(): " . $e->getMessage();
            return false;
        }
    }

    public function isOwner($challenge_id, $user_id) {
        try {
            $query = "SELECT id FROM challenges WHERE id = :challenge_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':challenge_id', $challenge_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            echo "Erreur SQL dans isOwner(): " . $e->getMessage();
            return false;
        }
    }

    public function countSubmissions($challenge_id) {
        try {
            $query = "SELECT COUNT(*) as total FROM submissions WHERE challenge_id = :challenge_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':challenge_id', $challenge_id);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'];
            
        } catch (PDOException $e) {
            echo "Erreur SQL dans countSubmissions(): " . $e->getMessage();
            return 0;
        }
    }

    public function isOpen($challenge_id) {
        try {
            $query = "SELECT deadline FROM challenges WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $challenge_id);
            $stmt->execute();
            $challenge = $stmt->fetch();
            
            if ($challenge && $challenge['deadline']) {
                return strtotime($challenge['deadline']) > time();
            }
            return true;
            
        } catch (PDOException $e) {
            echo "Erreur SQL dans isOpen(): " . $e->getMessage();
            return false;
        }
    }

    public function getCategories() {
        try {
            $query = "SELECT DISTINCT category FROM challenges ORDER BY category";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            echo "Erreur SQL dans getCategories(): " . $e->getMessage();
            return [];
        }
    }

    // ===== LIKES SUR LES DÉFIS =====

    public function getLikesCount($challenge_id) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM challenge_likes WHERE challenge_id = :id");
            $stmt->bindParam(':id', $challenge_id);
            $stmt->execute();
            $r = $stmt->fetch();
            return $r['total'] ?? 0;
        } catch(PDOException $e) { 
            return 0; 
        }
    }

    public function hasLiked($user_id, $challenge_id) {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM challenge_likes WHERE user_id = :uid AND challenge_id = :cid");
            $stmt->bindParam(':uid', $user_id);
            $stmt->bindParam(':cid', $challenge_id);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) { 
            return false; 
        }
    }

    public function toggleLike($user_id, $challenge_id) {
        try {
            if ($this->hasLiked($user_id, $challenge_id)) {
                $stmt = $this->conn->prepare("DELETE FROM challenge_likes WHERE user_id = :uid AND challenge_id = :cid");
                $stmt->bindParam(':uid', $user_id);
                $stmt->bindParam(':cid', $challenge_id);
                $stmt->execute();
                return ['action' => 'unliked', 'count' => $this->getLikesCount($challenge_id)];
            } else {
                $stmt = $this->conn->prepare("INSERT INTO challenge_likes (user_id, challenge_id) VALUES (:uid, :cid)");
                $stmt->bindParam(':uid', $user_id);
                $stmt->bindParam(':cid', $challenge_id);
                $stmt->execute();
                return ['action' => 'liked', 'count' => $this->getLikesCount($challenge_id)];
            }
        } catch(PDOException $e) {
            return ['action' => 'error', 'count' => 0];
        }
    }

    // ===== COMMENTAIRES SUR LES DÉFIS =====

    public function getChallengeComments($challenge_id) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT cc.*, u.username, u.avatar 
                 FROM challenge_comments cc 
                 JOIN users u ON cc.user_id = u.id 
                 WHERE cc.challenge_id = :cid 
                 ORDER BY cc.created_at ASC"
            );
            $stmt->bindParam(':cid', $challenge_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) { 
            return []; 
        }
    }

    public function countChallengeComments($challenge_id) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM challenge_comments WHERE challenge_id = :cid");
            $stmt->bindParam(':cid', $challenge_id);
            $stmt->execute();
            $r = $stmt->fetch();
            return $r['total'] ?? 0;
        } catch(PDOException $e) { 
            return 0; 
        }
    }

    public function addChallengeComment($user_id, $challenge_id, $content) {
        try {
            if (empty(trim($content))) return false;
            $stmt = $this->conn->prepare(
                "INSERT INTO challenge_comments (user_id, challenge_id, content) VALUES (:uid, :cid, :content)"
            );
            $stmt->bindParam(':uid', $user_id);
            $stmt->bindParam(':cid', $challenge_id);
            $stmt->bindParam(':content', $content);
            if ($stmt->execute()) return $this->conn->lastInsertId();
            return false;
        } catch(PDOException $e) { 
            return false; 
        }
    }

    /**
     * Récupère le classement des défis d'une catégorie
     * Tri : par score (likes ou participations) DESC, puis par date DESC (ex-aequo)
     */
    public function getRanking($category, $sort = 'likes') {
        try {
            $orderBy = ($sort === 'participations')
                ? "submissions_count DESC, c.created_at DESC"
                : "likes_count DESC, c.created_at DESC";

            $query = "SELECT DISTINCT c.id, c.user_id, c.title, c.description, c.category,
                             c.deadline, c.image, c.created_at,
                             u.username AS creator_name, 
                             u.avatar AS creator_avatar,
                             (SELECT COUNT(*) FROM challenge_likes cl WHERE cl.challenge_id = c.id) AS likes_count,
                             (SELECT COUNT(*) FROM submissions s WHERE s.challenge_id = c.id) AS submissions_count
                      FROM challenges c 
                      JOIN users u ON c.user_id = u.id
                      WHERE c.category = :category
                      GROUP BY c.id
                      ORDER BY $orderBy";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':category', $category);
            $stmt->execute();
            return $stmt->fetchAll();

        } catch(PDOException $e) {
            return [];
        }
    }

    public function getAllChallengesSorted($sort = 'recent', $category = 'all', $search = '') {
        try {
            $conditions = [];
            $params = [];

            if ($category && $category !== 'all') {
                $conditions[] = "c.category = :category";
                $params[':category'] = $category;
            }
            if ($search) {
                $conditions[] = "(c.title LIKE :search OR c.description LIKE :search)";
                $params[':search'] = "%$search%";
            }
            $where = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

            // Version SANS GROUP BY - on prend TOUS les défis
            $query = "SELECT c.*, u.username AS creator_name, u.avatar AS creator_avatar,
                            (SELECT COUNT(*) FROM challenge_likes cl WHERE cl.challenge_id = c.id) AS likes_count,
                            (SELECT COUNT(*) FROM submissions s WHERE s.challenge_id = c.id) AS submissions_count
                      FROM challenges c 
                      JOIN users u ON c.user_id = u.id
                      $where";

            if ($sort === 'likes') {
                $query .= " ORDER BY likes_count DESC, c.created_at DESC";
            } elseif ($sort === 'participations') {
                $query .= " ORDER BY submissions_count DESC, c.created_at DESC";
            } else {
                $query .= " ORDER BY c.created_at DESC";
            }

            $stmt = $this->conn->prepare($query);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            
            $results = $stmt->fetchAll();
            
            return $results;
            
        } catch(PDOException $e) {
            return [];
        }
    }
}
?>