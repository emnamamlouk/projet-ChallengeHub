<?php
require_once __DIR__ . '/../../config/Database.php';

/**
 * Modèle Badge — Système de badges (fonctionnalité bonus)
 * Emplacement : /app/models/Badge.php
 *
 * Gère la définition des badges, leur attribution aux utilisateurs,
 * et la vérification automatique des conditions d'obtention.
 */
class Badge {

    private $conn;

    // ----------------------------------------------------------------
    // Définition statique de tous les badges disponibles
    // ----------------------------------------------------------------
    private static $definitions = [
        'first_challenge' => [
            'label'       => 'Premier défi',
            'description' => 'Vous avez créé votre premier défi.',
            'icon'        => 'bi-flag-fill',
            'color'       => 'badge-primary',
        ],
        'first_submission' => [
            'label'       => 'Premier participant',
            'description' => 'Vous avez soumis votre première participation.',
            'icon'        => 'bi-send-fill',
            'color'       => 'badge-success',
        ],
        'popular_submission' => [
            'label'       => 'Participation populaire',
            'description' => 'Une de vos participations a reçu 10 votes ou plus.',
            'icon'        => 'bi-star-fill',
            'color'       => 'badge-warning',
        ],
        'active_commenter' => [
            'label'       => 'Commentateur actif',
            'description' => 'Vous avez posté 5 commentaires ou plus.',
            'icon'        => 'bi-chat-dots-fill',
            'color'       => 'badge-info',
        ],
        'challenge_creator_5' => [
            'label'       => 'Créateur prolifique',
            'description' => 'Vous avez créé 5 défis ou plus.',
            'icon'        => 'bi-trophy-fill',
            'color'       => 'badge-danger',
        ],
        'top_voter' => [
            'label'       => 'Grand votant',
            'description' => 'Vous avez voté pour 10 participations ou plus.',
            'icon'        => 'bi-hand-thumbs-up-fill',
            'color'       => 'badge-secondary',
        ],
    ];

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // ----------------------------------------------------------------
    // Récupérer les badges d'un utilisateur
    // ----------------------------------------------------------------
    public function getUserBadges(int $user_id): array {
        try {
            $stmt = $this->conn->prepare(
                "SELECT badge_key, obtained_at
                 FROM user_badges
                 WHERE user_id = :uid
                 ORDER BY obtained_at DESC"
            );
            $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Enrichir chaque ligne avec la définition du badge
            $result = [];
            foreach ($rows as $row) {
                $key = $row['badge_key'];
                if (isset(self::$definitions[$key])) {
                    $result[] = array_merge(
                        ['key' => $key, 'obtained_at' => $row['obtained_at']],
                        self::$definitions[$key]
                    );
                }
            }
            return $result;
        } catch (PDOException $e) {
            return [];
        }
    }

    // ----------------------------------------------------------------
    // Vérifier et attribuer automatiquement les badges mérités
    // Appelée après chaque action utilisateur (création, vote, commentaire…)
    // ----------------------------------------------------------------
    public function checkAndAward(int $user_id): array {
        $awarded = [];

        // Badge : premier défi créé
        if ($this->meetsCondition_first_challenge($user_id)) {
            if ($this->award($user_id, 'first_challenge')) {
                $awarded[] = 'first_challenge';
            }
        }

        // Badge : première participation soumise
        if ($this->meetsCondition_first_submission($user_id)) {
            if ($this->award($user_id, 'first_submission')) {
                $awarded[] = 'first_submission';
            }
        }

        // Badge : participation populaire (≥ 10 votes)
        if ($this->meetsCondition_popular_submission($user_id)) {
            if ($this->award($user_id, 'popular_submission')) {
                $awarded[] = 'popular_submission';
            }
        }

        // Badge : commentateur actif (≥ 5 commentaires)
        if ($this->meetsCondition_active_commenter($user_id)) {
            if ($this->award($user_id, 'active_commenter')) {
                $awarded[] = 'active_commenter';
            }
        }

        // Badge : créateur prolifique (≥ 5 défis)
        if ($this->meetsCondition_challenge_creator_5($user_id)) {
            if ($this->award($user_id, 'challenge_creator_5')) {
                $awarded[] = 'challenge_creator_5';
            }
        }

        // Badge : grand votant (≥ 10 votes donnés)
        if ($this->meetsCondition_top_voter($user_id)) {
            if ($this->award($user_id, 'top_voter')) {
                $awarded[] = 'top_voter';
            }
        }

        return $awarded;
    }

    // ----------------------------------------------------------------
    // Attribuer un badge (une seule fois par utilisateur)
    // Retourne true si nouveau badge, false si déjà possédé
    // ----------------------------------------------------------------
    public function award(int $user_id, string $badge_key): bool {
        try {
            // Vérifier si déjà attribué
            $check = $this->conn->prepare(
                "SELECT id FROM user_badges WHERE user_id = :uid AND badge_key = :key"
            );
            $check->bindParam(':uid', $user_id, PDO::PARAM_INT);
            $check->bindParam(':key', $badge_key);
            $check->execute();
            if ($check->rowCount() > 0) {
                return false; // Déjà obtenu
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO user_badges (user_id, badge_key) VALUES (:uid, :key)"
            );
            $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':key', $badge_key);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // ----------------------------------------------------------------
    // Retourner toutes les définitions (pour la vue)
    // ----------------------------------------------------------------
    public static function getAllDefinitions(): array {
        return self::$definitions;
    }

    // ----------------------------------------------------------------
    // Conditions d'obtention (requêtes SQL)
    // ----------------------------------------------------------------
    private function meetsCondition_first_challenge(int $uid): bool {
        $s = $this->conn->prepare("SELECT COUNT(*) FROM challenges WHERE user_id = :uid");
        $s->bindParam(':uid', $uid, PDO::PARAM_INT);
        $s->execute();
        return (int)$s->fetchColumn() >= 1;
    }

    private function meetsCondition_first_submission(int $uid): bool {
        $s = $this->conn->prepare("SELECT COUNT(*) FROM submissions WHERE user_id = :uid");
        $s->bindParam(':uid', $uid, PDO::PARAM_INT);
        $s->execute();
        return (int)$s->fetchColumn() >= 1;
    }

    private function meetsCondition_popular_submission(int $uid): bool {
        $s = $this->conn->prepare(
            "SELECT COUNT(*) FROM votes v
             JOIN submissions s ON v.submission_id = s.id
             WHERE s.user_id = :uid
             GROUP BY v.submission_id
             HAVING COUNT(*) >= 10
             LIMIT 1"
        );
        $s->bindParam(':uid', $uid, PDO::PARAM_INT);
        $s->execute();
        return $s->rowCount() > 0;
    }

    private function meetsCondition_active_commenter(int $uid): bool {
        $s = $this->conn->prepare("SELECT COUNT(*) FROM comments WHERE user_id = :uid");
        $s->bindParam(':uid', $uid, PDO::PARAM_INT);
        $s->execute();
        return (int)$s->fetchColumn() >= 5;
    }

    private function meetsCondition_challenge_creator_5(int $uid): bool {
        $s = $this->conn->prepare("SELECT COUNT(*) FROM challenges WHERE user_id = :uid");
        $s->bindParam(':uid', $uid, PDO::PARAM_INT);
        $s->execute();
        return (int)$s->fetchColumn() >= 5;
    }

    private function meetsCondition_top_voter(int $uid): bool {
        $s = $this->conn->prepare("SELECT COUNT(*) FROM votes WHERE user_id = :uid");
        $s->bindParam(':uid', $uid, PDO::PARAM_INT);
        $s->execute();
        return (int)$s->fetchColumn() >= 10;
    }
}
