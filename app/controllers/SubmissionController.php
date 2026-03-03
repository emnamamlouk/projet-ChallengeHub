<?php
// app/controllers/SubmissionController.php

// On démarre la session si ce n'est pas déjà fait
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!class_exists('CSRF') && defined('ROOT_PATH')) require_once ROOT_PATH . '/app/helpers/CSRF.php';

// On inclut les modèles nécessaires
require_once __DIR__ . '/../models/Submission.php';
require_once __DIR__ . '/../models/Challenge.php';
require_once __DIR__ . '/../models/Vote.php';
require_once __DIR__ . '/../models/Comment.php';

// DÉFINITION DE LA CLASSE SUBMISSIONCONTROLLER
// Cette classe gère toutes les actions liées aux participations aux défis
class SubmissionController {
    
    // Propriétés privées pour stocker les modèles
    private $submissionModel;
    private $challengeModel;
    private $voteModel;
    private $commentModel;
    
    // CONSTRUCTEUR
    public function __construct() {
        // Initialise les modèles nécessaires
        $this->submissionModel = new Submission();
        $this->challengeModel = new Challenge();
        $this->voteModel = new Vote();
        $this->commentModel = new Comment();
    }
    
    // MÉTHODE POUR AFFICHER UNE PARTICIPATION EN DÉTAIL
    public function show() {
        
        // Vérifier si un ID est fourni
        if(!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "Participation non trouvée";
            header('Location: index.php');
            exit();
        }
        
        $submission_id = $_GET['id'];
        
        // Récupérer les informations de la participation
        $submission = $this->submissionModel->getSubmissionById($submission_id);
        
        if(!$submission) {
            $_SESSION['error'] = "Cette participation n'existe pas";
            header('Location: index.php');
            exit();
        }
        
        // Récupérer le nombre de votes
        $submission['votes_count'] = $this->voteModel->getVoteCount($submission_id);
        
        // Vérifier si l'utilisateur connecté a voté
        if(isset($_SESSION['user_id'])) {
            $submission['user_voted'] = $this->voteModel->hasVoted(
                $_SESSION['user_id'],
                $submission_id
            );
        } else {
            $submission['user_voted'] = false;
        }
        
        // Récupérer les commentaires de cette participation
        $comments = $this->commentModel->getCommentsBySubmission($submission_id);
        
        // Inclure la vue de détail
        require_once __DIR__ . '/../views/submissions/show.php';
    }
    
    // MÉTHODE POUR AFFICHER LE FORMULAIRE DE PARTICIPATION
    public function createForm() {
        
        // Vérifier si l'utilisateur est connecté
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour participer à un défi";
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        // Vérifier si un ID de défi est fourni
        if(!isset($_GET['challenge_id']) || empty($_GET['challenge_id'])) {
            $_SESSION['error'] = "Défi non spécifié";
            header('Location: index.php');
            exit();
        }
        
        $challenge_id = $_GET['challenge_id'];
        
        // Récupérer les informations du défi
        $challenge = $this->challengeModel->getChallengeById($challenge_id);
        
        if(!$challenge) {
            $_SESSION['error'] = "Ce défi n'existe pas";
            header('Location: index.php');
            exit();
        }
        
        // Bloquer le créateur du défi
        if($challenge['user_id'] == $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous ne pouvez pas participer à votre propre défi";
            header('Location: index.php?action=showChallenge&id=' . $challenge_id);
            exit();
        }
        
        // Vérifier si l'utilisateur a déjà participé
        if($this->submissionModel->hasUserParticipated($challenge_id, $_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous avez déjà participé à ce défi";
            header('Location: index.php?action=showChallenge&id=' . $challenge_id);
            exit();
        }
        
        // Vérifier si le défi est toujours ouvert
        if(!$this->challengeModel->isOpen($challenge_id)) {
            $_SESSION['error'] = "Ce défi n'est plus ouvert aux participations";
            header('Location: index.php?action=showChallenge&id=' . $challenge_id);
            exit();
        }
        
        // Inclure la vue du formulaire
        require_once __DIR__ . '/../views/submissions/create.php';
    }
    
    // MÉTHODE POUR TRAITER LA CRÉATION D'UNE PARTICIPATION
    public function create() {
        
        // Vérifier si l'utilisateur est connecté
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {


            
            $challenge_id = $_POST['challenge_id'] ?? 0;
            $description = trim(htmlspecialchars($_POST['description']));
            $link = trim(htmlspecialchars($_POST['link'] ?? ''));
            
            $errors = [];
            
            // Validation
            if(empty($description)) {
                $errors[] = "La description est requise";
            } elseif(strlen($description) < 20) {
                $errors[] = "La description doit contenir au moins 20 caractères";
            } elseif(strlen($description) > 2000) {
                $errors[] = "La description est trop longue (max 2000 caractères)";
            }
            
            // Validation du lien (optionnel)
            if(!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
                $errors[] = "Le lien fourni n'est pas valide";
            }
            
            // Vérifier si l'utilisateur a déjà participé
            if($this->submissionModel->hasUserParticipated($challenge_id, $_SESSION['user_id'])) {
                $errors[] = "Vous avez déjà participé à ce défi";
            }
            
            // Gestion de l'image
            $image = null;
            if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['image']['type'];
                
                if(in_array($file_type, $allowed_types)) {
                    
                    if($_FILES['image']['size'] <= 5 * 1024 * 1024) { // 5 Mo max
                        
                        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $image_name = 'submission_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        $upload_dir = __DIR__ . '/../../public/uploads/submissions/';
                        
                        if(!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $destination = $upload_dir . $image_name;
                        
                        if(move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $image = 'uploads/submissions/' . $image_name;
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
                
                // Créer la participation
                $submission_id = $this->submissionModel->create(
                    $challenge_id,
                    $_SESSION['user_id'],
                    $description,
                    $image,
                    $link
                );
                
                if($submission_id) {
                    $_SESSION['success'] = "Votre participation a été envoyée avec succès !";
                    header('Location: index.php?action=showChallenge&id=' . $challenge_id);
                    exit();
                } else {
                    $errors[] = "Erreur lors de l'envoi de la participation";
                }
            }
            
            // S'il y a des erreurs
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = [
                'description' => $description,
                'link' => $link
            ];
            
            header('Location: index.php?action=createSubmissionForm&challenge_id=' . $challenge_id);
            exit();
        }
    }
    
    // MÉTHODE POUR AFFICHER LE FORMULAIRE DE MODIFICATION
    public function editForm() {
        
        // Vérifier si l'utilisateur est connecté
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if(!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "Participation non trouvée";
            header('Location: index.php');
            exit();
        }
        
        $submission_id = $_GET['id'];
        
        // Vérifier que l'utilisateur est bien le propriétaire
        if(!$this->submissionModel->isOwner($submission_id, $_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à modifier cette participation";
            header('Location: index.php');
            exit();
        }
        
        // Récupérer les informations de la participation
        $submission = $this->submissionModel->getSubmissionById($submission_id);
        
        if(!$submission) {
            $_SESSION['error'] = "Cette participation n'existe pas";
            header('Location: index.php');
            exit();
        }
        
        // Inclure la vue du formulaire
        require_once __DIR__ . '/../views/submissions/edit.php';
    }
    
    // MÉTHODE POUR TRAITER LA MODIFICATION D'UNE PARTICIPATION
    public function update() {
        
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {


            
            $submission_id = $_POST['submission_id'] ?? 0;
            
            // Vérifier les droits
            if(!$this->submissionModel->isOwner($submission_id, $_SESSION['user_id'])) {
                $_SESSION['error'] = "Action non autorisée";
                header('Location: index.php');
                exit();
            }
            
            $description = trim(htmlspecialchars($_POST['description']));
            $link = trim(htmlspecialchars($_POST['link'] ?? ''));
            
            $errors = [];
            
            // Validation
            if(empty($description)) {
                $errors[] = "La description est requise";
            } elseif(strlen($description) < 20) {
                $errors[] = "La description doit contenir au moins 20 caractères";
            } elseif(strlen($description) > 2000) {
                $errors[] = "La description est trop longue";
            }
            
            if(!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
                $errors[] = "Le lien fourni n'est pas valide";
            }
            
            // Gestion de la nouvelle image (si fournie)
            $image = null;
            if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['image']['type'];
                
                if(in_array($file_type, $allowed_types)) {
                    
                    if($_FILES['image']['size'] <= 5 * 1024 * 1024) {
                        
                        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $image_name = 'submission_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        $upload_dir = __DIR__ . '/../../public/uploads/submissions/';
                        
                        if(!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $destination = $upload_dir . $image_name;
                        
                        if(move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $image = 'uploads/submissions/' . $image_name;
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
                
                // Mettre à jour la participation
                $result = $this->submissionModel->update(
                    $submission_id,
                    $_SESSION['user_id'],
                    $description,
                    $image,
                    $link
                );
                
                if($result) {
                    $_SESSION['success'] = "Participation mise à jour avec succès !";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour";
                }
                
                header('Location: index.php?action=showSubmission&id=' . $submission_id);
                exit();
            }
            
            // Erreurs de validation
            $_SESSION['errors'] = $errors;
            header('Location: index.php?action=editSubmissionForm&id=' . $submission_id);
            exit();
        }
    }
    
    // MÉTHODE POUR SUPPRIMER UNE PARTICIPATION
    public function delete() {
        
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {


            
            $submission_id = $_POST['submission_id'] ?? 0;
            
            // Récupérer la participation pour connaître le challenge_id (pour la redirection)
            $submission = $this->submissionModel->getSubmissionById($submission_id);
            
            if(!$submission) {
                $_SESSION['error'] = "Participation non trouvée";
                header('Location: index.php');
                exit();
            }
            
            // Vérifier les droits
            if(!$this->submissionModel->isOwner($submission_id, $_SESSION['user_id'])) {
                $_SESSION['error'] = "Action non autorisée";
                header('Location: index.php');
                exit();
            }
            
            // Supprimer la participation
            if($this->submissionModel->delete($submission_id, $_SESSION['user_id'])) {
                $_SESSION['success'] = "Participation supprimée avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression";
            }
            
            header('Location: index.php?action=showChallenge&id=' . $submission['challenge_id']);
            exit();
        }
    }
}
// Fin de la classe SubmissionController
?>