<?php
$title       = "Créer un défi — ChallengeHub";
$active_page = "create";
$old         = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
ob_start();
?>

<div class="cr-wrap">

  <!-- ══ LEFT : FORM ══ -->
  <div class="cr-main">

    <!-- Header -->
    <div class="cr-head">
      <div class="cr-head-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
      </div>
      <div>
        <h1 class="cr-head-title">Créer un défi</h1>
        <p class="cr-head-sub">Proposez un défi à la communauté ChallengeHub</p>
      </div>
    </div>

    <form action="index.php?action=createChallenge" method="POST" enctype="multipart/form-data" class="cr-form" id="cr-form">
      <?= CSRF::field() ?>

      <!-- Titre -->
      <div class="cr-field">
        <label class="cr-label" for="title">
          Titre du défi <span class="cr-req">*</span>
        </label>
        <input type="text"
               id="title"
               name="title"
               class="cr-input"
               value="<?= htmlspecialchars($old['title'] ?? '') ?>"
               placeholder="Donnez un titre à votre défi…"
               required
               autocomplete="off">
        <span class="cr-hint">Soyez clair et accrocheur</span>
      </div>

      <!-- Catégorie -->
      <div class="cr-field">
        <label class="cr-label" for="category">
          Catégorie <span class="cr-req">*</span>
        </label>
        <div class="cr-select-wrap">
          <select id="category" name="category" class="cr-select" required>
            <option value="" disabled <?= empty($old['category'])?'selected':'' ?>>Choisir une catégorie</option>
            <?php
            $cats = ['Design','Code','Art','Écriture','Photo','Vidéo','Musique','Autre'];
            foreach($cats as $c):
              $sel = ($old['category']??'')===$c ? 'selected' : '';
            ?>
            <option value="<?= $c ?>" <?= $sel ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
          <svg class="cr-select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
      </div>

      <!-- Description — libre, sans limite -->
      <div class="cr-field">
        <label class="cr-label" for="description">
          Description <span class="cr-req">*</span>
        </label>
        <textarea id="description"
                  name="description"
                  class="cr-textarea"
                  rows="8"
                  placeholder="Décrivez votre défi librement : contexte, règles, objectifs, ce que vous attendez des participants…"
                  required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        <div class="cr-hint-row">
          <span class="cr-hint">Écrivez autant que vous voulez — aucune limite</span>
          <span class="cr-char-count" id="char-count">0 caractères</span>
        </div>
      </div>

      <!-- Date limite — optionnelle, ouverte si vide -->
      <div class="cr-field">
        <label class="cr-label" for="deadline">
          Date limite
          <span class="cr-badge-opt">optionnel</span>
        </label>
        <div class="cr-date-wrap">
          <div class="cr-date-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <input type="date"
                 id="deadline"
                 name="deadline"
                 class="cr-date-input"
                 value="<?= htmlspecialchars($old['deadline'] ?? '') ?>">
          <button type="button" class="cr-date-clear" id="cr-date-clear" onclick="clearDeadline()" title="Supprimer la date" style="display:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <!-- Indicateur ouvert/fermé -->
        <div class="cr-deadline-status" id="cr-deadline-status">
          <div class="cr-status-open">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Défi <strong>ouvert indéfiniment</strong> — aucune date limite fixée
          </div>
        </div>
      </div>

      <!-- Image -->
      <div class="cr-field">
        <label class="cr-label">
          Image d'illustration
          <span class="cr-badge-opt">optionnel</span>
        </label>
        <div class="cr-upload" id="cr-upload"
             ondragover="event.preventDefault();this.classList.add('cr-upload--over')"
             ondragleave="this.classList.remove('cr-upload--over')"
             ondrop="handleDrop(event)">
          <input type="file" id="image" name="image"
                 accept="image/jpeg,image/png,image/gif,image/webp"
                 onchange="previewImg(event)">
          <div class="cr-upload-content" id="cr-upload-content">
            <div class="cr-upload-ico">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <p class="cr-upload-label">Glissez une image ou <span class="cr-upload-browse">parcourir</span></p>
            <p class="cr-upload-types">JPG · PNG · GIF · WEBP — max 5 Mo</p>
          </div>
          <div class="cr-preview" id="cr-preview" style="display:none">
            <img id="cr-preview-img" src="#" alt="">
            <button type="button" class="cr-preview-del" onclick="removeImg()">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              Supprimer
            </button>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="cr-actions">
        <a href="index.php?action=home" class="cr-btn-cancel">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Annuler
        </a>
        <button type="submit" class="cr-btn-submit" id="cr-submit">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
          Lancer le défi
        </button>
      </div>

    </form>
  </div>

  <!-- ══ RIGHT : TIPS + APERÇU ══ -->
  <aside class="cr-aside">
    <div class="cr-tips">
      <div class="cr-tips-head">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Conseils
      </div>
      <div class="cr-tip-list">
        <div class="cr-tip">
          <div class="cr-tip-n">01</div>
          <div>
            <strong>Titre percutant</strong>
            <p>Quelques mots qui donnent envie de participer immédiatement</p>
          </div>
        </div>
        <div class="cr-tip">
          <div class="cr-tip-n">02</div>
          <div>
            <strong>Description libre</strong>
            <p>Pas de limite — expliquez le contexte, les règles, les exemples</p>
          </div>
        </div>
        <div class="cr-tip">
          <div class="cr-tip-n">03</div>
          <div>
            <strong>Date optionnelle</strong>
            <p>Sans date = défi ouvert pour toujours. Avec date = compétition limitée</p>
          </div>
        </div>
        <div class="cr-tip">
          <div class="cr-tip-n">04</div>
          <div>
            <strong>Image inspirante</strong>
            <p>Une bonne illustration multiplie les participations</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Preview card -->
    <div class="cr-preview-card" id="cr-card-preview">
      <div class="cr-pc-label">Aperçu de la carte</div>
      <div class="cr-pc-img" id="cr-pc-img">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#c4b5fd" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      </div>
      <div class="cr-pc-body">
        <span class="cr-pc-cat" id="cr-pc-cat">Catégorie</span>
        <p class="cr-pc-title" id="cr-pc-title">Titre du défi…</p>
        <div class="cr-pc-footer">
          <span class="cr-pc-author">
            <div class="cr-pc-av"><?= strtoupper(substr($_SESSION['username']??'?',0,1)) ?></div>
            <?= htmlspecialchars($_SESSION['username']??'Vous') ?>
          </span>
          <span class="cr-pc-date" id="cr-pc-date">Ouvert ∞</span>
        </div>
      </div>
    </div>
  </aside>

</div>

<style>
.cr-wrap, .cr-wrap * { font-family: 'Inter', system-ui, sans-serif; box-sizing: border-box; }

:root {
  --cr-indigo: #6366f1;
  --cr-violet: #8b5cf6;
  --cr-ink:    #111827;
  --cr-sub:    #4b5563;
  --cr-faint:  #9ca3af;
  --cr-line:   #e5e7eb;
  --cr-bg:     #f9fafb;
  --cr-white:  #fff;
  --cr-green:  #10b981;
  --cr-amber:  #f59e0b;
  --cr-r:      14px;
  --cr-sh:     0 1px 12px rgba(0,0,0,.07);
  --cr-sh-md:  0 4px 24px rgba(99,102,241,.18);
}

/* ─── Layout ─── */
.cr-wrap { display:grid; grid-template-columns:1fr 320px; gap:28px; max-width:960px; margin:0 auto; align-items:start; }

/* ─── Main card ─── */
.cr-main { background:var(--cr-white); border-radius:20px; border:1px solid var(--cr-line); box-shadow:var(--cr-sh); overflow:hidden; }

/* ─── Head ─── */
.cr-head { display:flex; align-items:center; gap:16px; padding:26px 28px 22px;
  background:linear-gradient(135deg,#1e1b4b,#312e81,#4c1d95);
  color:#fff; }
.cr-head-icon { width:52px; height:52px; border-radius:14px;
  background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.cr-head-title { font-size:1.4rem; font-weight:900; margin:0 0 3px; letter-spacing:-.3px; }
.cr-head-sub   { font-size:.83rem; opacity:.7; margin:0; }

/* ─── Form ─── */
.cr-form { padding:28px; display:flex; flex-direction:column; gap:22px; }

/* ─── Field ─── */
.cr-field { display:flex; flex-direction:column; gap:7px; }
.cr-label { font-size:.87rem; font-weight:700; color:var(--cr-ink); display:flex; align-items:center; gap:8px; }
.cr-req   { color:var(--cr-indigo); font-size:.9rem; }
.cr-badge-opt { font-size:.68rem; font-weight:600; background:#ede9fe; color:var(--cr-indigo); padding:2px 8px; border-radius:20px; }

/* ─── Inputs ─── */
.cr-input,
.cr-select,
.cr-textarea {
  width:100%; padding:12px 15px;
  border:2px solid var(--cr-line); border-radius:var(--cr-r);
  font-size:.93rem; color:var(--cr-ink); background:var(--cr-bg);
  outline:none; transition:border-color .2s, background .2s;
  font-family:inherit;
}
.cr-input:focus,
.cr-select:focus,
.cr-textarea:focus { border-color:var(--cr-indigo); background:var(--cr-white); }
.cr-input::placeholder,
.cr-textarea::placeholder { color:var(--cr-faint); }
.cr-textarea { resize:vertical; min-height:160px; line-height:1.6; }
.cr-hint { font-size:.75rem; color:var(--cr-faint); }
.cr-hint-row { display:flex; justify-content:space-between; align-items:center; }
.cr-char-count { font-size:.74rem; color:var(--cr-faint); font-weight:500; }

/* Select wrapper */
.cr-select-wrap { position:relative; }
.cr-select { appearance:none; -webkit-appearance:none; cursor:pointer; padding-right:38px; }
.cr-select-arrow { position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none; color:var(--cr-faint); }

/* ─── Date ─── */
.cr-date-wrap { display:flex; align-items:center; gap:10px; background:var(--cr-bg);
  border:2px solid var(--cr-line); border-radius:var(--cr-r); padding:0 14px;
  transition:border-color .2s; }
.cr-date-wrap:focus-within { border-color:var(--cr-indigo); background:var(--cr-white); }
.cr-date-icon { color:var(--cr-faint); flex-shrink:0; }
.cr-date-input { flex:1; border:none; background:transparent; font-size:.93rem;
  color:var(--cr-ink); outline:none; padding:12px 0; font-family:inherit; }
.cr-date-clear { border:none; background:none; cursor:pointer; color:var(--cr-faint);
  padding:8px; border-radius:8px; display:flex; align-items:center; transition:all .2s; }
.cr-date-clear:hover { background:#fee2e2; color:#ef4444; }

/* Status badge */
.cr-deadline-status { margin-top:6px; }
.cr-status-open, .cr-status-set { display:inline-flex; align-items:center; gap:7px;
  padding:7px 13px; border-radius:30px; font-size:.78rem; font-weight:600; }
.cr-status-open { background:#d1fae5; color:#065f46; }
.cr-status-set  { background:#fef3c7; color:#92400e; }

/* ─── Upload ─── */
.cr-upload { position:relative; border:2px dashed var(--cr-line); border-radius:var(--cr-r);
  background:var(--cr-bg); transition:all .25s; overflow:hidden; cursor:pointer; }
.cr-upload:hover, .cr-upload--over { border-color:var(--cr-indigo); background:#f5f3ff; }
.cr-upload input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; z-index:2; }
.cr-upload-content { display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:36px 20px; gap:8px; pointer-events:none; }
.cr-upload-ico { width:58px; height:58px; border-radius:16px;
  background:linear-gradient(135deg,var(--cr-indigo),var(--cr-violet));
  color:#fff; display:flex; align-items:center; justify-content:center; margin-bottom:4px; }
.cr-upload-label { font-size:.92rem; color:var(--cr-sub); margin:0; text-align:center; }
.cr-upload-browse { color:var(--cr-indigo); font-weight:700; }
.cr-upload-types { font-size:.74rem; color:var(--cr-faint); margin:0; }
.cr-preview { position:relative; padding:14px; display:flex; align-items:center; gap:14px; }
.cr-preview img { max-height:80px; max-width:120px; border-radius:10px; object-fit:cover; border:2px solid var(--cr-line); }
.cr-preview-del { display:inline-flex; align-items:center; gap:6px; padding:7px 14px;
  background:#fee2e2; color:#dc2626; border:none; border-radius:8px;
  font-size:.8rem; font-weight:700; cursor:pointer; transition:background .2s; }
.cr-preview-del:hover { background:#fecaca; }

/* ─── Actions ─── */
.cr-actions { display:flex; justify-content:space-between; align-items:center; gap:14px;
  padding-top:8px; border-top:2px solid var(--cr-bg); margin-top:4px; }
.cr-btn-cancel { display:inline-flex; align-items:center; gap:8px; padding:13px 22px;
  border:2px solid var(--cr-line); border-radius:12px; color:var(--cr-sub);
  text-decoration:none; font-weight:600; font-size:.9rem; transition:all .2s; }
.cr-btn-cancel:hover { border-color:#999; color:var(--cr-ink); background:var(--cr-bg); }
.cr-btn-submit { display:inline-flex; align-items:center; gap:10px; padding:13px 30px;
  background:linear-gradient(135deg,var(--cr-indigo),var(--cr-violet));
  color:#fff; border:none; border-radius:12px; font-size:.95rem; font-weight:800;
  cursor:pointer; transition:all .3s; box-shadow:var(--cr-sh-md); letter-spacing:.2px; }
.cr-btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(99,102,241,.45); }
.cr-btn-submit:active { transform:translateY(0); }

/* ══════════════════════════════════
   ASIDE
══════════════════════════════════ */
.cr-aside { display:flex; flex-direction:column; gap:18px; position:sticky; top:20px; }

/* Tips */
.cr-tips { background:linear-gradient(160deg,#1e1b4b,#312e81,#4c1d95);
  border-radius:18px; padding:22px; color:#fff; }
.cr-tips-head { display:flex; align-items:center; gap:9px; font-size:.95rem; font-weight:800;
  margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid rgba(255,255,255,.15); }
.cr-tip-list { display:flex; flex-direction:column; gap:16px; }
.cr-tip { display:flex; gap:12px; align-items:flex-start; }
.cr-tip-n { width:28px; height:28px; border-radius:8px; background:rgba(255,255,255,.15);
  display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:800;
  flex-shrink:0; margin-top:1px; }
.cr-tip strong { display:block; font-size:.87rem; font-weight:700; margin-bottom:3px; }
.cr-tip p { font-size:.78rem; opacity:.75; margin:0; line-height:1.5; }

/* Preview card */
.cr-preview-card { background:var(--cr-white); border:2px solid var(--cr-line);
  border-radius:18px; overflow:hidden; box-shadow:var(--cr-sh); }
.cr-pc-label { padding:10px 14px; font-size:.72rem; font-weight:700; color:var(--cr-faint);
  text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--cr-line); background:var(--cr-bg); }
.cr-pc-img { height:110px; background:linear-gradient(135deg,#ede9fe,#e0e7ff);
  display:flex; align-items:center; justify-content:center; overflow:hidden; transition:background .3s; }
.cr-pc-img img { width:100%; height:100%; object-fit:cover; display:none; }
.cr-pc-body { padding:14px; }
.cr-pc-cat { display:inline-block; background:#ede9fe; color:var(--cr-indigo); font-size:.68rem;
  font-weight:700; padding:2px 9px; border-radius:20px; text-transform:uppercase; letter-spacing:.5px; margin-bottom:7px; }
.cr-pc-title { font-size:.92rem; font-weight:800; color:var(--cr-ink); margin:0 0 10px; line-height:1.35;
  min-height:1.3em; word-break:break-word; }
.cr-pc-footer { display:flex; align-items:center; justify-content:space-between; }
.cr-pc-author { display:flex; align-items:center; gap:6px; font-size:.76rem; color:var(--cr-sub); font-weight:600; }
.cr-pc-av { width:22px; height:22px; border-radius:50%; background:linear-gradient(135deg,var(--cr-indigo),var(--cr-violet));
  color:#fff; display:flex; align-items:center; justify-content:center; font-size:.6rem; font-weight:800; }
.cr-pc-date { font-size:.72rem; color:var(--cr-faint); font-weight:500; }

/* ─── Responsive ─── */
@media(max-width:740px){
  .cr-wrap { grid-template-columns:1fr; }
  .cr-aside { position:static; }
  .cr-form { padding:18px; }
  .cr-head { padding:20px 18px; }
  .cr-tips { display:none; }
}
</style>

<script>
const titleEl = document.getElementById('title');
const descEl  = document.getElementById('description');
const catEl   = document.getElementById('category');
const dateEl  = document.getElementById('deadline');
const clearBtn = document.getElementById('cr-date-clear');
const statusEl = document.getElementById('cr-deadline-status');
const charCount = document.getElementById('char-count');

// ── Live preview ──
function updatePreview() {
    const t = titleEl.value.trim();
    document.getElementById('cr-pc-title').textContent = t || 'Titre du défi…';
    const c = catEl.value;
    document.getElementById('cr-pc-cat').textContent = c || 'Catégorie';
    const d = dateEl.value;
    document.getElementById('cr-pc-date').textContent = d
        ? 'Jusqu\'au ' + new Date(d).toLocaleDateString('fr-FR', {day:'2-digit',month:'short',year:'numeric'})
        : 'Ouvert ∞';
}

titleEl.addEventListener('input', updatePreview);
catEl.addEventListener('change', updatePreview);

// ── Char counter ──
descEl.addEventListener('input', () => {
    const n = descEl.value.length;
    charCount.textContent = n.toLocaleString('fr-FR') + ' caractère' + (n>1?'s':'');
});

// Init char count
const initLen = descEl.value.length;
if(initLen > 0) charCount.textContent = initLen.toLocaleString('fr-FR') + ' caractères';

// ── Deadline ──
dateEl.addEventListener('input', () => {
    const val = dateEl.value;
    clearBtn.style.display = val ? 'flex' : 'none';
    if(val) {
        const d = new Date(val);
        const fmt = d.toLocaleDateString('fr-FR', {weekday:'long', day:'numeric', month:'long', year:'numeric'});
        statusEl.innerHTML =
            '<div class="cr-status-set">' +
            '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>' +
            'Fermeture le <strong>' + fmt + '</strong>' +
            '</div>';
    } else {
        statusEl.innerHTML =
            '<div class="cr-status-open">' +
            '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' +
            'Défi <strong>ouvert indéfiniment</strong> — aucune date limite fixée' +
            '</div>';
    }
    updatePreview();
});

// Si date déjà remplie au chargement
if(dateEl.value) {
    dateEl.dispatchEvent(new Event('input'));
}

function clearDeadline() {
    dateEl.value = '';
    dateEl.dispatchEvent(new Event('input'));
}

// ── Upload image ──
function previewImg(event) {
    const file = event.target.files[0];
    if(!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('cr-upload-content').style.display = 'none';
        const prev = document.getElementById('cr-preview');
        document.getElementById('cr-preview-img').src = e.target.result;
        prev.style.display = 'flex';

        // Update card preview image
        const pcImg = document.getElementById('cr-pc-img');
        pcImg.innerHTML = '<img src="' + e.target.result + '" style="display:block;width:100%;height:100%;object-fit:cover">';
    };
    reader.readAsDataURL(file);
}

function removeImg() {
    document.getElementById('image').value = '';
    document.getElementById('cr-preview').style.display = 'none';
    document.getElementById('cr-upload-content').style.display = 'flex';
    // Reset card image
    document.getElementById('cr-pc-img').innerHTML =
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#c4b5fd" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('cr-upload').classList.remove('cr-upload--over');
    const file = e.dataTransfer.files[0];
    if(file && file.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('image').files = dt.files;
        previewImg({target:{files:[file]}});
    }
}

// ── Submit animation ──
document.getElementById('cr-form').addEventListener('submit', function() {
    const btn = document.getElementById('cr-submit');
    btn.disabled = true;
    btn.innerHTML =
        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="cr-spin"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Création en cours…';
});
</script>

<style>
@keyframes cr-spin { to { transform:rotate(360deg); } }
.cr-spin { animation: cr-spin .8s linear infinite; }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>