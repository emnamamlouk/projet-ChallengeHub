<?php
// app/controllers/ThemeController.php

require_once APP_PATH . '/config/ThemeConfig.php';

class ThemeController {

    public function switch() {
        // Valider le thème
        $theme = $_GET['theme'] ?? 'day';
        if (!in_array($theme, ['day', 'night'])) {
            $theme = 'day';
        }

        // Sauvegarder en session + cookie
        ThemeConfig::setTheme($theme);

        // Récupérer la page de redirection
        $redirect = $_GET['redirect'] ?? 'index.php';

        // Sécurité : si URL absolue, vérifier même domaine
        if (preg_match('/^https?:\/\//i', $redirect)) {
            $host = $_SERVER['HTTP_HOST'];
            if (strpos($redirect, $host) === false) {
                $redirect = 'index.php';
            }
        }

        if (empty(trim($redirect))) {
            $redirect = 'index.php';
        }

        header('Location: ' . $redirect);
        exit();
    }
}