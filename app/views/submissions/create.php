<?php
$title = "Participer - " . htmlspecialchars($challenge['title']);
$active_page = "";
ob_start();
?>

<div class="submission-create-page">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php?action=home"><i class="fas fa-home"></i> Accueil</a>
        <span>/</span>
        <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>"><?= htmlspecialchars($challenge['title']) ?></a>
        <span>/</span>
        <span>Participer</span>
    </div>

    <!-- En-tête du défi -->
    <div class="challenge-info-banner">
        <?php if(!empty($challenge['image'])): ?>
            <div class="banner-image">
                <img src="public/<?= htmlspecialchars($challenge['image']) ?>" alt="<?= htmlspecialchars($challenge['title']) ?>">
            </div>
        <?php endif; ?>
        <div class="banner-content">
            <span class="category-tag"><i class="fas fa-tag"></i> <?= htmlspecialchars($challenge['category']) ?></span>
            <h2><?= htmlspecialchars($challenge['title']) ?></h2>
            <p><?= htmlspecialchars(substr($challenge['description'], 0, 200)) ?>...</p>
        </div>
    </div>

    <!-- Formulaire de participation -->
    <div class="form-container">
        <div class="form-header">
            <div class="form-icon"><i class="fas fa-paper-plane"></i></div>
            <h1>Soumettre ma participation</h1>
            <p>Partagez votre création pour ce défi</p>
        </div>

        <form action="index.php?action=createSubmission" method="POST" enctype="multipart/form-data" class="submission-form">
            <input type="hidden" name="challenge_id" value="<?= $challenge['id'] ?>">

            <!-- Description -->
            <div class="form-group">
                <label for="description">
                    <i class="fas fa-align-left"></i> Description de votre participation *
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Décrivez votre création, votre démarche, les outils utilisés..."
                    required
                ><?= htmlspecialchars($_SESSION['old_input']['description'] ?? '') ?></textarea>
                <span class="field-hint">Minimum 20 caractères, maximum 2000.</span>
            </div>

            <!-- Image -->
            <div class="form-group">
                <label for="image">
                    <i class="fas fa-image"></i> Image de votre création (optionnel)
                </label>
                <div class="file-upload-area" id="fileUploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Glissez une image ici ou <span>cliquez pour choisir</span></p>
                    <small>JPEG, PNG, GIF, WEBP — Max 5Mo</small>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                <div class="image-preview" id="imagePreview" style="display:none;">
                    <img id="previewImg" src="" alt="Aperçu">
                    <button type="button" class="remove-image" id="removeImage">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Lien externe -->
            <div class="form-group">
                <label for="link">
                    <i class="fas fa-link"></i> Lien externe (optionnel)
                </label>
                <input
                    type="url"
                    id="link"
                    name="link"
                    placeholder="https://github.com/... ou https://..."
                    value="<?= htmlspecialchars($_SESSION['old_input']['link'] ?? '') ?>"
                >
                <span class="field-hint">Un lien vers votre projet, GitHub, Dribbble, etc.</span>
            </div>

            <!-- Boutons -->
            <div class="form-actions">
                <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i> Annuler
                </a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Envoyer ma participation
                </button>
            </div>
        </form>
    </div>
</div>

<?php if(isset($_SESSION['old_input'])): ?>
    <?php unset($_SESSION['old_input']); ?>
<?php endif; ?>

<style>
.submission-create-page { max-width: 750px; margin: 0 auto; }

.breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.9rem; color: #888; margin-bottom: 24px;
}
.breadcrumb a { color: #667eea; text-decoration: none; }
.breadcrumb a:hover { text-decoration: underline; }

.challenge-info-banner {
    display: flex; gap: 20px; align-items: flex-start;
    background: white; border-radius: 16px; padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    margin-bottom: 30px; border: 1px solid #f0f0f0;
}

.banner-image img {
    width: 120px; height: 90px; object-fit: cover; border-radius: 10px;
}

.banner-content { flex: 1; }

.category-tag {
    display: inline-block; padding: 3px 12px;
    background: #ede9fe; color: #667eea;
    border-radius: 20px; font-size: 0.78rem; font-weight: 600;
    margin-bottom: 8px;
}

.banner-content h2 { font-size: 1.1rem; color: #222; margin-bottom: 6px; }
.banner-content p  { font-size: 0.88rem; color: #666; line-height: 1.5; }

.form-container {
    background: white; border-radius: 20px; padding: 40px;
    box-shadow: 0 4px 30px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;
}

.form-header { text-align: center; margin-bottom: 35px; }

.form-icon {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 18px; display: flex; align-items: center;
    justify-content: center; margin: 0 auto 16px;
    font-size: 1.6rem; color: white;
}

.form-header h1 { font-size: 1.5rem; color: #222; margin-bottom: 6px; }
.form-header p  { color: #888; font-size: 0.95rem; }

.form-group { margin-bottom: 24px; }

.form-group label {
    display: flex; align-items: center; gap: 8px;
    font-weight: 600; color: #333; margin-bottom: 8px; font-size: 0.95rem;
}

.form-group label i { color: #667eea; }

.form-group input[type="text"],
.form-group input[type="url"],
.form-group textarea {
    width: 100%; padding: 12px 16px;
    border: 2px solid #e8e8e8; border-radius: 10px;
    font-size: 0.95rem; font-family: inherit;
    transition: border-color 0.3s; resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none; border-color: #667eea;
}

.field-hint { font-size: 0.8rem; color: #aaa; margin-top: 5px; display: block; }

/* Upload zone */
.file-upload-area {
    border: 2px dashed #d0d0d0; border-radius: 12px;
    padding: 30px; text-align: center;
    cursor: pointer; transition: all 0.3s; position: relative;
}

.file-upload-area:hover { border-color: #667eea; background: #f8f7ff; }

.file-upload-area i { font-size: 2rem; color: #bbb; margin-bottom: 10px; display: block; }
.file-upload-area p { color: #666; font-size: 0.9rem; }
.file-upload-area span { color: #667eea; font-weight: 600; }
.file-upload-area small { color: #aaa; font-size: 0.8rem; }
.file-upload-area input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}

.image-preview {
    position: relative; display: inline-block; margin-top: 12px;
}
.image-preview img {
    max-width: 100%; max-height: 250px; border-radius: 10px;
    border: 2px solid #e8e8e8;
}
.remove-image {
    position: absolute; top: -8px; right: -8px;
    background: #ef4444; color: white; border: none;
    border-radius: 50%; width: 26px; height: 26px;
    cursor: pointer; font-size: 0.75rem; display: flex;
    align-items: center; justify-content: center;
}

.form-actions {
    display: flex; justify-content: space-between; align-items: center;
    gap: 16px; margin-top: 10px;
}

.btn-cancel {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 24px; border: 2px solid #e0e0e0;
    color: #666; border-radius: 10px; text-decoration: none;
    font-weight: 600; transition: all 0.3s;
}
.btn-cancel:hover { border-color: #aaa; color: #333; }

.btn-submit {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 32px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white; border: none; border-radius: 10px;
    font-size: 1rem; font-weight: 700; cursor: pointer;
    transition: all 0.3s;
}
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102,126,234,0.4);
}

@media (max-width: 600px) {
    .form-container { padding: 24px 16px; }
    .challenge-info-banner { flex-direction: column; }
    .banner-image img { width: 100%; height: 160px; }
    .form-actions { flex-direction: column; }
    .btn-submit, .btn-cancel { width: 100%; justify-content: center; }
}
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
        reader.onload = e => {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'block';
            uploadArea.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    }
});

removeBtn.addEventListener('click', function() {
    fileInput.value = '';
    previewContainer.style.display = 'none';
    uploadArea.style.display = 'block';
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>