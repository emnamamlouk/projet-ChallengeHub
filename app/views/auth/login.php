<?php
$title = "Connexion - ChallengeHub";
$active_page = "login";
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 p-2">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <div class="mx-auto mb-3 rounded-3 d-flex align-items-center justify-content-center text-white"
                         style="width:56px;height:56px;background:linear-gradient(135deg,#667eea,#764ba2);">
                        <i class="fas fa-sign-in-alt fs-4"></i>
                    </div>
                    <h1 class="h4 fw-bold mb-1">Content de te revoir !</h1>
                    <p class="text-muted small">Connecte-toi pour continuer l'aventure</p>
                </div>

                <form method="POST" action="index.php?action=login" autocomplete="off">
                    <?= CSRF::field() ?>

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
                               placeholder="••••••••" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label small" for="remember">Se souvenir de moi</label>
                        </div>
                        <a href="#" class="small text-primary text-decoration-none">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                        <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                    </button>
                </form>

                <hr class="my-4">

                <p class="text-center small mb-2 text-muted">
                    Pas encore de compte ?
                    <a href="index.php?action=showRegister" class="text-primary fw-semibold text-decoration-none">S'inscrire</a>
                </p>

                <div class="bg-light rounded-3 p-3 text-center mt-3">
                    <p class="small text-muted mb-1">Compte de démo :</p>
                    <code class="small">demo@challengehub.com / demo123</code>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>