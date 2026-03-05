<?php
// app/controllers/AdminController.php

if(session_status() === PHP_SESSION_NONE) session_start();
if(!class_exists('CSRF') && defined('ROOT_PATH')) require_once ROOT_PATH . '/app/helpers/CSRF.php';

require_once __DIR__ . '/../models/AdminModel.php';

class AdminController {

    private $adminModel;

    public function __construct() {
        $this->checkAdmin();
        $this->adminModel = new AdminModel();
    }

    // ── Vérifie que l'utilisateur est admin ──────────────────
    private function checkAdmin() {
        if(!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
            $_SESSION['error'] = "Accès réservé aux administrateurs.";
            header('Location: index.php?action=home');
            exit();
        }
    }

    // ── Dashboard principal ───────────────────────────────────
    public function dashboard() {
        $stats             = $this->adminModel->getStats();
        $categoryData      = $this->adminModel->getChallengesByCategory();
        $usersPerDay       = $this->adminModel->getUsersPerDay();
        $challengesPerDay  = $this->adminModel->getChallengesPerDay();
        $topUsers          = $this->adminModel->getTopUsers();
        $allUsers          = $this->adminModel->getAllUsers();
        $allChallenges     = $this->adminModel->getAllChallenges();

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // ── Supprimer un utilisateur ──────────────────────────────
    public function deleteUser() {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            $this->adminModel->deleteUser((int)$_POST['user_id']);
            $_SESSION['success'] = "Utilisateur supprimé.";
        }
        header('Location: index.php?action=admin');
        exit();
    }

    // ── Supprimer un défi ─────────────────────────────────────
    public function deleteChallenge() {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['challenge_id'])) {
            $this->adminModel->deleteChallenge((int)$_POST['challenge_id']);
            $_SESSION['success'] = "Défi supprimé.";
        }
        header('Location: index.php?action=admin');
        exit();
    }

    // ── Toggler admin ─────────────────────────────────────────
    public function toggleAdmin() {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            $this->adminModel->toggleAdmin((int)$_POST['user_id']);
            $_SESSION['success'] = "Rôle admin modifié.";
        }
        header('Location: index.php?action=admin');
        exit();
    }
}
?>