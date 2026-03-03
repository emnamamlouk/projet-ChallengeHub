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
        
        // Charger les modèles nécessaires
        require_once __DIR__ . '/../models/Challenge.php';
        require_once __DIR__ . '/../models/Submission.php';
        require_once __DIR__ . '/../models/Vote.php';
        require_once __DIR__ . '/../models/Comment.php';
        
        $challengeModel  = new Challenge();
        $submissionModel = new Submission();
        $voteModel       = new Vote();
        $commentModel    = new Comment();
        
        // Récupérer les défis de l'utilisateur
        $userChallenges = $challengeModel->getChallengesByUser($userId);
        
        // Récupérer les participations de l'utilisateur
        $rawSubmissions = $submissionModel->getSubmissionsByUser($userId);
        
        // Enrichir chaque participation avec les votes, commentaires, votants
        $userSubmissions = [];
        $totalVotesReceived = 0;
        
        foreach($rawSubmissions as $sub) {
            // Compter les votes reçus
            $votesCount = $voteModel->getVoteCount($sub['id']);
            $totalVotesReceived += $votesCount;
            
            // Récupérer les votants
            $voters = $voteModel->getVotersForSubmission($sub['id']);
            
            // Récupérer les commentaires avec réponses
            $comments = $commentModel->getCommentsBySubmission($sub['id']);
            $commentsCount = count($comments);
            
            // Vérifier si l'utilisateur connecté a voté
            $userVoted = false;
            if(isset($_SESSION['user_id'])) {
                $userVoted = $voteModel->hasVoted($_SESSION['user_id'], $sub['id']);
            }
            
            // Ajouter toutes les infos à la participation
            $sub['votes_count'] = $votesCount;
            $sub['comments_count'] = $commentsCount;
            $sub['voters'] = $voters;
            $sub['comments'] = $comments;
            $sub['user_voted'] = $userVoted;
            
            $userSubmissions[] = $sub;
        }
        
        // Passer les variables à la vue
        require_once __DIR__ . '/../views/users/public_profile.php';
    }
}
?>