<?php
if(session_status() === PHP_SESSION_NONE) session_start();
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

        require_once __DIR__ . '/../models/Challenge.php';
        require_once __DIR__ . '/../models/Submission.php';
        require_once __DIR__ . '/../models/Vote.php';
        require_once __DIR__ . '/../models/Comment.php';

        $challengeModel  = new Challenge();
        $submissionModel = new Submission();
        $voteModel       = new Vote();
        $commentModel    = new Comment();

        $userChallenges  = $challengeModel->getChallengesByUser($userId);
        $rawSubmissions  = $submissionModel->getSubmissionsByUser($userId);

        // Enrichir chaque participation : votes réels, commentaires réels, qui a voté
        $userSubmissions = [];
        foreach($rawSubmissions as $sub) {
            $sub['votes_count']    = $voteModel->getVoteCount($sub['id']);
            $sub['comments_count'] = $commentModel->countComments($sub['id']);
            $sub['voters']         = $voteModel->getVotersForSubmission($sub['id']);
            $sub['comments']       = $commentModel->getCommentsBySubmission($sub['id']);
            $sub['user_voted']     = isset($_SESSION['user_id'])
                                     ? $voteModel->hasVoted($_SESSION['user_id'], $sub['id'])
                                     : false;
            $userSubmissions[] = $sub;
        }

        // Stats globales
        $totalVotesReceived = array_sum(array_column($userSubmissions, 'votes_count'));

        require_once __DIR__ . '/../views/users/public_profile.php';
    }
}
?>