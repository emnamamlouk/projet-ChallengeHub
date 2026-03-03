<?php
// app/controllers/ThemeController.php

require_once APP_PATH . '/config/ThemeConfig.php';

class ThemeController {
    
    public function switch() {
        $theme = $_GET['theme'] ?? 'day';
        ThemeConfig::setTheme($theme);
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();
    }
}