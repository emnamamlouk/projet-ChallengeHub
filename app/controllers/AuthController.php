<?php
// app/controllers/AuthController.php

// On démarre la session pour pouvoir stocker des informations utilisateur
// session_start() doit être appelé avant tout affichage HTML
// Note: si session_start() est déjà appelé dans index.php, on peut l'enlever d'ici
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!class_exists('CSRF') && defined('ROOT_PATH')) require_once ROOT_PATH . '/app/helpers/CSRF.php';

// On inclut les modèles nécessaires
require_once __DIR__ . '/../models/User.php';

// DÉFINITION DE LA CLASSE AUTHCONTROLLER
// Cette classe gère toutes les actions liées à l'authentification
class AuthController {
    
    // Propriété privée pour stocker l'objet User
    private $userModel;
    
    // CONSTRUCTEUR
    public function __construct() {
        // Crée une instance du modèle User
        // Cette instance sera utilisée par toutes les méthodes du contrôleur
        $this->userModel = new User();
    }
    
    // MÉTHODE POUR AFFICHER LA PAGE D'INSCRIPTION
    public function showRegister() {
        require_once __DIR__ . '/../views/auth/register.php';
    }
    // MÉTHODE POUR TRAITER L'INSCRIPTION
    // Cette méthode est appelée quand le formulaire d'inscription est soumis
    public function register() {
        
        // Vérifier si le formulaire a été soumis (méthode POST)
        if($_SERVER['REQUEST_METHOD'] === 'POST') {



            
            // Récupérer et nettoyer les données du formulaire
            // trim() enlève les espaces au début et à la fin
            // htmlspecialchars() convertit les caractères spéciaux en entités HTML (protection XSS)
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password']; // On ne fait pas htmlspecialchars sur le mot de passe
            $confirm_password = $_POST['confirm_password'];
            
            // TABLEAU POUR STOCKER LES ERREURS
            $errors = [];
            
            // VALIDATION DES DONNÉES
            // Vérifier que tous les champs sont remplis
            if(empty($username)) {
                $errors[] = "Le nom d'utilisateur est requis";
            } elseif(strlen($username) < 3) {
                $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
            } elseif(strlen($username) > 50) {
                $errors[] = "Le nom d'utilisateur est trop long (max 50 caractères)";
            }
            
            if(empty($email)) {
                $errors[] = "L'email est requis";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // filter_var avec FILTER_VALIDATE_EMAIL vérifie si l'email est valide
                $errors[] = "L'email n'est pas valide";
            }
            
            if(empty($password)) {
                $errors[] = "Le mot de passe est requis";
            } elseif(strlen($password) < 6) {
                $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
            }
            
            if($password !== $confirm_password) {
                $errors[] = "Les mots de passe ne correspondent pas";
            }
            
            // S'il n'y a pas d'erreurs, on procède à l'inscription
            if(empty($errors)) {
                
                // Appeler la méthode register() du modèle User
                $result = $this->userModel->register($username, $email, $password);
                
                if($result === true) {
                    // Inscription réussie
                    // On peut automatiquement connecter l'utilisateur ou le rediriger vers login
                    $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                    
                    // Redirection vers la page de connexion
                    header('Location: index.php?action=login');
                    exit();
                    
                } else {
                    // Erreur d'inscription (email ou username déjà utilisé)
                    $errors[] = $result; // $result contient le message d'erreur
                }
            }
            
            // S'il y a des erreurs, on les stocke en session pour les afficher dans la vue
            $_SESSION['errors'] = $errors;
            
            // On pré-remplit le formulaire avec les données saisies (sauf le mot de passe)
            $_SESSION['old_input'] = [
                'username' => $username,
                'email' => $email
            ];
            
            // Redirection vers la page d'inscription pour afficher les erreurs
            header('Location: index.php?action=showRegister');
            exit();
            
        } else {
            // Si quelqu'un essaie d'accéder directement à cette méthode sans formulaire POST
            header('Location: index.php?action=showRegister');
            exit();
        }
    }
    
    // MÉTHODE POUR AFFICHER LA PAGE DE CONNEXION
    public function showLogin() {
        // Inclut la vue de connexion
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    // MÉTHODE POUR TRAITER LA CONNEXION
    public function login() {
        
        // Vérifier si le formulaire a été soumis
        if($_SERVER['REQUEST_METHOD'] === 'POST') {



            
            // Récupérer et nettoyer les données
            $email = trim(htmlspecialchars($_POST['email']));
            $password = $_POST['password'];
            
            $errors = [];
            
            // Validation
            if(empty($email)) {
                $errors[] = "L'email est requis";
            }
            
            if(empty($password)) {
                $errors[] = "Le mot de passe est requis";
            }
            
            if(empty($errors)) {
                
                // Tenter la connexion
                $result = $this->userModel->login($email, $password);
                
                if($result === true) {
                    // Connexion réussie
                    
                    // Récupérer les infos de l'utilisateur connecté
                    $_SESSION['user_id'] = $this->userModel->getId();
                    $_SESSION['username'] = $this->userModel->getUsername();
                    $_SESSION['email'] = $this->userModel->getEmail();
                    $_SESSION['avatar'] = $this->userModel->getAvatar();
                    $_SESSION['bio'] = $this->userModel->getBio();
                    
                    $_SESSION['success'] = "Bienvenue " . $_SESSION['username'] . " !";
                    
                    // Redirection vers la page d'accueil ou le dashboard
                    header('Location: index.php?action=home');
                    exit();
                    
                } else {
                    // Erreur de connexion
                    $errors[] = $result; // Message d'erreur du modèle
                }
            }
            
            // S'il y a des erreurs
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = ['email' => $email];
            
            header('Location: index.php?action=showLogin');
            exit();
            
        } else {
            header('Location: index.php?action=showLogin');
            exit();
        }
    }
    
    // MÉTHODE POUR LA DÉCONNEXION
    public function logout() {
        
        // Détruire toutes les variables de session
        $_SESSION = [];
        
        // Détruire le cookie de session si utilisé
        if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Détruire la session
        session_destroy();
        
        // Redirection vers la page d'accueil
        header('Location: index.php');
        exit();
    }
    
    // MÉTHODE POUR AFFICHER LE PROFIL UTILISATEUR
    public function profile() {
        
        // Vérifier si l'utilisateur est connecté
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour voir votre profil";
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        // Récupérer les informations complètes de l'utilisateur
        $userInfo = $this->userModel->getUserById($_SESSION['user_id']);
        
        // Inclure la vue du profil
        require_once __DIR__ . '/../views/users/profile.php';
    }
    
    // MÉTHODE POUR METTRE À JOUR LE PROFIL
    public function updateProfile() {
        
        // Vérifier si l'utilisateur est connecté
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {



            
            $username = trim($_POST['username']);
            $bio = trim($_POST['bio']);
            
            $errors = [];
            
            // Validation
            if(empty($username)) {
                $errors[] = "Le nom d'utilisateur est requis";
            } elseif(strlen($username) < 3) {
                $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
            }
            
            // Gestion de l'avatar (upload de fichier)
            $avatar = null;
            if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                
                // Vérifier le type de fichier
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['avatar']['type'];
                
                if(in_array($file_type, $allowed_types)) {
                    
                    // Vérifier la taille (max 2Mo)
                    if($_FILES['avatar']['size'] <= 2 * 1024 * 1024) {
                        
                        // Générer un nom unique
                        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                        $avatar_name = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
                        
                        // Dossier de destination
                        $upload_dir = __DIR__ . '/../../public/uploads/avatars/';
                        
                        // Créer le dossier s'il n'existe pas
                        if(!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $destination = $upload_dir . $avatar_name;
                        
                        // Déplacer le fichier uploadé
                        if(move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                            $avatar = 'uploads/avatars/' . $avatar_name;
                        } else {
                            $errors[] = "Erreur lors de l'upload de l'avatar";
                        }
                        
                    } else {
                        $errors[] = "L'avatar ne doit pas dépasser 2Mo";
                    }
                } else {
                    $errors[] = "Format d'image non autorisé (JPEG, PNG, GIF uniquement)";
                }
            }
            
            if(empty($errors)) {
                
                // Mettre à jour le profil
                $result = $this->userModel->updateProfile(
                    $_SESSION['user_id'],
                    $username,
                    $bio,
                    $avatar
                );
                
                if($result) {
                    $_SESSION['success'] = "Profil mis à jour avec succès";
                    $_SESSION['username'] = $username; // Mettre à jour la session
                    if($avatar) {
                        $_SESSION['avatar'] = $avatar;
                    }
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour";
                }
            } else {
                $_SESSION['errors'] = $errors;
            }
            
            header('Location: index.php?action=profile');
            exit();
        }
    }
    
    // MÉTHODE POUR SUPPRIMER LE COMPTE
    public function deleteAccount() {
        
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {



            
            // Vérifier le mot de passe avant suppression
            $password = $_POST['password'];
            $email = $_SESSION['email'];
            
            // Tenter de reconnecter l'utilisateur pour vérifier le mot de passe
            $result = $this->userModel->login($email, $password);
            
            if($result === true) {
                // Mot de passe correct, on supprime le compte
                if($this->userModel->deleteAccount($_SESSION['user_id'])) {
                    
                    // Déconnecter l'utilisateur
                    $this->logout();
                    
                } else {
                    $_SESSION['error'] = "Erreur lors de la suppression du compte";
                    header('Location: index.php?action=profile');
                    exit();
                }
            } else {
                $_SESSION['error'] = "Mot de passe incorrect";
                header('Location: index.php?action=profile');
                exit();
            }
        }
    }
}
// Fin de la classe AuthController
?>