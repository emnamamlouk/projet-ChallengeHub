<?php
// app/controllers/CommentController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!class_exists('CSRF') && defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/app/helpers/CSRF.php';
}

require_once __DIR__ . '/../models/Comment.php';

class CommentController {

    private $commentModel;

    public function __construct() {
        $this->commentModel = new Comment();
    }

    // Ajouter un commentaire (AJAX - retourne JSON)
    public function add() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'redirect' => 'index.php?action=showLogin']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode invalide']);
            exit();
        }

        $submission_id = intval($_POST['submission_id'] ?? 0);
        $content       = trim($_POST['content'] ?? '');
        $parent_id     = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? intval($_POST['parent_id']) : null;

        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Commentaire vide']);
            exit();
        }

        if (strlen($content) > 500) {
            echo json_encode(['success' => false, 'message' => 'Commentaire trop long (max 500 caractères)']);
            exit();
        }

        $comment_id = $this->commentModel->addComment(
            $_SESSION['user_id'],
            $submission_id,
            $content,
            $parent_id
        );

        if ($comment_id) {
            // ── BONUS : badges commentateur actif + notification propriétaire ──
            require_once APP_PATH . '/models/Badge.php';
            require_once APP_PATH . '/models/Notification.php';
            require_once APP_PATH . '/models/Submission.php';
            $badgeModel = new Badge();
            $badgeModel->checkAndAward((int)$_SESSION['user_id']);

            $submissionModel = new Submission();
            $sub = $submissionModel->getSubmissionById($submission_id);
            if ($sub && (int)$sub['user_id'] !== (int)$_SESSION['user_id']) {
                $notifModel = new Notification();
                $notifModel->create(
                    (int)$sub['user_id'],
                    'new_comment',
                    htmlspecialchars($_SESSION['username']) . ' a commenté votre participation.',
                    (int)$submission_id
                );
            }

            $total = $this->commentModel->countComments($submission_id);
            echo json_encode([
                'success'      => true,
                'comment_id'   => $comment_id,
                'username'     => $_SESSION['username'],
                'content'      => htmlspecialchars($content),
                'total'        => $total,
                'is_reply'     => $parent_id !== null,
                'parent_id'    => $parent_id,
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout']);
        }
        exit();
    }

    // Modifier un commentaire (AJAX - retourne JSON)
    public function update() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode invalide']);
            exit();
        }

        $comment_id = intval($_POST['comment_id'] ?? 0);
        $content    = trim($_POST['content'] ?? '');

        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Commentaire vide']);
            exit();
        }

        if (strlen($content) > 500) {
            echo json_encode(['success' => false, 'message' => 'Trop long (max 500 caractères)']);
            exit();
        }

        $result = $this->commentModel->updateComment($comment_id, $_SESSION['user_id'], $content);

        if ($result) {
            echo json_encode(['success' => true, 'content' => htmlspecialchars($content)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur ou non autorisé']);
        }
        exit();
    }

    // Supprimer un commentaire (AJAX - retourne JSON)
    public function delete() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            exit();
        }

        $comment_id    = intval($_POST['comment_id'] ?? 0);
        $submission_id = intval($_POST['submission_id'] ?? 0);

        $result = $this->commentModel->deleteComment($comment_id, $_SESSION['user_id']);

        if ($result) {
            $total = $this->commentModel->countComments($submission_id);
            echo json_encode(['success' => true, 'total' => $total]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur ou non autorisé']);
        }
        exit();
    }

    // Récupérer les commentaires (AJAX - retourne JSON)
    public function getComments() {
        header('Content-Type: application/json');

        $submission_id = intval($_GET['submission_id'] ?? 0);
        $comments      = $this->commentModel->getCommentsBySubmission($submission_id);

        echo json_encode(['success' => true, 'comments' => $comments]);
        exit();
    }

    // Compter les commentaires (AJAX - retourne JSON)
    public function countComments() {
        header('Content-Type: application/json');

        $submission_id = intval($_GET['submission_id'] ?? 0);
        $total         = $this->commentModel->countComments($submission_id);

        echo json_encode(['success' => true, 'total' => $total]);
        exit();
    }
}
?> 