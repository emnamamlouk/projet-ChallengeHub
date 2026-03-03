<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!class_exists('CSRF') && defined('ROOT_PATH')) require_once ROOT_PATH . '/app/helpers/CSRF.php';

require_once __DIR__ . '/../models/User.php';

class UserController {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function search() {
        $search = $_GET['q'] ?? '';
        $users = [];
        
        if(!empty($search)) {
            $users = $this->userModel->searchUsers($search);
        }
        
        require_once __DIR__ . '/../views/users/search.php';
    }
    
    public function viewProfile() {
        $userId = $_GET['id'] ?? 0;
        
        if(isset($_SESSION['user_id']) && $userId == $_SESSION['user_id']) {
            header('Location: index.php?action=profile');
            exit();
        }
        
        $user = $this->userModel->getUserById($userId);
        
        if(!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé";
            header('Location: index.php');
            exit();
        }
        
        // Récupérer les défis de l'utilisateur
        require_once __DIR__ . '/../models/Challenge.php';
        $challengeModel = new Challenge();
        $userChallenges = $challengeModel->getChallengesByUser($userId);
        
        require_once __DIR__ . '/../views/users/public_profile.php';
    }
}
?>