<?php
$title = "Modifier ma participation - ChallengeHub";
$active_page = "";
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?action=home" class="text-decoration-none"><i class="fas fa-home me-1"></i>Accueil</a></li>
                <li class="breadcrumb-item"><a href="index.php?action=showSubmission&id=<?= $submission['id'] ?>" class="text-decoration-none">Ma participation</a></li>
                <li class="breadcrumb-item active">Modifier</li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <div class="mx-auto mb-3 rounded-3 d-flex align-items-center justify-content-center text-white"
                         style="width:48px;height:48px;background:linear-gradient(135deg,#667eea,#764ba2);">
                        <i class="fas fa-edit fs-5"></i>
                    </div>
                    <h1 class="h5 fw-bold mb-1">Modifier ma participation</h1>
                    <p class="text-muted small">Mettez à jour votre soumission</p>
                </div>

                <form action="index.php?action=updateSubmission" method="POST" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">
                            <i class="fas fa-align-left me-1 text-primary"></i> Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="5" required><?= htmlspecialchars($_SESSION['old_input']['description'] ?? $submission['description']) ?></textarea>
                    </div>

                    <?php if (!empty($submission['image'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="fas fa-image me-1 text-primary"></i> Image actuelle</label>
                        <div class="p-2 bg-light rounded-3">
                            <img src="public/<?= htmlspecialchars($submission['image']) ?>" class="rounded-2" style="max-height:120px;">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="image" class="form-label fw-semibold">
                            <i class="fas fa-upload me-1 text-primary"></i> Changer l'image
                            <span class="badge bg-secondary fw-normal ms-1">Optionnel</span>
                        </label>
                        <input type="file" class="form-control" id="image" name="image"
                               accept="image/*" onchange="previewImage(event)">
                        <div id="imagePreview" class="mt-2" style="display:none;">
                            <img id="previewImg" src="" class="rounded-3 border" style="max-height:150px;max-width:100%;">
                            <br>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeImage">
                                <i class="fas fa-times me-1"></i> Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="link" class="form-label fw-semibold">
                            <i class="fas fa-link me-1 text-primary"></i> Lien externe
                            <span class="badge bg-secondary fw-normal ms-1">Optionnel</span>
                        </label>
                        <input type="url" class="form-control" id="link" name="link"
                               placeholder="https://..."
                               value="<?= htmlspecialchars($_SESSION['old_input']['link'] ?? $submission['link'] ?? '') ?>">
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <a href="index.php?action=showSubmission&id=<?= $submission['id'] ?>" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="fas fa-save me-2"></i> Enregistrer
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
removeBtn.addEventListener('click', function () { fileInput.value = ''; previewContainer.style.display = 'none'; });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>