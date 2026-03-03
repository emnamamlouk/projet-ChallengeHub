<?php
// app/controllers/ThemeController.php

require_once APP_PATH . '/config/ThemeConfig.php';

class ThemeController {

    public function switch() {
        $theme = $_GET['theme'] ?? 'day';
        ThemeConfig::setTheme($theme);

        // Récupérer la page de redirection passée en paramètre
        $redirect = $_GET['redirect'] ?? 'index.php';

        // Sécurité : s'assurer que la redirection reste sur le même domaine
        $host = $_SERVER['HTTP_HOST'];
        if (strpos($redirect, $host) === false && strpos($redirect, 'index.php') === false) {
            $redirect = 'index.php';
        }

        header('Location: ' . $redirect);
        exit();
    }
}