<?php
$title = "Modifier le défi - ChallengeHub";
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
                <i class="fas fa-edit fs-5"></i>
            </div>
            <div>
                <h1 class="h4 fw-bold mb-0">Modifier le défi</h1>
                <p class="text-muted small mb-0">Mettez à jour votre défi</p>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <form action="index.php?action=updateChallenge" method="POST" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="challenge_id" value="<?= $challenge['id'] ?>">

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            <i class="fas fa-heading me-1 text-primary"></i> Titre <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= htmlspecialchars($old_input['title'] ?? $challenge['title']) ?>"
                               required minlength="5" maxlength="200">
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label fw-semibold">
                            <i class="fas fa-tag me-1 text-primary"></i> Catégorie <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="category" name="category" required>
                            <?php
                            $current_cat = $old_input['category'] ?? $challenge['category'];
                            $cats = ['Design','Code','Art','Écriture','Photo','Vidéo','Musique','Autre'];
                            $labels = ['🎨 Design','💻 Code / Programmation','🖼️ Art / Illustration','✍️ Écriture','📷 Photographie','🎬 Vidéo','🎵 Musique','📦 Autre'];
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
                                  rows="5" required><?= htmlspecialchars($old_input['description'] ?? $challenge['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="deadline" class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt me-1 text-primary"></i> Date limite
                        </label>
                        <?php
                        $deadline_val = $old_input['deadline'] ?? '';
                        if (empty($deadline_val) && !empty($challenge['deadline'])) {
                            $ts = strtotime($challenge['deadline']);
                            if ($ts && $ts > mktime(0,0,0,1,1,2000)) $deadline_val = date('Y-m-d', $ts);
                        }
                        ?>
                        <input type="date" class="form-control" id="deadline" name="deadline"
                               value="<?= htmlspecialchars($deadline_val) ?>">
                    </div>

                    <?php if (!empty($challenge['image'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="fas fa-image me-1 text-primary"></i> Image actuelle</label>
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                            <img src="public/<?= htmlspecialchars($challenge['image']) ?>" class="rounded-2" style="width:80px;height:60px;object-fit:cover;">
                            <small class="text-muted">Uploadez une nouvelle image pour la remplacer</small>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label for="image" class="form-label fw-semibold">
                            <i class="fas fa-upload me-1 text-primary"></i> <?= !empty($challenge['image']) ? 'Changer l\'image' : 'Image d\'illustration' ?>
                        </label>
                        <input type="file" class="form-control" id="image" name="image"
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               onchange="previewImage(event)">
                        <div id="image-preview" class="mt-2" style="display:none;">
                            <img id="preview-img" src="#" class="rounded-3 border" style="max-height:200px; max-width:100%;">
                            <br>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeImage()">
                                <i class="fas fa-times me-1"></i> Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="btn btn-outline-secondary rounded-pill px-4">
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