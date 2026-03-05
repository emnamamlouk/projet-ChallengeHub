<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../models/Notification.php';

/**
 * NotificationController — Endpoints AJAX pour les notifications (fonctionnalité bonus)
 * Emplacement : /app/controllers/NotificationController.php
 *
 * Toutes les méthodes retournent du JSON.
 * Le layout (layout.php) appelle le polling toutes les 30 secondes via JS.
 *
 * Routes à ajouter dans index.php :
 *   'getNotifications'  => ['controller' => 'NotificationController', 'method' => 'getNotifications'],
 *   'markNotifRead'     => ['controller' => 'NotificationController', 'method' => 'markRead'],
 *   'markAllNotifsRead' => ['controller' => 'NotificationController', 'method' => 'markAllRead'],
 */
class NotificationController {

    private $notifModel;

    public function __construct() {
        $this->notifModel = new Notification();
    }

    // ----------------------------------------------------------------
    // GET index.php?action=getNotifications
    // Retourne la liste des 10 dernières notifications + compteur non lus
    // ----------------------------------------------------------------
    public function getNotifications(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            exit();
        }

        $user_id       = (int)$_SESSION['user_id'];
        $notifications = $this->notifModel->getForUser($user_id, 10);
        $unread        = $this->notifModel->countUnread($user_id);

        echo json_encode([
            'success'       => true,
            'unread'        => $unread,
            'notifications' => $notifications,
        ]);
        exit();
    }

    // ----------------------------------------------------------------
    // POST index.php?action=markNotifRead  { id: N }
    // ----------------------------------------------------------------
    public function markRead(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit();
        }

        $notif_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $user_id  = (int)$_SESSION['user_id'];

        $ok = $this->notifModel->markRead($notif_id, $user_id);
        echo json_encode(['success' => $ok]);
        exit();
    }

    // ----------------------------------------------------------------
    // POST index.php?action=markAllNotifsRead
    // ----------------------------------------------------------------
    public function markAllRead(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit();
        }

        $ok = $this->notifModel->markAllRead((int)$_SESSION['user_id']);
        echo json_encode(['success' => $ok]);
        exit();
    }
}
?> 