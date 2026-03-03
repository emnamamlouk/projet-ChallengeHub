<?php
if (!class_exists('CSRF') && defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/app/helpers/CSRF.php';
}
$currentTheme = 'day';
if (defined('ROOT_PATH') && file_exists(ROOT_PATH . '/app/config/ThemeConfig.php')) {
    require_once ROOT_PATH . '/app/config/ThemeConfig.php';
    $currentTheme = ThemeConfig::getCurrentTheme();
}
$isNight = ($currentTheme === 'night');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'ChallengeHub' ?></title>

    <!-- Bootstrap 5 CSS (via Composer) -->
    <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (via Composer) -->
    <link href="vendor/twbs/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS custom -->
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/challenges.css">

    <?php if ($isNight): ?>
    <link rel="stylesheet" id="theme-css" href="public/css/themes/night.css">
    <?php endif; ?>

    <style>
    /* FORCER LA NAVBAR EN MODE NUIT */
    body.night-mode .navbar {
        background-color: #1a1a1a !important;
        border-bottom: 3px solid #8b5cf6 !important;
    }
    body.night-mode .navbar-brand,
    body.night-mode .navbar-brand span {
        color: white !important;
    }
    body.night-mode .navbar-brand span {
        color: #8b5cf6 !important;
    }
    body.night-mode .nav-link {
        color: #e0e0e0 !important;
    }
    body.night-mode .nav-link:hover,
    body.night-mode .nav-link.active {
        color: white !important;
        background-color: #8b5cf6 !important;
        border-radius: 6px !important;
    }
    body.night-mode .theme-toggle {
        background-color: #2d2d2d !important;
        border: 2px solid #8b5cf6 !important;
        color: white !important;
    }
    body.night-mode .theme-toggle:hover {
        background-color: #8b5cf6 !important;
    }
    </style>
</head>
<body class="<?= $isNight ? 'night-mode' : '' ?>">

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg sticky-top shadow-sm border-bottom">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php?action=home">
            <div class="rounded-2 d-flex align-items-center justify-content-center text-white"
                 style="width:36px;height:36px;background:linear-gradient(135deg,#667eea,#764ba2);">
                <i class="bi bi-trophy-fill"></i>
            </div>
            Challenge<span class="text-primary">Hub</span>
        </a>

        <!-- Toggler mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Liens gauche -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= isset($active_page) && $active_page === 'home' ? 'active fw-semibold' : '' ?>"
                       href="index.php?action=home">
                        <i class="bi bi-house-fill me-1"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($active_page) && $active_page === 'search' ? 'active fw-semibold' : '' ?>"
                       href="index.php?action=search">
                        <i class="bi bi-search me-1"></i> Recherche
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= isset($active_page) && $active_page === 'ranking' ? 'active fw-semibold' : '' ?>"
                       href="index.php?action=ranking">
                        <i class="bi bi-bar-chart-fill me-1"></i> Classement
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Liens droite -->
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">

                <!-- Bouton thème -->
                <li class="nav-item">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="themeToggle">
                        <?php if ($isNight): ?>
                            <i class="bi bi-sun-fill me-1"></i> Mode jour
                        <?php else: ?>
                            <i class="bi bi-moon-fill me-1"></i> Mode nuit
                        <?php endif; ?>
                    </button>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Dropdown utilisateur -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                       href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="public/<?= htmlspecialchars($_SESSION['avatar']) ?>"
                                 class="rounded-circle" width="28" height="28" style="object-fit:cover;">
                        <?php else: ?>
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                 style="width:28px;height:28px;background:linear-gradient(135deg,#667eea,#764ba2);font-size:0.75rem;">
                                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <?= htmlspecialchars($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <a class="dropdown-item" href="index.php?action=profile">
                                <i class="bi bi-person-badge-fill me-2 text-primary"></i> Mon profil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="index.php?action=createChallengeForm">
                                <i class="bi bi-plus-circle-fill me-2 text-success"></i> Créer un défi
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="index.php?action=logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="btn btn-outline-primary btn-sm rounded-pill px-3"
                       href="index.php?action=showLogin">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Connexion
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm rounded-pill px-3 text-white"
                       href="index.php?action=showRegister">
                        <i class="bi bi-person-plus-fill me-1"></i> Inscription
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- ===== CONTENU PRINCIPAL ===== -->
<main class="py-4">
    <div class="container">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?= htmlspecialchars($_SESSION['success']) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <div><?= htmlspecialchars($_SESSION['error']) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
            <?php foreach ($_SESSION['errors'] as $err): ?>
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= htmlspecialchars($err) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <?= isset($content) ? $content : '' ?>

    </div>
</main>

<!-- ===== FOOTER ===== -->
<footer class="mt-5 py-5 border-top">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-trophy-fill text-primary me-2"></i>ChallengeHub
                </h5>
                <p class="text-muted small">La plateforme collaborative de défis créatifs.</p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Navigation</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1">
                        <a href="index.php?action=home" class="text-muted text-decoration-none">
                            <i class="bi bi-house-fill me-1"></i> Accueil
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="index.php?action=ranking" class="text-muted text-decoration-none">
                            <i class="bi bi-bar-chart-fill me-1"></i> Classement
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="index.php?action=search" class="text-muted text-decoration-none">
                            <i class="bi bi-search me-1"></i> Recherche
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Compte</h6>
                <ul class="list-unstyled small">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="mb-1">
                            <a href="index.php?action=profile" class="text-muted text-decoration-none">
                                <i class="bi bi-person-badge-fill me-1"></i> Mon profil
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="index.php?action=createChallengeForm" class="text-muted text-decoration-none">
                                <i class="bi bi-plus-circle-fill me-1"></i> Créer un défi
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="index.php?action=logout" class="text-muted text-decoration-none">
                                <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="mb-1">
                            <a href="index.php?action=showLogin" class="text-muted text-decoration-none">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Connexion
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="index.php?action=showRegister" class="text-muted text-decoration-none">
                                <i class="bi bi-person-plus-fill me-1"></i> Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <hr>
        <p class="text-center text-muted small mb-0">
            &copy; <?= date('Y') ?> ChallengeHub. Tous droits réservés.
        </p>
    </div>
</footer>

<!-- JS (tous via Composer) -->
<script src="vendor/components/jquery/jquery.min.js"></script>
<script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/main.js"></script>
<script src="public/js/challenges.js"></script>
<script src="public/js/theme-switcher.js"></script>

</body>
</html>