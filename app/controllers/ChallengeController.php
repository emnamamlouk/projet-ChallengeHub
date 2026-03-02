<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!class_exists('CSRF') && defined('ROOT_PATH')) require_once ROOT_PATH . '/app/helpers/CSRF.php';

require_once __DIR__ . '/../models/Challenge.php';
require_once __DIR__ . '/../models/Submission.php';
require_once __DIR__ . '/../models/Vote.php';

class ChallengeController {
    
    private $challengeModel;
    private $submissionModel;
    private $voteModel;
    
    public function __construct() {
        $this->challengeModel = new Challenge();
        $this->submissionModel = new Submission();
        $this->voteModel = new Vote();
    }
    
    public function home() {
        $category = $_GET['category'] ?? 'all';
        $search   = $_GET['search']   ?? '';
        $sort     = $_GET['sort']     ?? 'recent';

        // getAllChallengesSorted trie directement en SQL par likes ou participations
        $challenges = $this->challengeModel->getAllChallengesSorted($sort, $category, $search);
        $categories = $this->challengeModel->getCategories();

        // Ajouter les infos user (liked, participated) pour chaque défi
        foreach ($challenges as &$challenge) {
            $challenge['user_liked']        = false;
            $challenge['user_participated'] = false;

            if (isset($_SESSION['user_id'])) {
                $challenge['user_liked']        = $this->challengeModel->hasLiked($_SESSION['user_id'], $challenge['id']);
                $challenge['user_participated'] = $this->submissionModel->hasUserParticipated($challenge['id'], $_SESSION['user_id']);
            }
        }
        unset($challenge);

        require_once __DIR__ . '/../views/challenges/home.php';
    }
    
    public function show() {
        if(!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "Défi non trouvé";
            header('Location: index.php');
            exit();
        }
        
        $challenge_id = $_GET['id'];
        $challenge = $this->challengeModel->getChallengeById($challenge_id);
        
        if(!$challenge) {
            $_SESSION['error'] = "Ce défi n'existe pas";
            header('Location: index.php');
            exit();
        }
        
        $submissions = $this->submissionModel->getSubmissionsByChallenge($challenge_id);
        
        foreach($submissions as &$submission) {
            $submission['votes_count'] = $this->voteModel->getVoteCount($submission['id']);
            
            if(isset($_SESSION['user_id'])) {
                $submission['user_voted'] = $this->voteModel->hasVoted(
                    $_SESSION['user_id'], 
                    $submission['id']
                );
            } else {
                $submission['user_voted'] = false;
            }
        }
        
        $user_participated = false;
        if(isset($_SESSION['user_id'])) {
            $user_participated = $this->submissionModel->hasUserParticipated(
                $challenge_id,
                $_SESSION['user_id']
            );
        }
        
        $is_open = $this->challengeModel->isOpen($challenge_id);

        // Charger les commentaires des participations pour la vue
        require_once __DIR__ . '/../models/Comment.php';
        $commentModel = new Comment();
        $comments = [];
        foreach($submissions as $sub) {
            $comments[$sub['id']] = $commentModel->getCommentsBySubmission($sub['id']);
        }
        
        require_once __DIR__ . '/../views/challenges/show.php';
    }
    
    public function createForm() {
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour créer un défi";
            header('Location: index.php?action=showLogin');
            exit();
        }
        require_once __DIR__ . '/../views/challenges/create.php';
    }
    
    public function create() {
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {


            
            $title = trim(htmlspecialchars($_POST['title']));
            $description = trim(htmlspecialchars($_POST['description']));
            $category = trim(htmlspecialchars($_POST['category']));
            $deadline = $_POST['deadline'] ?? null;
            
            $errors = [];
            
            if(empty($title)) {
                $errors[] = "Le titre est requis";
            }
            
            if(empty($description)) {
                $errors[] = "La description est requise";
            }
            
            if(empty($category)) {
                $errors[] = "La catégorie est requise";
            }
            
            if(!empty($deadline)) {
                $deadline_timestamp = strtotime($deadline);
                if($deadline_timestamp === false) {
                    $errors[] = "Format de date invalide";
                }
            }
            
            $image = null;
            if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['image']['type'];
                
                if(in_array($file_type, $allowed_types)) {
                    
                    if($_FILES['image']['size'] <= 5 * 1024 * 1024) {
                        
                        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $image_name = 'challenge_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        $upload_dir = __DIR__ . '/../../public/uploads/challenges/';
                        
                        if(!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $destination = $upload_dir . $image_name;
                        
                        if(move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $image = 'uploads/challenges/' . $image_name;
                        } else {
                            $errors[] = "Erreur lors de l'upload de l'image";
                        }
                        
                    } else {
                        $errors[] = "L'image ne doit pas dépasser 5Mo";
                    }
                } else {
                    $errors[] = "Format d'image non autorisé (JPEG, PNG, GIF, WEBP)";
                }
            }
            
            if(empty($errors)) {
                
                $challenge_id = $this->challengeModel->create(
                    $_SESSION['user_id'],
                    $title,
                    $description,
                    $category,
                    $deadline,
                    $image
                );
                
                if($challenge_id) {
                    $_SESSION['success'] = "Défi créé avec succès !";
                    header('Location: index.php?action=showChallenge&id=' . $challenge_id);
                    exit();
                } else {
                    $errors[] = "Erreur lors de la création du défi";
                }
            }
            
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = [
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'deadline' => $deadline
            ];
            
            header('Location: index.php?action=createChallengeForm');
            exit();
        }
    }
    
    public function editForm() {
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if(!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "Défi non trouvé";
            header('Location: index.php');
            exit();
        }
        
        $challenge_id = $_GET['id'];
        
        if(!$this->challengeModel->isOwner($challenge_id, $_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à modifier ce défi";
            header('Location: index.php?action=showChallenge&id=' . $challenge_id);
            exit();
        }
        
        $challenge = $this->challengeModel->getChallengeById($challenge_id);
        
        if(!$challenge) {
            $_SESSION['error'] = "Ce défi n'existe pas";
            header('Location: index.php');
            exit();
        }
        
        require_once __DIR__ . '/../views/challenges/edit.php';
    }
    
    public function update() {
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {


            
            $challenge_id = $_POST['challenge_id'] ?? 0;
            
            if(!$this->challengeModel->isOwner($challenge_id, $_SESSION['user_id'])) {
                $_SESSION['error'] = "Action non autorisée";
                header('Location: index.php');
                exit();
            }
            
            $title = trim(htmlspecialchars($_POST['title']));
            $description = trim(htmlspecialchars($_POST['description']));
            $category = trim(htmlspecialchars($_POST['category']));
            $deadline = $_POST['deadline'] ?? null;
            
            $errors = [];
            
            if(empty($title)) {
                $errors[] = "Le titre est requis";
            }
            
            if(empty($description)) {
                $errors[] = "La description est requise";
            }
            
            if(empty($category)) {
                $errors[] = "La catégorie est requise";
            }
            
            $image = null;
            if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['image']['type'];
                
                if(in_array($file_type, $allowed_types)) {
                    
                    if($_FILES['image']['size'] <= 5 * 1024 * 1024) {
                        
                        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $image_name = 'challenge_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        $upload_dir = __DIR__ . '/../../public/uploads/challenges/';
                        
                        if(!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $destination = $upload_dir . $image_name;
                        
                        if(move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $image = 'uploads/challenges/' . $image_name;
                        } else {
                            $errors[] = "Erreur lors de l'upload de l'image";
                        }
                        
                    } else {
                        $errors[] = "L'image ne doit pas dépasser 5Mo";
                    }
                } else {
                    $errors[] = "Format d'image non autorisé";
                }
            }
            
            if(empty($errors)) {
                
                $result = $this->challengeModel->update(
                    $challenge_id,
                    $_SESSION['user_id'],
                    $title,
                    $description,
                    $category,
                    $deadline,
                    $image
                );
                
                if($result) {
                    $_SESSION['success'] = "Défi mis à jour avec succès !";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour";
                }
                
                header('Location: index.php?action=showChallenge&id=' . $challenge_id);
                exit();
            }
            
            $_SESSION['errors'] = $errors;
            header('Location: index.php?action=editChallengeForm&id=' . $challenge_id);
            exit();
        }
    }
    
    public function delete() {
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {


            
            $challenge_id = $_POST['challenge_id'] ?? 0;
            
            if(!$this->challengeModel->isOwner($challenge_id, $_SESSION['user_id'])) {
                $_SESSION['error'] = "Action non autorisée";
                header('Location: index.php');
                exit();
            }
            
            if($this->challengeModel->delete($challenge_id, $_SESSION['user_id'])) {
                $_SESSION['success'] = "Défi supprimé avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression";
            }
            
            header('Location: index.php');
            exit();
        }
    }
    
    public function vote() {
        header('Content-Type: application/json');
        
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $submission_id = $_POST['submission_id'] ?? 0;
            $result = $this->voteModel->toggleVote($_SESSION['user_id'], $submission_id);
            
            echo json_encode($result);
            exit();
        }
    }
    
    public function ranking() {
        $category = $_GET['category'] ?? 'all';
        $limit = $_GET['limit'] ?? 20;
        
        $ranking = $this->submissionModel->getRanking($category, $limit);
        $categories = $this->challengeModel->getCategories();
        
        require_once __DIR__ . '/../views/challenges/ranking.php';
    }

    public function toggleLike() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'redirect' => 'index.php?action=showLogin']);
            exit();
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
$challenge_id = $_POST['challenge_id'] ?? 0;
            $result = $this->challengeModel->toggleLike($_SESSION['user_id'], $challenge_id);
            echo json_encode(['success' => true, 'action' => $result['action'], 'count' => $result['count']]);
        }
        exit();
    }


    public function getChallengeComments() {
        header('Content-Type: application/json');
        $challenge_id = $_GET['challenge_id'] ?? 0;
        if(!$challenge_id) { echo json_encode(['success' => false]); exit(); }
        $comments = $this->challengeModel->getChallengeComments($challenge_id);
        echo json_encode(['success' => true, 'comments' => $comments]);
        exit();
    }

    public function addChallengeComment() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'redirect' => 'index.php?action=showLogin']);
            exit();
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $challenge_id = $_POST['challenge_id'] ?? 0;
            $content = trim($_POST['content'] ?? '');
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $result = $this->challengeModel->addChallengeComment($_SESSION['user_id'], $challenge_id, $content, $parent_id);
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
                echo json_encode(['success' => false, 'message' => 'Erreur']);
            }
        }
        exit();
    }

    public function deleteChallengeComment() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            exit();
        }
        $comment_id = $_POST['comment_id'] ?? 0;
        if($this->challengeModel->deleteChallengeComment($comment_id, $_SESSION['user_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur suppression']);
        }
        exit();
    }

}
?>