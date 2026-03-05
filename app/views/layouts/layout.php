<?php 
if(!class_exists('CSRF') && defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/app/helpers/CSRF.php';
}

$currentTheme = 'day';
if (defined('ROOT_PATH') && file_exists(ROOT_PATH . '/app/config/ThemeConfig.php')) {
    require_once ROOT_PATH . '/app/config/ThemeConfig.php';
    $currentTheme = ThemeConfig::getCurrentTheme();
}
$isNight = ($currentTheme === 'night');

$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath   = rtrim(dirname($scriptName), '/\\');
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'ChallengeHub' ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>/vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $basePath ?>/public/css/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $basePath ?>/public/css/style.css">
    <link rel="stylesheet" href="<?= $basePath ?>/public/css/challenges.css">
    <?php if ($isNight): ?>
    <link rel="stylesheet" id="theme-css" href="<?= $basePath ?>/public/css/themes/night.css">
    <?php endif; ?>
</head>
<body class="<?= $isNight ? 'night-mode' : '' ?>">

    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">

                <div class="logo">
                    <a href="<?= $basePath ?>/index.php?action=home">
                        <div class="logo-icon"><i class="bi bi-trophy-fill"></i></div>
                        Challenge<span>Hub</span>
                    </a>
                </div>

                <ul class="nav-menu" id="navMenu">
                    <li>
                        <a href="<?= $basePath ?>/index.php?action=home" class="<?= isset($active_page) && $active_page === 'home' ? 'active' : '' ?>">
                            <i class="bi bi-house-fill"></i> Accueil
                        </a>
                    </li>
                    <li>
                        <a href="<?= $basePath ?>/index.php?action=search" class="<?= isset($active_page) && $active_page === 'search' ? 'active' : '' ?>">
                            <i class="bi bi-search"></i> Recherche
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li>
                        <a href="<?= $basePath ?>/index.php?action=ranking" class="<?= isset($active_page) && $active_page === 'ranking' ? 'active' : '' ?>">
                            <i class="bi bi-bar-chart-fill"></i> Classement
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['user_id'])): ?>

                        <!-- ── BONUS : Cloche de notifications AJAX ── -->
                        <li class="notif-nav-item" style="position:relative; list-style:none; display:flex; align-items:center;">
                            <div style="position:relative; cursor:pointer;" id="notifToggle" onclick="document.getElementById('notifDropdown').classList.toggle('notif-open')">
                                <i class="bi bi-bell-fill" style="font-size:1.3rem; color:#495057;"></i>
                                <span id="notif-count"
                                      style="display:none; position:absolute; top:-6px; right:-8px;
                                             background:#dc3545; color:#fff; border-radius:50%;
                                             font-size:.6rem; min-width:16px; height:16px;
                                             text-align:center; line-height:16px; font-weight:700;">
                                </span>
                            </div>
                            <!-- Dropdown notifications -->
                            <div id="notifDropdown" style="display:none; position:absolute; top:130%; right:0; width:320px; max-height:380px; overflow-y:auto;
                                                           background:#fff; border:1px solid #dee2e6; border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,.12); z-index:9999;">
                                <div style="padding:10px 14px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                                    <strong style="font-size:.9rem;">Notifications</strong>
                                    <a href="#" id="notif-mark-all" style="font-size:.75rem; color:#6c757d; text-decoration:none;">Tout marquer lu</a>
                                </div>
                                <ul id="notif-list" style="list-style:none; margin:0; padding:0;">
                                    <li style="padding:12px 14px; color:#6c757d; font-size:.85rem;">Chargement…</li>
                                </ul>
                            </div>
                        </li>



                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <i class="bi bi-person-fill"></i>
                                <?= htmlspecialchars($_SESSION['username']) ?>
                                <i class="bi bi-chevron-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= $basePath ?>/index.php?action=profile"><i class="bi bi-person-vcard-fill"></i> Mon profil</a></li>
                                <li><a href="<?= $basePath ?>/index.php?action=createChallengeForm"><i class="bi bi-plus-circle-fill"></i> Créer un défi</a></li>

                                <li><hr></li>
                                <li><a href="<?= $basePath ?>/index.php?action=logout" class="text-danger"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?= $basePath ?>/index.php?action=showLogin" class="<?= isset($active_page) && $active_page === 'login' ? 'active' : '' ?>">
                                <i class="bi bi-box-arrow-in-right"></i> Connexion
                            </a>
                        </li>
                        <li>
                            <a href="<?= $basePath ?>/index.php?action=showRegister" class="<?= isset($active_page) && $active_page === 'register' ? 'active' : '' ?>">
                                <i class="bi bi-person-plus-fill"></i> Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- BOUTON ADMIN — visible uniquement si is_admin = 1 -->
                <?php if(!empty($_SESSION['is_admin'])): ?>
                <a href="<?= $basePath ?>/index.php?action=admin" class="btn-admin-navbar">
                    <i class="bi bi-shield-fill-check"></i>
                    <span>Dashboard</span>
                </a>
                <?php endif; ?>

                <div class="theme-switcher">
                    <button class="theme-toggle" id="themeToggle">
                        <?php if ($isNight): ?>
                            <i class="bi bi-sun-fill"></i>
                            <span>Mode jour</span>
                        <?php else: ?>
                            <i class="bi bi-moon-fill"></i>
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

    <main class="main-content">
        <div class="container">

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
                <?php foreach($_SESSION['errors'] as $err): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= htmlspecialchars($err) ?>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <?= isset($content) ? $content : '' ?>

        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><i class="bi bi-trophy-fill"></i> ChallengeHub</h4>
                    <p>La plateforme collaborative de défis créatifs.</p>
                </div>
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="<?= $basePath ?>/index.php?action=home"><i class="bi bi-house-fill"></i> Accueil</a></li>
                        <li><a href="<?= $basePath ?>/index.php?action=ranking"><i class="bi bi-bar-chart-fill"></i> Classement</a></li>
                        <li><a href="<?= $basePath ?>/index.php?action=search"><i class="bi bi-search"></i> Recherche</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Compte</h4>
                    <ul>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="<?= $basePath ?>/index.php?action=profile"><i class="bi bi-person-vcard-fill"></i> Mon profil</a></li>
                            <li><a href="<?= $basePath ?>/index.php?action=createChallengeForm"><i class="bi bi-plus-lg"></i> Créer un défi</a></li>
                            <?php if(!empty($_SESSION['is_admin'])): ?>
                            <li><a href="<?= $basePath ?>/index.php?action=admin"><i class="bi bi-shield-fill-check"></i> Dashboard Admin</a></li>
                            <?php endif; ?>
                            <li><a href="<?= $basePath ?>/index.php?action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                        <?php else: ?>
                            <li><a href="<?= $basePath ?>/index.php?action=showLogin"><i class="bi bi-box-arrow-in-right"></i> Connexion</a></li>
                            <li><a href="<?= $basePath ?>/index.php?action=showRegister"><i class="bi bi-person-plus-fill"></i> Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> ChallengeHub. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="<?= $basePath ?>/vendor/components/jquery/jquery.min.js"></script>
    <script src="<?= $basePath ?>/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $basePath ?>/public/js/main.js"></script>
    <script src="<?= $basePath ?>/public/js/challenges.js"></script>
    <script src="<?= $basePath ?>/public/js/theme-switcher.js"></script>

    <!-- ── BONUS : Notifications AJAX (uniquement si connecté) ── -->
    <?php if(isset($_SESSION['user_id'])): ?>
    <script>
        window.APP_BASE_URL = '<?= $basePath ?>/';
    </script>
    <script src="<?= $basePath ?>/public/js/notifications.js"></script>
    <script>
    // Fermer le dropdown notifs en cliquant ailleurs
    document.addEventListener('click', function(e) {
        const toggle   = document.getElementById('notifToggle');
        const dropdown = document.getElementById('notifDropdown');
        if (dropdown && toggle && !toggle.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('notif-open');
            dropdown.style.display = 'none';
        }
    });
    // Gérer l'ouverture/fermeture du dropdown par CSS class
    const obs = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            const d = document.getElementById('notifDropdown');
            if (d) d.style.display = d.classList.contains('notif-open') ? 'block' : 'none';
        });
    });
    const nd = document.getElementById('notifDropdown');
    if (nd) obs.observe(nd, { attributes: true, attributeFilter: ['class'] });
    </script>
    <!-- Toast container pour les nouvelles notifications -->
    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;"></div>
    <?php endif; ?>

    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .main-content { min-height: calc(100vh - 200px); padding: 30px 0; }
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger  { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .dropdown { position: relative; }
        .dropdown-menu { display: none; position: absolute; top: 110%; right: 0; background: white; border: 1px solid #dee2e6; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); min-width: 200px; z-index: 200; list-style: none; padding: 8px 0; }
        .dropdown:hover .dropdown-menu { display: block; }
        .dropdown-menu li a { display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #495057; text-decoration: none; transition: background 0.2s; }
        .dropdown-menu li a:hover { background: #f8f9fa; color: #667eea; }
        .text-danger { color: #dc3545 !important; }
        .nav-toggle { display: none; flex-direction: column; gap: 5px; background: none; border: none; cursor: pointer; padding: 5px; }
        .nav-toggle span { display: block; width: 25px; height: 2px; background: #495057; border-radius: 2px; transition: all 0.3s; }
        .theme-switcher { margin-right: 15px; display: flex; align-items: center; }
        .theme-toggle { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border: 2px solid #667eea; border-radius: 30px; background: transparent; color: #667eea; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; outline: none; }
        .theme-toggle i { font-size: 1.1rem; transition: transform 0.3s; }
        .theme-toggle:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.3); }

        /* ── BOUTON ADMIN NAVBAR ── */
        .btn-admin-navbar {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 18px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white !important;
            border-radius: 30px;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            margin-right: 12px;
            transition: all 0.3s;
            box-shadow: 0 4px 14px rgba(102,126,234,0.4);
        }
        .btn-admin-navbar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.5);
            color: white !important;
        }
        .btn-admin-navbar i { font-size: 1rem; }

        @media (max-width: 768px) {
            .nav-toggle { display: flex; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; flex-direction: column; padding: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-top: 1px solid #dee2e6; z-index: 100; }
            .nav-menu.open { display: flex; }
            .dropdown-menu { position: static; box-shadow: none; border: none; padding-left: 20px; }
            .theme-toggle span { display: none; }
            .theme-toggle { padding: 8px 12px; }
            .btn-admin-navbar span { display: none; }
            .btn-admin-navbar { padding: 8px 12px; margin-right: 6px; }
        }
    </style>

    <script>
        const navToggle = document.getElementById('navToggle');
        const navMenu   = document.getElementById('navMenu');
        if (navToggle && navMenu) {
            navToggle.addEventListener('click', function() {
                navMenu.classList.toggle('open');
            });
        }
    </script>

</body>
</html>