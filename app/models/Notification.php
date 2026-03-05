<?php
require_once __DIR__ . '/../../config/Database.php';

/**
 * Modèle Notification — Notifications AJAX (fonctionnalité bonus)
 * Emplacement : /app/models/Notification.php
 *
 * Gère la création, la récupération et le marquage comme "lu"
 * des notifications en temps (quasi-)réel via polling AJAX.
 *
 * Types de notifications :
 *  - new_vote       : quelqu'un a voté pour votre participation
 *  - new_comment    : quelqu'un a commenté votre participation
 *  - new_submission : quelqu'un a participé à votre défi
 *  - badge_earned   : vous avez obtenu un badge
 */
class Notification {

    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // ----------------------------------------------------------------
    // Créer une notification pour un utilisateur cible
    // ----------------------------------------------------------------
    public function create(int $recipient_id, string $type, string $message, ?int $link_id = null): bool {
        try {
            // Éviter les doublons dans la même heure pour le même type/lien
            if ($link_id !== null) {
                $dup = $this->conn->prepare(
                    "SELECT id FROM notifications
                     WHERE recipient_id = :rid AND type = :type AND link_id = :lid
                       AND is_read = 0
                       AND created_at > NOW() - INTERVAL 1 HOUR"
                );
                $dup->bindParam(':rid',  $recipient_id, PDO::PARAM_INT);
                $dup->bindParam(':type', $type);
                $dup->bindParam(':lid',  $link_id,      PDO::PARAM_INT);
                $dup->execute();
                if ($dup->rowCount() > 0) return false;
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO notifications (recipient_id, type, message, link_id)
                 VALUES (:rid, :type, :msg, :lid)"
            );
            $stmt->bindParam(':rid',  $recipient_id, PDO::PARAM_INT);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':msg',  $message);
            $stmt->bindParam(':lid',  $link_id,      PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // ----------------------------------------------------------------
    // Récupérer les N dernières notifications d'un utilisateur
    // ----------------------------------------------------------------
    public function getForUser(int $user_id, int $limit = 10): array {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id, type, message, link_id, is_read, created_at
                 FROM notifications
                 WHERE recipient_id = :uid
                 ORDER BY created_at DESC
                 LIMIT :lim"
            );
            $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':lim', $limit,   PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // ----------------------------------------------------------------
    // Compter les notifications non lues
    // ----------------------------------------------------------------
    public function countUnread(int $user_id): int {
        try {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) FROM notifications
                 WHERE recipient_id = :uid AND is_read = 0"
            );
            $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // ----------------------------------------------------------------
    // Marquer une notification comme lue
    // ----------------------------------------------------------------
    public function markRead(int $notification_id, int $user_id): bool {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE notifications SET is_read = 1
                 WHERE id = :nid AND recipient_id = :uid"
            );
            $stmt->bindParam(':nid', $notification_id, PDO::PARAM_INT);
            $stmt->bindParam(':uid', $user_id,         PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // ----------------------------------------------------------------
    // Marquer toutes les notifications comme lues
    // ----------------------------------------------------------------
    public function markAllRead(int $user_id): bool {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE notifications SET is_read = 1
                 WHERE recipient_id = :uid AND is_read = 0"
            );
            $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?> 