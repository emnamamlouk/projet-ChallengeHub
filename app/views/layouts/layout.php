<?php
if(!class_exists('CSRF') && defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/app/helpers/CSRF.php';
}
// Charger le thème courant
$currentTheme = 'day';
if (defined('ROOT_PATH') && file_exists(ROOT_PATH . '/app/config/ThemeConfig.php')) {
    require_once ROOT_PATH . '/app/config/ThemeConfig.php';
    $currentTheme = ThemeConfig::getCurrentTheme();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'ChallengeHub' ?></title>

    <!-- Bootstrap CSS -->
    <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="vendor/twbs/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS personnalisés -->
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/challenges.css">

    <!-- THÈME NUIT (chargé depuis PHP selon la session) -->
    <?php if($currentTheme === 'night'): ?>
    <link rel="stylesheet" id="theme-css" href="public/css/themes/night.css">
    <?php endif; ?>
</head>
<body>

    <!-- HEADER -->
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">

                <div class="logo">
                    <a href="index.php?action=home">
                        <div class="logo-icon"><i class="fas fa-trophy"></i></div>
                        Challenge<span>Hub</span>
                    </a>
                </div>

                <ul class="nav-menu" id="navMenu">
                    <li>
                        <a href="index.php?action=home" class="<?= isset($active_page) && $active_page === 'home' ? 'active' : '' ?>">
                            <i class="fas fa-home"></i> Accueil
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=search" class="<?= isset($active_page) && $active_page === 'search' ? 'active' : '' ?>">
                            <i class="fas fa-search"></i> Recherche
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li>
                        <a href="index.php?action=ranking" class="<?= isset($active_page) && $active_page === 'ranking' ? 'active' : '' ?>">
                            <i class="fas fa-chart-bar"></i> Classement
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($_SESSION['username']) ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="index.php?action=profile"><i class="fas fa-id-card"></i> Mon profil</a></li>
                                <li><a href="index.php?action=createChallengeForm"><i class="fas fa-plus-circle"></i> Créer un défi</a></li>
                                <li><hr></li>
                                <li><a href="index.php?action=logout" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="index.php?action=showLogin" class="<?= isset($active_page) && $active_page === 'login' ? 'active' : '' ?>">
                                <i class="fas fa-sign-in-alt"></i> Connexion
                            </a>
                        </li>
                        <li>
                            <a href="index.php?action=showRegister" class="<?= isset($active_page) && $active_page === 'register' ? 'active' : '' ?>">
                                <i class="fas fa-user-plus"></i> Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- THEME SWITCHER -->
                <div class="theme-switcher">
                    <button class="theme-toggle" id="themeToggle">
                        <?php if($currentTheme === 'night'): ?>
                            <i class="fas fa-sun"></i>
                            <span>Mode jour</span>
                        <?php else: ?>
                            <i class="fas fa-moon"></i>
                            <span>Mode nuit</span>
                        <?php endif; ?>
                    </button>
                </div>

                <button class="nav-toggle" id="navToggle" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>

            </div>
        </nav>
    </header>

    <!-- CONTENU PRINCIPAL -->
    <main class="main-content">
        <div class="container">

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <div><?= htmlspecialchars($_SESSION['success']) ?></div>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?= htmlspecialchars($_SESSION['error']) ?></div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
                <?php foreach($_SESSION['errors'] as $err): ?>
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div><?= htmlspecialchars($err) ?></div>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <?= isset($content) ? $content : '' ?>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><i class="fas fa-trophy"></i> ChallengeHub</h4>
                    <p>La plateforme collaborative de défis créatifs.</p>
                </div>
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="index.php?action=home"><i class="fas fa-home"></i> Accueil</a></li>
                        <li><a href="index.php?action=ranking"><i class="fas fa-chart-bar"></i> Classement</a></li>
                        <li><a href="index.php?action=search"><i class="fas fa-search"></i> Recherche</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Compte</h4>
                    <ul>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="index.php?action=profile"><i class="fas fa-id-card"></i> Mon profil</a></li>
                            <li><a href="index.php?action=createChallengeForm"><i class="fas fa-plus"></i> Créer un défi</a></li>
                            <li><a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                        <?php else: ?>
                            <li><a href="index.php?action=showLogin"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
                            <li><a href="index.php?action=showRegister"><i class="fas fa-user-plus"></i> Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> ChallengeHub. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- JS -->
    <script src="vendor/components/jquery/jquery.min.js"></script>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
    <script src="public/js/challenges.js"></script>
    <script src="public/js/theme-switcher.js"></script>

    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .main-content { min-height: calc(100vh - 200px); padding: 30px 0; }
        .dropdown { position: relative; }
        .dropdown-menu {
            display: none; position: absolute; top: 110%; right: 0;
            background: white; border: 1px solid #dee2e6; border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); min-width: 180px;
            z-index: 200; list-style: none; padding: 8px 0;
        }
        .dropdown:hover .dropdown-menu { display: block; }
        .dropdown-menu li a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 16px; color: #495057; text-decoration: none; transition: background 0.2s;
        }
        .dropdown-menu li a:hover { background: #f8f9fa; color: #667eea; }
        .text-danger { color: #dc3545 !important; }
        .nav-toggle { display: none; flex-direction: column; gap: 5px; background: none; border: none; cursor: pointer; padding: 5px; }
        .nav-toggle span { display: block; width: 25px; height: 2px; background: #495057; border-radius: 2px; transition: all 0.3s; }
        .theme-switcher { margin-right: 15px; display: flex; align-items: center; }
        .theme-toggle {
            display: flex; align-items: center; gap: 8px; padding: 8px 16px;
            border: 2px solid #667eea; border-radius: 30px; background: transparent;
            color: #667eea; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;
        }
        .theme-toggle i { font-size: 1.1rem; transition: transform 0.3s; }
        .theme-toggle:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.3); }
        .theme-toggle:hover i { transform: rotate(15deg); }
        @media (max-width: 768px) {
            .nav-toggle { display: flex; }
            .nav-menu {
                display: none; position: absolute; top: 100%; left: 0; right: 0;
                background: white; flex-direction: column; padding: 20px;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-top: 1px solid #dee2e6; z-index: 100;
            }
            .nav-menu.open { display: flex; }
            .dropdown-menu { position: static; box-shadow: none; border: none; padding-left: 20px; }
            .theme-toggle span { display: none; }
            .theme-toggle { padding: 8px 12px; }
        }
    </style>

    <script>
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.getElementById('navMenu');
        if (navToggle && navMenu) {
            navToggle.addEventListener('click', function() {
                navMenu.classList.toggle('open');
            });
        }
    </script>

</body>
</html>