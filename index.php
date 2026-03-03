<?php
// ============================================
// POINT D'ENTRÉE UNIQUE - ChallengeHub
// ============================================

// CONFIGURATION ERREURS
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// CONSTANTES DE CHEMINS
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);

// DÉMARRAGE SESSION (avant tout)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// CHARGEMENT CSRF (après session)
require_once ROOT_PATH . '/app/helpers/CSRF.php';
// Génère le token dès le début si pas encore fait
CSRF::generateToken();

// Protection CSRF - token généré et disponible dans les formulaires
// La vérification se fait via CSRF::check() dans les actions sensibles

// ============================================
// AUTOLOADER
// ============================================
spl_autoload_register(function ($class_name) {
    $directories = [
        APP_PATH . '/controllers/',
        APP_PATH . '/models/'
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ============================================
// ROUTER
// ============================================
$action = isset($_GET['action']) ? $_GET['action'] : 'home';
$action = preg_replace('/[^a-zA-Z0-9_]/', '', $action);

$routes = [
    // AUTH
    'showRegister' => ['controller' => 'AuthController', 'method' => 'showRegister'],
    'register' => ['controller' => 'AuthController', 'method' => 'register'],
    'showLogin' => ['controller' => 'AuthController', 'method' => 'showLogin'],
    'login' => ['controller' => 'AuthController', 'method' => 'login'],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    'profile' => ['controller' => 'AuthController', 'method' => 'profile'],
    'updateProfile' => ['controller' => 'AuthController', 'method' => 'updateProfile'],
    'deleteAccount' => ['controller' => 'AuthController', 'method' => 'deleteAccount'],

    // CHALLENGES
    'home' => ['controller' => 'ChallengeController', 'method' => 'home'],
    'showChallenge' => ['controller' => 'ChallengeController', 'method' => 'show'],
    'createChallengeForm' => ['controller' => 'ChallengeController', 'method' => 'createForm'],
    'createChallenge' => ['controller' => 'ChallengeController', 'method' => 'create'],
    'editChallengeForm' => ['controller' => 'ChallengeController', 'method' => 'editForm'],
    'updateChallenge' => ['controller' => 'ChallengeController', 'method' => 'update'],
    'deleteChallenge' => ['controller' => 'ChallengeController', 'method' => 'delete'],
    'vote' => ['controller' => 'ChallengeController', 'method' => 'vote'],
    'ranking' => ['controller' => 'ChallengeController', 'method' => 'ranking'],
    'toggleChallengeLike' => ['controller' => 'ChallengeController', 'method' => 'toggleLike'],
    'addChallengeComment'  => ['controller' => 'ChallengeController', 'method' => 'addChallengeComment'],
    'getChallengeComments' => ['controller' => 'ChallengeController', 'method' => 'getChallengeComments'],
    'deleteChallengeComment' => ['controller' => 'ChallengeController', 'method' => 'deleteChallengeComment'],

    // SUBMISSIONS
    'showSubmission' => ['controller' => 'SubmissionController', 'method' => 'show'],
    'createSubmissionForm' => ['controller' => 'SubmissionController', 'method' => 'createForm'],
    'createSubmission' => ['controller' => 'SubmissionController', 'method' => 'create'],
    'editSubmissionForm' => ['controller' => 'SubmissionController', 'method' => 'editForm'],
    'updateSubmission' => ['controller' => 'SubmissionController', 'method' => 'update'],
    'deleteSubmission' => ['controller' => 'SubmissionController', 'method' => 'delete'],

    // COMMENTS
    'addComment' => ['controller' => 'CommentController', 'method' => 'add'],
    'deleteComment' => ['controller' => 'CommentController', 'method' => 'delete'],
    'getComments' => ['controller' => 'CommentController', 'method' => 'getComments'],
    'countComments' => ['controller' => 'CommentController', 'method' => 'countComments'],

    // SEARCH
    'search' => ['controller' => 'ChallengeController', 'method' => 'home'],
'search' => ['controller' => 'UserController', 'method' => 'search'],
'viewProfile' => ['controller' => 'UserController', 'method' => 'viewProfile'],
// THEME
'switchTheme' => ['controller' => 'ThemeController', 'method' => 'switch'],



];


// ============================================
// FONCTION 404
// ============================================
function notFound() {
    http_response_code(404);
    echo "<h1>404 - Page non trouvée</h1>";
    echo "<p>La page que vous cherchez n'existe pas.</p>";
    echo '<p><a href="index.php?action=home">Retour à l\'accueil</a></p>';
    exit();
}

// ============================================
// EXÉCUTION ROUTER
// ============================================
if (isset($routes[$action])) {
    $route = $routes[$action];
    $controllerName = $route['controller'];
    $methodName = $route['method'];

    $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

    if (file_exists($controllerFile)) {
        require_once $controllerFile;

        if (class_exists($controllerName)) {
            $controller = new $controllerName();

            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
            } else {
                notFound();
            }
        } else {
            notFound();
        }
    } else {
        notFound();
    }
} else {
    notFound();
}

// ============================================
// HELPERS
// ============================================

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function url($action, $params = []) {
    $url = 'index.php?action=' . $action;
    foreach ($params as $key => $value) {
        $url .= '&' . urlencode($key) . '=' . urlencode($value);
    }
    return $url;
}

function displayFlashMessages() {
    $output = '';
    if (isset($_SESSION['success'])) {
        $output .= '<div class="alert alert-success">' . e($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        $output .= '<div class="alert alert-danger">' . e($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])) {
        foreach ($_SESSION['errors'] as $error) {
            $output .= '<div class="alert alert-warning">' . e($error) . '</div>';
        }
        unset($_SESSION['errors']);
    }
    return $output;
}
?>