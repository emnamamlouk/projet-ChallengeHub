<?php
$title = "Participer - " . htmlspecialchars($challenge['title']);
$active_page = "";
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?action=home" class="text-decoration-none"><i class="bi bi-house-fill me-1"></i>Accueil</a></li>
                <li class="breadcrumb-item"><a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($challenge['title']) ?></a></li>
                <li class="breadcrumb-item active">Participer</li>
            </ol>
        </nav>

        <!-- Info défi -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 d-flex gap-3 align-items-center">
                <?php if (!empty($challenge['image'])): ?>
                    <img src="public/<?= htmlspecialchars($challenge['image']) ?>" class="rounded-3" style="width:80px;height:60px;object-fit:cover;">
                <?php endif; ?>
                <div>
                    <span class="badge rounded-pill mb-1" style="background:#ede9fe;color:#667eea;"><?= htmlspecialchars($challenge['category']) ?></span>
                    <h2 class="h6 fw-bold mb-0"><?= htmlspecialchars($challenge['title']) ?></h2>
                    <small class="text-muted"><?= htmlspecialchars(substr($challenge['description'], 0, 100)) ?>...</small>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="mx-auto mb-3 rounded-3 d-flex align-items-center justify-content-center text-white"
                         style="width:48px;height:48px;background:linear-gradient(135deg,#667eea,#764ba2);">
                        <i class="bi bi-send-fill fs-5"></i>
                    </div>
                    <h1 class="h5 fw-bold mb-1">Soumettre ma participation</h1>
                    <p class="text-muted small">Partagez votre création pour ce défi</p>
                </div>

                <form action="index.php?action=createSubmission" method="POST" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="challenge_id" value="<?= $challenge['id'] ?>">

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">
                            <i class="bi bi-text-left me-1 text-primary"></i> Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="5"
                                  placeholder="Décrivez votre création, votre démarche, les outils utilisés..."
                                  required><?= htmlspecialchars($_SESSION['old_input']['description'] ?? '') ?></textarea>
                        <div class="form-text">Minimum 20 caractères, maximum 2000</div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label fw-semibold">
                            <i class="bi bi-card-image me-1 text-primary"></i> Image
                            <span class="badge bg-secondary fw-normal ms-1">Optionnel</span>
                        </label>
                        <input type="file" class="form-control" id="image" name="image"
                               accept="image/*" onchange="previewImage(event)">
                        <div class="form-text">JPEG, PNG, GIF, WEBP — Max 5Mo</div>
                        <div id="imagePreview" class="mt-2" style="display:none;">
                            <img id="previewImg" src="" class="rounded-3 border" style="max-height:200px;max-width:100%;">
                            <br>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeImage">
                                <i class="bi bi-x-circle-fill me-1"></i> Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="link" class="form-label fw-semibold">
                            <i class="bi bi-link-45deg me-1 text-primary"></i> Lien externe
                            <span class="badge bg-secondary fw-normal ms-1">Optionnel</span>
                        </label>
                        <input type="url" class="form-control" id="link" name="link"
                               placeholder="https://github.com/..."
                               value="<?= htmlspecialchars($_SESSION['old_input']['link'] ?? '') ?>">
                        <div class="form-text">Lien vers votre projet, GitHub, Dribbble, etc.</div>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="bi bi-arrow-left me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="bi bi-send-fill me-2"></i> Envoyer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['old_input'])) unset($_SESSION['old_input']); ?>

<script>
const fileInput = document.getElementById('image');
const previewContainer = document.getElementById('imagePreview');
const previewImg = document.getElementById('previewImg');
const removeBtn = document.getElementById('removeImage');

fileInput.addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { previewImg.src = e.target.result; previewContainer.style.display = 'block'; };
        reader.readAsDataURL(this.files[0]);
    }
});
removeBtn.addEventListener('click', function () {
    fileInput.value = ''; previewContainer.style.display = 'none';
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>