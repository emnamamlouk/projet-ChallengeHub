<?php
// app/views/challenges/ranking.php
$title = "Classement des défis - ChallengeHub";
$active_page = "ranking";

$selectedCategory = $_GET['category'] ?? 'Design';
$selectedSort     = $_GET['sort']     ?? 'votes';

if ($selectedCategory == 'all') {
    header('Location: index.php?action=ranking&category=Design&sort=' . $selectedSort);
    exit();
}

$allCategories = ['Design', 'Code', 'Art', 'Écriture', 'Photo', 'Vidéo', 'Musique', 'Autre'];

$categoryDisplay = [
    'Design'   => 'Design',
    'Code'     => 'Code / Programmation',
    'Art'      => 'Art / Illustration',
    'Écriture' => 'Écriture',
    'Photo'    => 'Photographie',
    'Vidéo'    => 'Vidéo',
    'Musique'  => 'Musique',
    'Autre'    => 'Autre',
];

ob_start();
?>

<div class="ranking-page">

    <!-- HEADER -->
    <div class="ranking-header">
        <h1 class="page-title">Classement</h1>
        <p class="page-subtitle">
            Catégorie &mdash; <strong><?= htmlspecialchars($categoryDisplay[$selectedCategory] ?? $selectedCategory) ?></strong>
        </p>
    </div>

    <!-- FILTRES -->
    <div class="ranking-filters">
        <form action="index.php" method="GET">
            <input type="hidden" name="action" value="ranking">
            <div class="filters-row">
                <div class="filter-group">
                    <label for="category">Catégorie</label>
                    <select name="category" id="category" onchange="this.form.submit()">
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?= $cat ?>" <?= $selectedCategory == $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoryDisplay[$cat] ?? $cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="sort">Classer par</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="votes"          <?= $selectedSort == 'votes'          ? 'selected' : '' ?>>Votes</option>
                        <option value="participations" <?= $selectedSort == 'participations' ? 'selected' : '' ?>>Participations</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- LISTE -->
    <div class="ranking-card">
        <?php if (isset($topChallenges) && !empty($topChallenges)): ?>

            <?php
            // Dédoublonner par ID
            $seen   = [];
            $unique = [];
            foreach ($topChallenges as $ch) {
                if (!in_array($ch['id'], $seen)) {
                    $seen[]   = $ch['id'];
                    $unique[] = $ch;
                }
            }
            $topTen = array_slice($unique, 0, 10);

            // Détecter ex-aequo
            $hasTie    = false;
            $prevValue = null;
            foreach ($topTen as $ch) {
                $val = $selectedSort == 'votes'
                    ? (int)($ch['likes_count'] ?? 0)
                    : (int)($ch['submissions_count'] ?? 0);
                if ($prevValue !== null && $val == $prevValue) { $hasTie = true; break; }
                $prevValue = $val;
            }
            ?>

            <!-- En-tête tableau -->
            <div class="ranking-table-header">
                <span class="col-rank">Rang</span>
                <span class="col-title">Défi</span>
                <span class="col-creator">Créateur</span>
                <span class="col-score"><?= $selectedSort == 'votes' ? 'Votes' : 'Participations' ?></span>
            </div>

            <!-- Lignes -->
            <?php $rank = 1; foreach ($topTen as $challenge):
                $score = $selectedSort == 'votes'
                    ? (int)($challenge['likes_count'] ?? 0)
                    : (int)($challenge['submissions_count'] ?? 0);

                $medalClass = '';
                if ($rank === 1) $medalClass = 'gold';
                elseif ($rank === 2) $medalClass = 'silver';
                elseif ($rank === 3) $medalClass = 'bronze';
            ?>
                <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="ranking-row <?= $medalClass ?>">
                    <span class="col-rank">
                        <?php if ($rank === 1): ?>
                            <span class="crown">👑</span>
                        <?php endif; ?>
                        <span class="rank-badge <?= $medalClass ?>"><?= $rank ?></span>
                    </span>
                    <span class="col-title">
                        <span class="challenge-title"><?= htmlspecialchars($challenge['title']) ?></span>
                        <span class="challenge-date">
                            Publié le <?= date('d/m/Y à H:i', strtotime($challenge['created_at'])) ?>
                        </span>
                    </span>
                    <span class="col-creator"><?= htmlspecialchars($challenge['creator_name'] ?? '') ?></span>
                    <span class="col-score">
                        <span class="score-value"><?= $score ?></span>
                        <span class="score-label">
                            <?= $selectedSort == 'votes'
                                ? 'vote' . ($score > 1 ? 's' : '')
                                : 'participation' . ($score > 1 ? 's' : '') ?>
                        </span>
                    </span>
                </a>
            <?php $rank++; endforeach; ?>

            <!-- Notice ex-aequo -->
            <?php if ($hasTie): ?>
                <div class="ranking-notice">
                    <i class="fas fa-info-circle"></i>
                    En cas d'égalité de votes, le défi publié en premier est classé devant.
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="ranking-footer">
                <?= count($unique) ?> défi<?= count($unique) > 1 ? 's' : '' ?> dans cette catégorie
                <?= count($unique) > 10 ? ' &mdash; affichage des 10 premiers' : '' ?>
            </div>

        <?php else: ?>
            <div class="ranking-empty">
                <i class="fas fa-trophy"></i>
                <h3>Aucun défi dans cette catégorie</h3>
                <p>Soyez le premier à publier un défi.</p>
                <a href="index.php?action=createChallengeForm" class="btn-create">Créer un défi</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<style>
/* ===== PAGE ===== */
.ranking-page {
    max-width: 860px;
    margin: 0 auto;
    padding: 30px 20px 60px;
}

/* ===== HEADER ===== */
.ranking-header {
    margin-bottom: 26px;
    padding-bottom: 18px;
    border-bottom: 2px solid #f0f0f0;
}

.page-title {
    font-size: 1.9rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 5px;
    letter-spacing: -0.5px;
}

.page-subtitle {
    font-size: 0.93rem;
    color: #999;
    margin: 0;
}

.page-subtitle strong {
    color: #667eea;
    font-weight: 600;
}

/* ===== FILTRES ===== */
.ranking-filters {
    background: #fff;
    border: 1px solid #ebebeb;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 5px rgba(0,0,0,0.04);
    transition: box-shadow 0.3s ease;
}

.ranking-filters:hover {
    box-shadow: 0 4px 12px rgba(102,126,234,0.15);
}

.filters-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-group label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #bbb;
    margin-bottom: 6px;
}

.filter-group select {
    width: 100%;
    padding: 9px 32px 9px 12px;
    border: 1.5px solid #e2e2e2;
    border-radius: 7px;
    font-size: 0.91rem;
    color: #333;
    background: #fafafa;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%23aaa' d='M5 7L0 2h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}

.filter-group select:hover {
    border-color: #667eea;
    background-color: #fff;
}

.filter-group select:focus {
    outline: none;
    border-color: #667eea;
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
}

/* ===== CARTE ===== */
.ranking-card {
    background: #fff;
    border: 1px solid #ebebeb;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 14px rgba(0,0,0,0.05);
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===== EN-TÊTE TABLEAU ===== */
.ranking-table-header {
    display: flex;
    align-items: center;
    padding: 10px 22px;
    background: #f7f8fc;
    border-bottom: 1px solid #ebebeb;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #bbb;
}

/* ===== COLONNES ===== */
.col-rank    { width: 60px;  flex-shrink: 0; position: relative; }
.col-title   { flex: 1;      padding-right: 14px; min-width: 0; }
.col-creator { width: 130px; flex-shrink: 0; font-size: 0.85rem; color: #aaa; }
.col-score   { width: 110px; flex-shrink: 0; text-align: right;
               display: flex; flex-direction: column; align-items: flex-end; position: relative; }

/* ===== LIGNE ===== */
.ranking-row {
    display: flex;
    align-items: center;
    padding: 14px 22px;
    border-bottom: 1px solid #f3f3f3;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.ranking-row::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 3px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.ranking-row:hover::before {
    opacity: 1;
}

.ranking-row:last-of-type { border-bottom: none; }

.ranking-row:hover {
    background: #f7f8ff;
    transform: translateX(8px) scale(1.02);
    box-shadow: 0 4px 12px rgba(102,126,234,0.2);
    z-index: 2;
}

/* Couleurs top 3 */
.ranking-row.gold   { background: #fffcec; }
.ranking-row.silver { background: #f9f9f9; }
.ranking-row.bronze { background: #fff7f2; }
.ranking-row.gold:hover   { background: #fff5cc; }
.ranking-row.silver:hover { background: #f2f2f2; }
.ranking-row.bronze:hover { background: #ffede0; }

/* ===== COURONNE ===== */
.crown {
    position: absolute;
    top: -25px;
    left: 15px;
    font-size: 2rem;
    z-index: 10;
    animation: floatCrown 2s ease-in-out infinite;
    filter: drop-shadow(0 4px 6px rgba(255,215,0,0.5));
    display: block !important;
    pointer-events: none;
}

@keyframes floatCrown {
    0%, 100% {
        transform: translateY(0) rotate(-3deg);
    }
    50% {
        transform: translateY(-8px) rotate(3deg);
    }
}

/* ===== BADGE RANG ===== */
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 33px;
    height: 33px;
    border-radius: 50%;
    font-size: 0.84rem;
    font-weight: 800;
    background: #eee;
    color: #aaa;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.ranking-row:hover .rank-badge {
    transform: scale(1.15);
}

.rank-badge.gold { 
    background: #f5c518; 
    color: #7a5800;
    box-shadow: 0 2px 8px rgba(245,197,24,0.4);
}

.rank-badge.silver { 
    background: #b8b8b8; 
    color: #fff;
    box-shadow: 0 2px 8px rgba(184,184,184,0.4);
}

.rank-badge.bronze { 
    background: #cd7f32; 
    color: #fff;
    box-shadow: 0 2px 8px rgba(205,127,50,0.4);
}

/* ===== TITRE + DATE ===== */
.challenge-title {
    display: block;
    font-size: 0.94rem;
    font-weight: 600;
    color: #1a1a2e;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.3s ease;
}

.ranking-row:hover .challenge-title {
    color: #667eea;
}

.challenge-date {
    display: block;
    font-size: 0.75rem;
    color: #ccc;
    margin-top: 3px;
    transition: color 0.3s ease;
}

.ranking-row:hover .challenge-date {
    color: #999;
}

/* ===== SCORE ===== */
.score-value {
    font-size: 1.15rem;
    font-weight: 800;
    color: #667eea;
    line-height: 1;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.ranking-row:hover .score-value {
    transform: scale(1.2);
    color: #5568d6;
}

.score-label {
    font-size: 0.7rem;
    color: #ccc;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 2px;
    transition: color 0.3s ease;
}

.ranking-row:hover .score-label {
    color: #999;
}

/* ===== NOTICE EX-AEQUO ===== */
.ranking-notice {
    padding: 11px 22px;
    background: #f0f4ff;
    border-top: 1px solid #dce8ff;
    font-size: 0.81rem;
    color: #5a72cc;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* ===== FOOTER ===== */
.ranking-footer {
    padding: 11px 22px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    font-size: 0.79rem;
    color: #ccc;
    transition: color 0.3s ease;
}

.ranking-footer:hover {
    color: #667eea;
}

/* ===== ÉTAT VIDE ===== */
.ranking-empty {
    text-align: center;
    padding: 60px 30px;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.ranking-empty i {
    font-size: 2.5rem;
    color: #e0e0e0;
    display: block;
    margin-bottom: 16px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.ranking-empty h3 {
    font-size: 1.05rem;
    color: #555;
    margin-bottom: 8px;
    font-weight: 600;
}

.ranking-empty p {
    color: #bbb;
    font-size: 0.88rem;
    margin-bottom: 22px;
}

.btn-create {
    display: inline-block;
    padding: 10px 26px;
    background: #667eea;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.88rem;
    transition: all 0.3s ease;
}

.btn-create:hover {
    background: #5568d6;
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 8px 20px rgba(102,126,234,0.4);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 600px) {
    .col-creator { display: none; }
    .col-score   { width: 75px; }
    .filters-row { flex-direction: column; gap: 12px; }
    .page-title  { font-size: 1.4rem; }
    .ranking-row,
    .ranking-table-header { padding: 12px 14px; }
    .score-value { font-size: 1rem; }
    .crown {
        left: 5px;
        top: -20px;
        font-size: 1.5rem;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>