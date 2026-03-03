<?php
$title = "Inscription - ChallengeHub";
$active_page = "register";
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4 p-2">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <div class="mx-auto mb-3 rounded-3 d-flex align-items-center justify-content-center text-white"
                         style="width:56px;height:56px;background:linear-gradient(135deg,#667eea,#764ba2);">
                        <i class="fas fa-user-plus fs-4"></i>
                    </div>
                    <h1 class="h4 fw-bold mb-1">Créer un compte</h1>
                    <p class="text-muted small">Rejoins la communauté des créatifs</p>
                </div>

                <form method="POST" action="index.php?action=register" autocomplete="off">
                    <?= CSRF::field() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">
                            <i class="fas fa-user me-1 text-primary"></i> Nom d'utilisateur
                        </label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= htmlspecialchars($old_input['username'] ?? '') ?>"
                               placeholder="CyberPunk2024" required minlength="3" maxlength="50">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            <i class="fas fa-envelope me-1 text-primary"></i> Adresse email
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($old_input['email'] ?? '') ?>"
                               placeholder="exemple@mail.com" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-primary"></i> Mot de passe
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="••••••••" required minlength="6">
                        <div class="form-text">Minimum 6 caractères</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-primary"></i> Confirmer le mot de passe
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                               placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                        <i class="fas fa-user-plus me-2"></i> Créer mon compte
                    </button>
                </form>

                <hr class="my-4">

                <p class="text-center small mb-0 text-muted">
                    Déjà un compte ?
                    <a href="index.php?action=showLogin" class="text-primary fw-semibold text-decoration-none">Se connecter</a>
                </p>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>