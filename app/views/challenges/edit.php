<?php
// app/views/challenges/edit.php
$title = "Modifier le défi - ChallengeHub";
$active_page = "";

$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

ob_start();
?>

<div class="form-container">
    <div class="form-header">
        <h1><i class="fas fa-edit"></i> Modifier le défi</h1>
        <p>Mettez à jour votre défi</p>
    </div>

    <form action="index.php?action=updateChallenge" method="POST" enctype="multipart/form-data" class="challenge-form" autocomplete="off">
        <?= CSRF::field() ?>
        <input type="hidden" name="challenge_id" value="<?= $challenge['id'] ?>">

        <!-- Titre -->
        <div class="form-group">
            <label for="title"><i class="fas fa-heading"></i> Titre du défi <span class="required">*</span></label>
            <input type="text" id="title" name="title"
                   value="<?= htmlspecialchars($old_input['title'] ?? $challenge['title']) ?>"
                   required minlength="5" maxlength="200">
            <small class="form-hint">Minimum 5 caractères, maximum 200</small>
        </div>

        <!-- Catégorie -->
        <div class="form-group">
            <label for="category"><i class="fas fa-tag"></i> Catégorie <span class="required">*</span></label>
            <select id="category" name="category" required>
                <option value="" disabled>Choisissez une catégorie</option>
                <?php
                $current_cat = $old_input['category'] ?? $challenge['category'];
                $cats = ['Design','Code','Art','Écriture','Photo','Vidéo','Musique','Autre'];
                $labels = ['Design','Code / Programmation','Art / Illustration','Écriture','Photographie','Vidéo','Musique','Autre'];
                foreach($cats as $i => $cat):
                ?>
                <option value="<?= $cat ?>" <?= $current_cat === $cat ? 'selected' : '' ?>><?= $labels[$i] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Description <span class="required">*</span></label>
            <textarea id="description" name="description" rows="6" required minlength="20"><?= htmlspecialchars($old_input['description'] ?? $challenge['description']) ?></textarea>
            <small class="form-hint">Minimum 20 caractères</small>
        </div>

        <!-- Date limite -->
        <div class="form-group">
            <label for="deadline"><i class="fas fa-calendar-alt"></i> Date limite (optionnelle)</label>
            <?php
            $deadline_val = $old_input['deadline'] ?? '';
            if(empty($deadline_val) && !empty($challenge['deadline'])) {
                $ts = strtotime($challenge['deadline']);
                if($ts && $ts > mktime(0,0,0,1,1,2000)) {
                    $deadline_val = date('Y-m-d', $ts);
                }
            }
            ?>
            <input type="date" id="deadline" name="deadline"
                   value="<?= htmlspecialchars($deadline_val) ?>">
            <small class="form-hint">Laissez vide pour un défi sans limite de temps</small>
        </div>

        <!-- Image actuelle -->
        <?php if(!empty($challenge['image'])): ?>
        <div class="form-group">
            <label><i class="fas fa-image"></i> Image actuelle</label>
            <div class="current-image-preview">
                <img src="public/<?= htmlspecialchars($challenge['image']) ?>" alt="Image actuelle">
                <span>Image actuelle — uploadez une nouvelle pour la remplacer</span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Nouvelle image -->
        <div class="form-group">
            <label for="image"><i class="fas fa-upload"></i> <?= !empty($challenge['image']) ? 'Changer l\'image (optionnel)' : 'Image d\'illustration (optionnelle)' ?></label>
            <div class="file-upload">
                <input type="file" id="image" name="image"
                       accept="image/jpeg,image/png,image/gif,image/webp"
                       onchange="previewImage(event)">
                <div class="file-upload-label">
                    <div class="upload-icon-wrap"><i class="fas fa-cloud-upload-alt"></i></div>
                    <span class="upload-text">Cliquez ou <strong>glissez</strong> une image ici</span>
                    <small>JPG, PNG, GIF, WEBP &mdash; max 5 Mo</small>
                </div>
            </div>
            <div id="image-preview" class="image-preview" style="display: none;">
                <img id="preview-img" src="#" alt="Aperçu">
                <button type="button" class="remove-image" onclick="removeImage()">
                    <i class="fas fa-times"></i> Supprimer
                </button>
            </div>
        </div>

        <!-- Boutons -->
        <div class="form-actions">
            <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="btn-cancel">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

<style>
.form-container { max-width: 720px; margin: 0 auto; background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 30px rgba(0,0,0,0.08); }
.form-header { text-align: center; margin-bottom: 32px; }
.form-header h1 { font-size: 1.6rem; color: #222; margin-bottom: 6px; }
.form-header h1 i { color: #667eea; }
.form-header p { color: #888; }
.form-group { margin-bottom: 22px; }
.form-group label { display: flex; align-items: center; gap: 8px; font-weight: 600; color: #333; margin-bottom: 8px; }
.form-group label i { color: #667eea; }
.required { color: #e74c3c; }
.form-group input[type="text"],
.form-group input[type="date"],
.form-group select,
.form-group textarea { width: 100%; padding: 12px 16px; border: 2px solid #e8e8e8; border-radius: 10px; font-size: 0.95rem; font-family: inherit; transition: border-color 0.3s; box-sizing: border-box; }
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { outline: none; border-color: #667eea; }
.form-group textarea { resize: vertical; min-height: 120px; }
.form-hint { font-size: 0.8rem; color: #999; margin-top: 4px; display: block; }
.current-image-preview { display: flex; align-items: center; gap: 14px; padding: 12px; background: #f8f7ff; border-radius: 10px; border: 2px solid #e8e8e8; }
.current-image-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
.current-image-preview span { font-size: 0.85rem; color: #666; }
.file-upload { position: relative; border: 2px dashed #c4b5fd; border-radius: 16px; background: #f5f3ff; transition: all 0.3s; overflow: hidden; }
.file-upload:hover { border-color: #667eea; background: #ede9fe; }
.file-upload input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.file-upload-label { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 28px 20px; gap: 8px; cursor: pointer; }
.upload-icon-wrap { width: 52px; height: 52px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: white; margin-bottom: 4px; }
.upload-text { font-size: 0.92rem; color: #555; }
.upload-text strong { color: #667eea; }
.file-upload-label small { font-size: 0.78rem; color: #999; }
.image-preview { margin-top: 12px; }
.image-preview img { max-width: 100%; max-height: 200px; border-radius: 12px; border: 2px solid #e0e0e0; display: block; }
.remove-image { display: inline-flex; align-items: center; gap: 6px; margin-top: 8px; padding: 6px 14px; background: #fee2e2; color: #dc2626; border: none; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.remove-image:hover { background: #fecaca; }
.form-actions { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-top: 30px; padding-top: 24px; border-top: 2px solid #f0f0f0; }
.btn-cancel { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border: 2px solid #e0e0e0; border-radius: 12px; color: #666; text-decoration: none; font-weight: 600; transition: all 0.2s; }
.btn-cancel:hover { border-color: #999; color: #333; background: #f5f5f5; }
.btn-submit { display: inline-flex; align-items: center; gap: 10px; padding: 14px 28px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(102,126,234,0.35); }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102,126,234,0.5); }
</style>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
            document.querySelector('.file-upload-label').style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
}
function removeImage() {
    document.getElementById('image').value = '';
    document.getElementById('image-preview').style.display = 'none';
    document.querySelector('.file-upload-label').style.display = 'flex';
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>