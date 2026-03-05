<?php
if(session_status() === PHP_SESSION_NONE) session_start();
if(!class_exists('CSRF') && defined('ROOT_PATH')) require_once ROOT_PATH . '/app/helpers/CSRF.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {

    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showRegister() {
        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function register() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username         = trim($_POST['username']);
            $email            = trim($_POST['email']);
            $password         = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $errors = [];

            if(empty($username))              $errors[] = "Le nom d'utilisateur est requis";
            elseif(strlen($username) < 3)     $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
            elseif(strlen($username) > 50)    $errors[] = "Le nom d'utilisateur est trop long (max 50 caractères)";

            if(empty($email))                          $errors[] = "L'email est requis";
            elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email n'est pas valide";

            if(empty($password))              $errors[] = "Le mot de passe est requis";
            elseif(strlen($password) < 6)    $errors[] = "Le mot de passe doit contenir au moins 6 caractères";

            if($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";

            if(empty($errors)) {
                $result = $this->userModel->register($username, $email, $password);
                if($result === true) {
                    $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                    header('Location: index.php?action=login');
                    exit();
                } else {
                    $errors[] = $result;
                }
            }

            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = ['username' => $username, 'email' => $email];
            header('Location: index.php?action=showRegister');
            exit();
        } else {
            header('Location: index.php?action=showRegister');
            exit();
        }
    }

    public function showLogin() {
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim(htmlspecialchars($_POST['email']));
            $password = $_POST['password'];
            $errors   = [];

            if(empty($email))    $errors[] = "L'email est requis";
            if(empty($password)) $errors[] = "Le mot de passe est requis";

            if(empty($errors)) {
                $result = $this->userModel->login($email, $password);
                if($result === true) {
                    $_SESSION['user_id']  = $this->userModel->getId();
                    $_SESSION['username'] = $this->userModel->getUsername();
                    $_SESSION['email']    = $this->userModel->getEmail();
                    $_SESSION['avatar']   = $this->userModel->getAvatar();
                    $_SESSION['bio']      = $this->userModel->getBio();
                    $_SESSION['is_admin'] = $this->userModel->getIsAdmin(); // ← NOUVEAU

                    $_SESSION['success'] = "Bienvenue " . $_SESSION['username'] . " !";
                    header('Location: index.php?action=home');
                    exit();
                } else {
                    $errors[] = $result;
                }
            }

            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = ['email' => $email];
            header('Location: index.php?action=showLogin');
            exit();
        } else {
            header('Location: index.php?action=showLogin');
            exit();
        }
    }

    public function logout() {
        $_SESSION = [];
        if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: index.php');
        exit();
    }

    public function profile() {
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour voir votre profil";
            header('Location: index.php?action=showLogin');
            exit();
        }

        $userInfo = $this->userModel->getUserById($_SESSION['user_id']);

        require_once __DIR__ . '/../models/Challenge.php';
        require_once __DIR__ . '/../models/Submission.php';

        $challengeModel  = new Challenge();
        $submissionModel = new Submission();

        $userId           = $_SESSION['user_id'];
        $userChallenges   = $challengeModel->getChallengesByUser($userId);
        $userSubmissions  = $submissionModel->getSubmissionsByUser($userId);
        $totalChallenges  = count($userChallenges);
        $totalSubmissions = count($userSubmissions);

        require_once __DIR__ . '/../views/users/profile.php';
    }

    public function updateProfile() {
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $bio      = trim($_POST['bio']);
            $errors   = [];

            if(empty($username))           $errors[] = "Le nom d'utilisateur est requis";
            elseif(strlen($username) < 3)  $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";

            $avatar = null;
            if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type     = $_FILES['avatar']['type'];

                if(in_array($file_type, $allowed_types)) {
                    if($_FILES['avatar']['size'] <= 2 * 1024 * 1024) {
                        $extension   = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                        $avatar_name = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
                        $upload_dir  = __DIR__ . '/../../public/uploads/avatars/';
                        if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                        $destination = $upload_dir . $avatar_name;
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
                $result = $this->userModel->updateProfile($_SESSION['user_id'], $username, $bio, $avatar);
                if($result) {
                    $_SESSION['success']  = "Profil mis à jour avec succès";
                    $_SESSION['username'] = $username;
                    if($avatar) $_SESSION['avatar'] = $avatar;
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

    public function deleteAccount() {
        if(!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=showLogin');
            exit();
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $email    = $_SESSION['email'];
            $result   = $this->userModel->login($email, $password);

            if($result === true) {
                if($this->userModel->deleteAccount($_SESSION['user_id'])) {
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