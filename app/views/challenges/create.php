<?php
$title = "Créer un défi - ChallengeHub";
$active_page = "";
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-white"
                 style="width:48px;height:48px;background:linear-gradient(135deg,#667eea,#764ba2);">
                <i class="fas fa-plus fs-5"></i>
            </div>
            <div>
                <h1 class="h4 fw-bold mb-0">Créer un défi</h1>
                <p class="text-muted small mb-0">Publiez un nouveau défi créatif</p>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <form action="index.php?action=createChallenge" method="POST" enctype="multipart/form-data">
                    <?= CSRF::field() ?>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            <i class="fas fa-heading me-1 text-primary"></i> Titre du défi <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= htmlspecialchars($old_input['title'] ?? '') ?>"
                               placeholder="Ex: Créez un logo minimaliste..." required minlength="5" maxlength="200">
                        <div class="form-text">Entre 5 et 200 caractères</div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label fw-semibold">
                            <i class="fas fa-tag me-1 text-primary"></i> Catégorie <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="" disabled selected>Choisissez une catégorie</option>
                            <?php
                            $cats = ['Design','Code','Art','Écriture','Photo','Vidéo','Musique','Autre'];
                            $labels = ['🎨 Design','💻 Code / Programmation','🖼️ Art / Illustration','✍️ Écriture','📷 Photographie','🎬 Vidéo','🎵 Musique','📦 Autre'];
                            $current_cat = $old_input['category'] ?? '';
                            foreach ($cats as $i => $cat):
                            ?>
                            <option value="<?= $cat ?>" <?= $current_cat === $cat ? 'selected' : '' ?>><?= $labels[$i] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">
                            <i class="fas fa-align-left me-1 text-primary"></i> Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="5" required minlength="20"
                                  placeholder="Décrivez votre défi en détail..."><?= htmlspecialchars($old_input['description'] ?? '') ?></textarea>
                        <div class="form-text">Minimum 20 caractères</div>
                    </div>

                    <div class="mb-3">
                        <label for="deadline" class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt me-1 text-primary"></i> Date limite
                            <span class="badge bg-secondary fw-normal ms-1">Optionnel</span>
                        </label>
                        <input type="date" class="form-control" id="deadline" name="deadline"
                               value="<?= htmlspecialchars($old_input['deadline'] ?? '') ?>">
                        <div class="form-text">Laissez vide pour un défi sans limite</div>
                    </div>

                    <div class="mb-4">
                        <label for="image" class="form-label fw-semibold">
                            <i class="fas fa-image me-1 text-primary"></i> Image d'illustration
                            <span class="badge bg-secondary fw-normal ms-1">Optionnel</span>
                        </label>
                        <input type="file" class="form-control" id="image" name="image"
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               onchange="previewImage(event)">
                        <div class="form-text">JPG, PNG, GIF, WEBP — max 5 Mo</div>
                        <div id="image-preview" class="mt-2" style="display:none;">
                            <img id="preview-img" src="#" alt="Aperçu"
                                 class="rounded-3 border" style="max-height:200px; max-width:100%;">
                            <br>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeImage()">
                                <i class="fas fa-times me-1"></i> Supprimer l'image
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <a href="index.php?action=home" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="fas fa-paper-plane me-2"></i> Publier le défi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
function removeImage() {
    document.getElementById('image').value = '';
    document.getElementById('image-preview').style.display = 'none';
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>