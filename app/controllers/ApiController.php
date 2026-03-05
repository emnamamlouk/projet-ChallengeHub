<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../models/Challenge.php';
require_once __DIR__ . '/../models/Submission.php';
require_once __DIR__ . '/../models/Vote.php';
require_once __DIR__ . '/../models/User.php';

/**
 * ApiController — API REST interne (fonctionnalité bonus)
 * Emplacement : /app/controllers/ApiController.php
 *
 * Expose des endpoints JSON réutilisables depuis le front-end (JS/AJAX)
 * ou par tout client externe.
 *
 * Routes à ajouter dans index.php :
 *   'api_challenges'   => ['controller' => 'ApiController', 'method' => 'challenges'],
 *   'api_challenge'    => ['controller' => 'ApiController', 'method' => 'challenge'],
 *   'api_submissions'  => ['controller' => 'ApiController', 'method' => 'submissions'],
 *   'api_leaderboard'  => ['controller' => 'ApiController', 'method' => 'leaderboard'],
 *   'api_user'         => ['controller' => 'ApiController', 'method' => 'user'],
 *
 * Exemples d'appels :
 *   GET index.php?action=api_challenges&category=art&sort=popular&page=2
 *   GET index.php?action=api_challenge&id=5
 *   GET index.php?action=api_submissions&challenge_id=5
 *   GET index.php?action=api_leaderboard&limit=10
 *   GET index.php?action=api_user&id=3
 */
class ApiController {

    private $challengeModel;
    private $submissionModel;
    private $voteModel;
    private $userModel;

    public function __construct() {
        $this->challengeModel  = new Challenge();
        $this->submissionModel = new Submission();
        $this->voteModel       = new Vote();
        $this->userModel       = new User();
    }

    // ----------------------------------------------------------------
    // Helpers internes
    // ----------------------------------------------------------------

    /** Envoie une réponse JSON et termine l'exécution */
    private function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        // Entête CORS minimale (utile si consommation externe)
        header('Access-Control-Allow-Origin: *');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /** Vérifier que la clé API simple est fournie (protection minimale) */
    private function requireApiKey(): void {
        // Clé configurée dans config/Database.php ou directement ici
        // Pour les besoins du cours, une clé statique suffit.
        $expected = defined('API_KEY') ? API_KEY : 'challengehub_api_2026';
        $provided = $_GET['api_key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';
        if ($provided !== $expected) {
            $this->json(['error' => 'Clé API invalide ou manquante.'], 401);
        }
    }

    // ----------------------------------------------------------------
    // GET /api_challenges  — Liste paginée des défis
    // Paramètres : category, sort (recent|popular), page, per_page, search
    // ----------------------------------------------------------------
    public function challenges(): void {
        $this->requireApiKey();

        $category = $_GET['category'] ?? 'all';
        $sort     = $_GET['sort']     ?? 'recent';
        $search   = $_GET['search']   ?? '';
        $page     = max(1, (int)($_GET['page']     ?? 1));
        $perPage  = min(50, max(1, (int)($_GET['per_page'] ?? 10)));

        // Récupération via le modèle existant
        $all   = $this->challengeModel->getAllChallengesSorted($sort, $category, $search);
        $total = count($all);
        $items = array_slice($all, ($page - 1) * $perPage, $perPage);

        // Nettoyer les champs sensibles
        foreach ($items as &$item) {
            unset($item['user_email']);
        }

        $this->json([
            'success' => true,
            'meta'    => [
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => max(1, (int)ceil($total / $perPage)),
            ],
            'data' => $items,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /api_challenge?id=N  — Détail d'un défi
    // ----------------------------------------------------------------
    public function challenge(): void {
        $this->requireApiKey();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'Paramètre id manquant ou invalide.'], 400);
        }

        $challenge = $this->challengeModel->getChallengeById($id);
        if (!$challenge) {
            $this->json(['error' => 'Défi introuvable.'], 404);
        }

        $submissions = $this->submissionModel->getSubmissionsByChallenge($id);
        foreach ($submissions as &$sub) {
            $sub['votes_count'] = $this->voteModel->getVoteCount($sub['id']);
        }

        $this->json([
            'success'     => true,
            'challenge'   => $challenge,
            'submissions' => $submissions,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /api_submissions?challenge_id=N  — Participations d'un défi
    // ----------------------------------------------------------------
    public function submissions(): void {
        $this->requireApiKey();

        $challenge_id = (int)($_GET['challenge_id'] ?? 0);
        if ($challenge_id <= 0) {
            $this->json(['error' => 'Paramètre challenge_id manquant.'], 400);
        }

        $submissions = $this->submissionModel->getSubmissionsByChallenge($challenge_id);
        foreach ($submissions as &$sub) {
            $sub['votes_count'] = $this->voteModel->getVoteCount($sub['id']);
        }

        // Trier par votes décroissant
        usort($submissions, fn($a, $b) => $b['votes_count'] - $a['votes_count']);

        $this->json([
            'success' => true,
            'count'   => count($submissions),
            'data'    => $submissions,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /api_leaderboard?limit=N  — Top participations toutes catégories
    // ----------------------------------------------------------------
    public function leaderboard(): void {
        $this->requireApiKey();

        $limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));

        // Réutilise la méthode du modèle Submission existant
        $top = $this->submissionModel->getTopSubmissions($limit);

        foreach ($top as &$item) {
            $item['votes_count'] = $this->voteModel->getVoteCount($item['id']);
        }

        $this->json([
            'success' => true,
            'data'    => $top,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /api_user?id=N  — Profil public d'un utilisateur
    // ----------------------------------------------------------------
    public function user(): void {
        $this->requireApiKey();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'Paramètre id manquant.'], 400);
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $this->json(['error' => 'Utilisateur introuvable.'], 404);
        }

        // Ne jamais exposer le mot de passe
        unset($user['password']);

        $this->json(['success' => true, 'user' => $user]);
    }
}
?> 