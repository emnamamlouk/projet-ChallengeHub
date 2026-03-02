<?php
$title = "Connexion - ChallengeHub";
$active_page = "login";

$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

ob_start();
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo">👾 Challenge<span>Hub</span></div>
            <h1>Content de te revoir !</h1>
            <p>Connecte-toi pour continuer l'aventure</p>
        </div>

        <form method="POST" action="index.php?action=login" class="auth-form" autocomplete="off">
            <?= CSRF::field() ?>
            <!-- Email -->
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Adresse email
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= htmlspecialchars($old_input['email'] ?? '') ?>"
                       placeholder="exemple@mail.com" 
                       required>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Mot de passe
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="••••••••" 
                       required>
            </div>

            <!-- Options -->
            <div class="form-options">
                <label class="checkbox">
                    <input type="checkbox" name="remember">
                    <span class="checkmark"></span>
                    Se souvenir de moi
                </label>
                <a href="#" class="forgot-password">Mot de passe oublié ?</a>
            </div>

            <!-- Bouton -->
            <button type="submit" class="btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>

        <div class="auth-footer">
            <p>Pas encore de compte ? <a href="index.php?action=showRegister">S'inscrire</a></p>
        </div>

        <!-- Compte de démo -->
        <div class="demo-account">
            <p>Compte de démo :</p>
            <code>demo@challengehub.com / demo123</code>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>