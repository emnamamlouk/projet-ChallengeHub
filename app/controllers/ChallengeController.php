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
            // SUPPRIMÉ : elseif(strlen($title) < 5)
            
            if(empty($description)) {
                $errors[] = "La description est requise";
            }
            // SUPPRIMÉ : elseif(strlen($description) < 20)
            
            if(empty($category)) {
                $errors[] = "La catégorie est requise";
            }
            
            if(!empty($deadline)) {
                $deadline_timestamp = strtotime($deadline);
                if($deadline_timestamp === false) {
                    $errors[] = "Format de date invalide";
                } elseif($deadline_timestamp < time()) {
                    $errors[] = "La date limite doit être dans le futur";
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
                    // ── BONUS : Vérifier et attribuer les badges ──
                    require_once APP_PATH . '/models/Badge.php';
                    $badgeModel = new Badge();
                    $badgeModel->checkAndAward((int)$_SESSION['user_id']);

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
            // SUPPRIMÉ : elseif(strlen($title) < 5)
            
            if(empty($description)) {
                $errors[] = "La description est requise";
            }
            // SUPPRIMÉ : elseif(strlen($description) < 20)
            
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

            // ── BONUS : badges + notification au propriétaire de la participation ──
            if (!empty($result['success'])) {
                require_once APP_PATH . '/models/Badge.php';
                require_once APP_PATH . '/models/Notification.php';
                $badgeModel = new Badge();
                $badgeModel->checkAndAward((int)$_SESSION['user_id']);

                // Récupérer le propriétaire de la participation
                $sub = $this->submissionModel->getSubmissionById($submission_id);
                if ($sub && (int)$sub['user_id'] !== (int)$_SESSION['user_id']) {
                    $notifModel = new Notification();
                    $notifModel->create(
                        (int)$sub['user_id'],
                        'new_vote',
                        htmlspecialchars($_SESSION['username']) . ' a voté pour votre participation.',
                        (int)$submission_id
                    );
                }
            }

            echo json_encode($result);
            exit();
        }
    }
    
    public function ranking() {
        $category = $_GET['category'] ?? 'Design';
        $sort = $_GET['sort'] ?? 'votes';
        
        if($category == 'all') {
            header('Location: index.php?action=ranking&category=Design&sort=' . $sort);
            exit();
        }
        
        // Récupérer TOUS les défis de la catégorie (pas de limite ici)
        $challenges = $this->challengeModel->getAllChallengesSorted('recent', $category, '');
        
        // Ajouter les compteurs pour chaque défi
        foreach($challenges as &$challenge) {
            $challenge['likes_count'] = $this->challengeModel->getLikesCount($challenge['id']);
            $challenge['submissions_count'] = $this->challengeModel->countSubmissions($challenge['id']);
        }
        
        // TRI avec gestion des ex-aequo
        if($sort == 'votes') {
            usort($challenges, function($a, $b) {
                // 1. Par nombre de likes
                if($b['likes_count'] != $a['likes_count']) {
                    return $b['likes_count'] - $a['likes_count'];
                }
                // 2. En cas d'égalité, le plus récent en premier
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        } else {
            usort($challenges, function($a, $b) {
                // 1. Par nombre de participations
                if($b['submissions_count'] != $a['submissions_count']) {
                    return $b['submissions_count'] - $a['submissions_count'];
                }
                // 2. En cas d'égalité, le plus récent en premier
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }
        
        // === NE PAS FILTRER ICI === On garde TOUS les défis pour la vue
        // La vue décidera combien afficher (top 10)
        $topChallenges = $challenges; // Tous les défis triés
        
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

    public function addChallengeComment() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'redirect' => 'index.php?action=showLogin']);
            exit();
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $challenge_id = $_POST['challenge_id'] ?? 0;
            $content = trim($_POST['content'] ?? '');
            $result = $this->challengeModel->addChallengeComment($_SESSION['user_id'], $challenge_id, $content);
            if($result) {
                echo json_encode([
                    'success' => true,
                    'username' => $_SESSION['username'],
                    'content' => htmlspecialchars($content)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur']);
            }
        }
        exit();
    }

    public function getChallengeComments() {
        header('Content-Type: application/json');
        $challenge_id = $_GET['challenge_id'] ?? 0;
        if(!$challenge_id) { 
            echo json_encode(['success' => false]); 
            exit(); 
        }
        $comments = $this->challengeModel->getChallengeComments($challenge_id);
        echo json_encode(['success' => true, 'comments' => $comments]);
        exit();
    }

    public function deleteChallengeComment() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            exit();
        }
        $comment_id   = intval($_POST['comment_id']   ?? 0);
        $challenge_id = intval($_POST['challenge_id'] ?? 0);
        $result = $this->challengeModel->deleteChallengeComment($comment_id, $_SESSION['user_id']);
        echo json_encode(['success' => (bool)$result]);
        exit();
    }

}
?>