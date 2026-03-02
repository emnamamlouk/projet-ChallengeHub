<?php
if(session_status() === PHP_SESSION_NONE) session_start();
if(!class_exists('CSRF') && defined('ROOT_PATH')) require_once ROOT_PATH . '/app/helpers/CSRF.php';
require_once __DIR__ . '/../models/Comment.php';

class CommentController {
    private $commentModel;

    public function __construct() {
        $this->commentModel = new Comment();
    }

    public function add() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
            exit();
        }
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        $submission_id = $_POST['submission_id'] ?? 0;
        $content = trim($_POST['content'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        if(empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Le commentaire ne peut pas être vide']);
            exit();
        }
        $result = $this->commentModel->addComment($_SESSION['user_id'], $submission_id, $content, $parent_id);
        if($result) {
            echo json_encode([
                'success' => true,
                'comment_id' => $result,
                'username' => $_SESSION['username'],
                'content' => htmlspecialchars($content),
                'created_at' => date('Y-m-d H:i:s'),
                'parent_id' => $parent_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du commentaire']);
        }
        exit();
    }

    public function delete() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            exit();
        }
        $comment_id = $_POST['comment_id'] ?? 0;
        if($this->commentModel->deleteComment($comment_id, $_SESSION['user_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur suppression']);
        }
        exit();
    }

    public function getComments() {
        header('Content-Type: application/json');
        $submission_id = $_GET['submission_id'] ?? 0;
        if(!$submission_id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit();
        }
        $comments = $this->commentModel->getCommentsBySubmission($submission_id);
        echo json_encode(['success' => true, 'comments' => $comments]);
        exit();
    }

    public function countComments() {
        header('Content-Type: application/json');
        $submission_id = $_GET['submission_id'] ?? 0;
        if(!$submission_id) {
            echo json_encode(['success' => false, 'count' => 0]);
            exit();
        }
        $count = $this->commentModel->countComments($submission_id);
        echo json_encode(['success' => true, 'count' => $count]);
        exit();
    }
}
?>