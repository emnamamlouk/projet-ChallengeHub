<?php
$title = "Inscription - ChallengeHub";
$active_page = "register";

$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

ob_start();
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo">⚡ Challenge<span>Hub</span></div>
            <h1>Créer un compte</h1>
            <p>Rejoins la communauté des créatifs</p>
        </div>

        <form method="POST" action="index.php?action=register" class="auth-form" autocomplete="off">
            <?= CSRF::field() ?>
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i>
                    Nom d'utilisateur
                </label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?= htmlspecialchars($old_input['username'] ?? '') ?>"
                       placeholder="CyberPunk2024" 
                       required
                       minlength="3"
                       maxlength="50">
            </div>

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

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Mot de passe
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="••••••••" 
                       required
                       minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i>
                    Confirmer le mot de passe
                </label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       placeholder="••••••••" 
                       required>
            </div>

            <button type="submit" class="btn-primary btn-block">
                <i class="fas fa-user-plus"></i>
                Créer mon compte
            </button>
        </form>

        <div class="auth-footer">
            <p>Déjà un compte ? <a href="index.php?action=showLogin">Se connecter</a></p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>