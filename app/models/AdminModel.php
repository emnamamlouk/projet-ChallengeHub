<?php
// app/models/AdminModel.php

require_once __DIR__ . '/../../config/Database.php';

class AdminModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── KPIs globaux ──────────────────────────────────────────
    public function getStats() {
        $stats = [];

        $stats['total_users']       = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['total_challenges']  = $this->db->query("SELECT COUNT(*) FROM challenges")->fetchColumn();
        $stats['total_submissions'] = $this->db->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
        $stats['total_votes']       = $this->db->query("SELECT COUNT(*) FROM challenge_likes")->fetchColumn();
        $stats['total_comments']    = $this->db->query("SELECT COUNT(*) FROM challenge_comments")->fetchColumn();

        // Nouveaux cette semaine
        $stats['new_users_week'] = $this->db->query(
            "SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        $stats['new_challenges_week'] = $this->db->query(
            "SELECT COUNT(*) FROM challenges WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        return $stats;
    }

    // ── Défis par catégorie (pour camembert) ──────────────────
    public function getChallengesByCategory() {
        return $this->db->query(
            "SELECT category, COUNT(*) as total 
             FROM challenges 
             GROUP BY category 
             ORDER BY total DESC"
        )->fetchAll();
    }

    // ── Inscriptions par jour (30 derniers jours) ─────────────
    public function getUsersPerDay() {
        return $this->db->query(
            "SELECT DATE(created_at) as day, COUNT(*) as total
             FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC"
        )->fetchAll();
    }

    // ── Défis par jour (30 derniers jours) ───────────────────
    public function getChallengesPerDay() {
        return $this->db->query(
            "SELECT DATE(created_at) as day, COUNT(*) as total
             FROM challenges
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC"
        )->fetchAll();
    }

    // ── Top utilisateurs (par défis créés) ───────────────────
    public function getTopUsers() {
        return $this->db->query(
            "SELECT u.id, u.username, u.email, u.created_at, u.is_admin,
                    COUNT(DISTINCT c.id) as nb_challenges,
                    COUNT(DISTINCT s.id) as nb_submissions
             FROM users u
             LEFT JOIN challenges c ON c.user_id = u.id
             LEFT JOIN submissions s ON s.user_id = u.id
             GROUP BY u.id
             ORDER BY nb_challenges DESC
             LIMIT 10"
        )->fetchAll();
    }

    // ── Tous les utilisateurs ─────────────────────────────────
    public function getAllUsers() {
        return $this->db->query(
            "SELECT u.id, u.username, u.email, u.is_admin, u.created_at,
                    COUNT(DISTINCT c.id) as nb_challenges,
                    COUNT(DISTINCT s.id) as nb_submissions
             FROM users u
             LEFT JOIN challenges c ON c.user_id = u.id
             LEFT JOIN submissions s ON s.user_id = u.id
             GROUP BY u.id
             ORDER BY u.created_at DESC"
        )->fetchAll();
    }

    // ── Tous les défis ────────────────────────────────────────
    public function getAllChallenges() {
        return $this->db->query(
            "SELECT c.id, c.title, c.category, c.created_at,
                    u.username as creator,
                    COUNT(DISTINCT cl.id) as nb_likes,
                    COUNT(DISTINCT s.id)  as nb_submissions
             FROM challenges c
             JOIN users u ON u.id = c.user_id
             LEFT JOIN challenge_likes cl ON cl.challenge_id = c.id
             LEFT JOIN submissions s ON s.challenge_id = c.id
             GROUP BY c.id
             ORDER BY c.created_at DESC"
        )->fetchAll();
    }

    // ── Supprimer un utilisateur ──────────────────────────────
    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
        return $stmt->execute([$id]);
    }

    // ── Supprimer un défi ─────────────────────────────────────
    public function deleteChallenge($id) {
        $stmt = $this->db->prepare("DELETE FROM challenges WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ── Toggler admin ─────────────────────────────────────────
    public function toggleAdmin($id) {
        $stmt = $this->db->prepare(
            "UPDATE users SET is_admin = IF(is_admin=1, 0, 1) WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }
}
?>
