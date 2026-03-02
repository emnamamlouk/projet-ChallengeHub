<?php
// app/helpers/CSRF.php
// Protection CSRF simple et fiable

class CSRF {

    // Génère le token une seule fois par session
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Champ hidden à mettre dans les formulaires
    public static function field() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    // Vérifie le token - redirige si invalide
    public static function verify($redirect = 'index.php') {
        $posted  = $_POST['csrf_token'] ?? '';
        $session = $_SESSION['csrf_token'] ?? '';
        if (empty($session) || empty($posted) || $session !== $posted) {
            $_SESSION['error'] = "Erreur de sécurité. Veuillez réessayer.";
            header('Location: ' . $redirect);
            exit();
        }
    }
}
?>