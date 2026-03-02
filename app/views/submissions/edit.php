<?php
$title = "Modifier ma participation - ChallengeHub";
$active_page = "";
ob_start();
?>

<div class="submission-create-page">

    <div class="breadcrumb">
        <a href="index.php?action=home"><i class="fas fa-home"></i> Accueil</a>
        <span>/</span>
        <a href="index.php?action=showSubmission&id=<?= $submission['id'] ?>">Ma participation</a>
        <span>/</span>
        <span>Modifier</span>
    </div>

    <div class="form-container">
        <div class="form-header">
            <div class="form-icon"><i class="fas fa-edit"></i></div>
            <h1>Modifier ma participation</h1>
            <p>Mettez à jour votre soumission</p>
        </div>

        <form action="index.php?action=updateSubmission" method="POST" enctype="multipart/form-data" class="submission-form">
            <?= CSRF::field() ?>
            <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">

            <div class="form-group">
                <label for="description">
                    <i class="fas fa-align-left"></i> Description *
                </label>
                <textarea id="description" name="description" rows="6" required><?= htmlspecialchars($_SESSION['old_input']['description'] ?? $submission['description']) ?></textarea>
            </div>

            <!-- Image actuelle -->
            <?php if(!empty($submission['image'])): ?>
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Image actuelle</label>
                    <div class="current-image">
                        <img src="public/<?= htmlspecialchars($submission['image']) ?>" alt="Image actuelle">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="image">
                    <i class="fas fa-upload"></i> Changer l'image (optionnel)
                </label>
                <div class="file-upload-area" id="fileUploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Glissez une image ici ou <span>cliquez pour choisir</span></p>
                    <small>JPEG, PNG, GIF, WEBP — Max 5Mo</small>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                <div class="image-preview" id="imagePreview" style="display:none;">
                    <img id="previewImg" src="" alt="Aperçu">
                    <button type="button" class="remove-image" id="removeImage"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <div class="form-group">
                <label for="link">
                    <i class="fas fa-link"></i> Lien externe (optionnel)
                </label>
                <input type="url" id="link" name="link"
                       placeholder="https://..."
                       value="<?= htmlspecialchars($_SESSION['old_input']['link'] ?? $submission['link'] ?? '') ?>">
            </div>

            <div class="form-actions">
                <a href="index.php?action=showSubmission&id=<?= $submission['id'] ?>" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i> Annuler
                </a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<?php if(isset($_SESSION['old_input'])): ?>
    <?php unset($_SESSION['old_input']); ?>
<?php endif; ?>

<style>
.submission-create-page { max-width: 700px; margin: 0 auto; }
.breadcrumb { display:flex; align-items:center; gap:8px; font-size:0.9rem; color:#888; margin-bottom:24px; }
.breadcrumb a { color:#667eea; text-decoration:none; }
.form-container { background:white; border-radius:20px; padding:40px; box-shadow:0 4px 30px rgba(0,0,0,0.08); }
.form-header { text-align:center; margin-bottom:30px; }
.form-icon { width:64px; height:64px; background:linear-gradient(135deg,#667eea,#764ba2); border-radius:18px; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; font-size:1.6rem; color:white; }
.form-header h1 { font-size:1.5rem; color:#222; margin-bottom:6px; }
.form-header p { color:#888; }
.form-group { margin-bottom:22px; }
.form-group label { display:flex; align-items:center; gap:8px; font-weight:600; color:#333; margin-bottom:8px; }
.form-group label i { color:#667eea; }
.form-group input, .form-group textarea { width:100%; padding:12px 16px; border:2px solid #e8e8e8; border-radius:10px; font-size:0.95rem; font-family:inherit; transition:border-color 0.3s; resize:vertical; }
.form-group input:focus, .form-group textarea:focus { outline:none; border-color:#667eea; }
.current-image img { max-width:200px; border-radius:10px; border:2px solid #e8e8e8; }
.file-upload-area { border:2px dashed #d0d0d0; border-radius:12px; padding:24px; text-align:center; cursor:pointer; position:relative; transition:all 0.3s; }
.file-upload-area:hover { border-color:#667eea; background:#f8f7ff; }
.file-upload-area i { font-size:1.8rem; color:#bbb; display:block; margin-bottom:8px; }
.file-upload-area p { color:#666; font-size:0.88rem; }
.file-upload-area span { color:#667eea; font-weight:600; }
.file-upload-area small { color:#aaa; font-size:0.78rem; }
.file-upload-area input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.image-preview { position:relative; display:inline-block; margin-top:10px; }
.image-preview img { max-width:100%; max-height:200px; border-radius:10px; }
.remove-image { position:absolute; top:-8px; right:-8px; background:#ef4444; color:white; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; font-size:0.7rem; display:flex; align-items:center; justify-content:center; }
.form-actions { display:flex; justify-content:space-between; gap:16px; margin-top:8px; }
.btn-cancel { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; border:2px solid #e0e0e0; color:#666; border-radius:10px; text-decoration:none; font-weight:600; transition:all 0.3s; }
.btn-cancel:hover { border-color:#aaa; color:#333; }
.btn-submit { display:inline-flex; align-items:center; gap:8px; padding:12px 28px; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; border-radius:10px; font-size:1rem; font-weight:700; cursor:pointer; transition:all 0.3s; }
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(102,126,234,0.4); }
</style>

<script>
const fileInput = document.getElementById('image');
const previewContainer = document.getElementById('imagePreview');
const previewImg = document.getElementById('previewImg');
const removeBtn = document.getElementById('removeImage');
const uploadArea = document.getElementById('fileUploadArea');
fileInput.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { previewImg.src = e.target.result; previewContainer.style.display = 'block'; uploadArea.style.display = 'none'; };
        reader.readAsDataURL(this.files[0]);
    }
});
removeBtn.addEventListener('click', function() { fileInput.value = ''; previewContainer.style.display = 'none'; uploadArea.style.display = 'block'; });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>