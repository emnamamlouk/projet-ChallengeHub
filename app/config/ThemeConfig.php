<?php
// app/config/ThemeConfig.php

class ThemeConfig {
    
    private static $themes = [
        'night' => [
            'name' => 'Mode Nuit',
            'icon' => 'moon-stars-fill'
        ],
        'day' => [
            'name' => 'Mode Jour',
            'icon' => 'sun-fill'
        ]
    ];
    
    public static function getCurrentTheme() {
        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['theme'])) {
            return $_SESSION['theme'];
        }
        
        if (isset($_COOKIE['theme'])) {
            $_SESSION['theme'] = $_COOKIE['theme'];
            return $_COOKIE['theme'];
        }
        
        return 'day'; // thème par défaut
    }
    
    public static function setTheme($theme) {
        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset(self::$themes[$theme])) {
            $theme = 'day';
        }
        
        $_SESSION['theme'] = $theme;
        setcookie('theme', $theme, time() + (86400 * 30), '/'); // 30 jours
        
        return $theme;
    }
    
    public static function getThemeIcon($theme) {
        return self::$themes[$theme]['icon'] ?? 'sun-fill';
    }
    
    public static function getThemeName($theme) {
        return self::$themes[$theme]['name'] ?? 'Mode Jour';
    }
}
?>