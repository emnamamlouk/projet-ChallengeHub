<?php
$title = htmlspecialchars($challenge['title']) . " - ChallengeHub";
$active_page = "challenge";
ob_start();
?>

<div class="challenge-detail-page">

    <!-- EN-TÊTE DU DÉFI -->
    <div class="challenge-header">
        <div class="challenge-meta-top">
            <span class="category-badge"><?= htmlspecialchars($challenge['category']) ?></span>
            <span class="challenge-status <?= $is_open ? 'open' : 'closed' ?>">
                <i class="fas <?= $is_open ? 'fa-lock-open' : 'fa-lock' ?>"></i>
                <?= $is_open ? 'Défi ouvert' : 'Défi terminé' ?>
            </span>
        </div>
        
        <h1 class="challenge-title"><?= htmlspecialchars($challenge['title']) ?></h1>
        
        <div class="challenge-author-info">
            <div class="author-avatar">
                <?php if(!empty($challenge['creator_avatar'])): ?>
                    <img src="public/<?= htmlspecialchars($challenge['creator_avatar']) ?>" alt="">
                <?php else: ?>
                    <div class="avatar-initials"><?= strtoupper(substr($challenge['creator_name'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="author-details">
                <span class="author-name"><?= htmlspecialchars($challenge['creator_name']) ?></span>
                <span class="publication-date">
                    <i class="bi bi-calendar-event-fill"></i> Publié le <?= date('d/m/Y à H:i', strtotime($challenge['created_at'])) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- IMAGE DU DÉFI -->
    <?php if(!empty($challenge['image'])): ?>
    <div class="challenge-image-container">
        <img src="public/<?= htmlspecialchars($challenge['image']) ?>" alt="<?= htmlspecialchars($challenge['title']) ?>" class="challenge-image">
    </div>
    <?php endif; ?>

    <!-- DESCRIPTION DU DÉFI -->
    <div class="challenge-description-card">
        <h2><i class="bi bi-text-left"></i> Description du défi</h2>
        <div class="description-content">
            <?= nl2br(htmlspecialchars($challenge['description'])) ?>
        </div>
    </div>

    <!-- INFORMATIONS COMPLÉMENTAIRES -->
    <div class="challenge-info-grid">
        <div class="info-item">
            <i class="bi bi-calendar-fill"></i>
            <div>
                <span class="info-label">Date limite</span>
                <span class="info-value">
                    <?php if(!empty($challenge['deadline'])): ?>
                        <?= date('d/m/Y', strtotime($challenge['deadline'])) ?>
                        <?php if($is_open): ?>
                            <span class="deadline-remaining">
                                (<?= round((strtotime($challenge['deadline']) - time())/86400) ?> jours restants)
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        Pas de date limite
                    <?php endif; ?>
                </span>
            </div>
        </div>
        
        <div class="info-item">
            <i class="bi bi-person-fills"></i>
            <div>
                <span class="info-label">Participations</span>
                <span class="info-value"><?= count($submissions) ?> personne<?= count($submissions) > 1 ? 's' : '' ?></span>
            </div>
        </div>
        
        <div class="info-item">
            <i class="bi bi-hand-thumbs-up-fill"></i>
            <div>
                <span class="info-label">Votes totaux</span>
                <span class="info-value">
                    <?php 
                    $totalVotes = 0;
                    foreach($submissions as $sub) {
                        $totalVotes += $sub['votes_count'];
                    }
                    echo $totalVotes;
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- BOUTONS D'ACTION -->
    <div class="challenge-action-buttons">
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['user_id'] == $challenge['user_id']): ?>
                <a href="index.php?action=editChallengeForm&id=<?= $challenge['id'] ?>" class="btn-edit">
                    <i class="bi bi-pencil-square"></i> Modifier le défi
                </a>
                <button onclick="document.getElementById('deleteModal').style.display='flex'" class="btn-delete">
                    <i class="bi bi-trash-fill"></i> Supprimer
                </button>
            <?php else: ?>
                <?php if($user_participated): ?>
                    <div class="already-participated">
                        <i class="bi bi-check-circle-fill"></i> Vous avez déjà participé
                    </div>
                <?php elseif($is_open): ?>
                    <a href="index.php?action=createSubmissionForm&challenge_id=<?= $challenge['id'] ?>" class="btn-participate">
                        <i class="bi bi-plus-lg-circle"></i> Participer à ce défi
                    </a>
                <?php else: ?>
                    <div class="challenge-closed-message">
                        <i class="bi bi-clock-fill"></i> Ce défi est terminé
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <a href="index.php?action=showLogin" class="btn-participate">
                <i class="bi bi-box-arrow-in-right"></i> Connectez-vous pour participer
            </a>
        <?php endif; ?>
    </div>

    <!-- SECTION PARTICIPATIONS -->
    <div class="participations-section">
        <div class="section-header">
            <h2><i class="bi bi-person-fills"></i> Participations (<?= count($submissions) ?>)</h2>
            <?php if(count($submissions) > 0): ?>
            <div class="sort-participations">
                <label>Trier par :</label>
                <a href="?action=showChallenge&id=<?= $challenge['id'] ?>&sort=recent" class="sort-link <?= ($_GET['sort'] ?? 'recent') == 'recent' ? 'active' : '' ?>">
                    <i class="bi bi-clock-fill"></i> Récents
                </a>
                <a href="?action=showChallenge&id=<?= $challenge['id'] ?>&sort=popular" class="sort-link <?= ($_GET['sort'] ?? 'recent') == 'popular' ? 'active' : '' ?>">
                    <i class="bi bi-fire"></i> Populaires
                </a>
            </div>
            <?php endif; ?>
        </div>

        <?php if(empty($submissions)): ?>
            <div class="empty-participations">
                <i class="bi bi-send-fill"></i>
                <h3>Aucune participation pour le moment</h3>
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $challenge['user_id'] && $is_open): ?>
                    <p>Soyez le premier à participer à ce défi !</p>
                    <a href="index.php?action=createSubmissionForm&challenge_id=<?= $challenge['id'] ?>" class="btn-participate-small">
                        <i class="bi bi-plus-lg-circle"></i> Participer
                    </a>
                <?php else: ?>
                    <p>Les participations apparaîtront ici</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="participations-grid">
                <?php foreach($submissions as $sub): ?>
                <div class="participation-card" id="submission-<?= $sub['id'] ?>">
                    <div class="participation-header">
                        <div class="participant-info">
                            <div class="participant-avatar">
                                <?php if(!empty($sub['avatar'])): ?>
                                    <img src="public/<?= htmlspecialchars($sub['avatar']) ?>" alt="">
                                <?php else: ?>
                                    <div class="avatar-small"><?= strtoupper(substr($sub['username'], 0, 1)) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="participant-details">
                                <a href="index.php?action=viewProfile&id=<?= $sub['user_id'] ?>" class="participant-name">
                                    <?= htmlspecialchars($sub['username']) ?>
                                </a>
                                <span class="participation-date">
                                    <i class="bi bi-clock-fill"></i> <?= date('d/m/Y', strtotime($sub['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                        <a href="index.php?action=showSubmission&id=<?= $sub['id'] ?>" class="btn-view-participation">
                            Voir en détail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <div class="participation-content">
                        <p class="participation-description">
                            <?= nl2br(htmlspecialchars(mb_substr($sub['description'], 0, 150))) ?>
                            <?= mb_strlen($sub['description']) > 150 ? '...' : '' ?>
                        </p>
                        
                        <?php if(!empty($sub['image'])): ?>
                        <div class="participation-thumbnail">
                            <img src="public/<?= htmlspecialchars($sub['image']) ?>" alt="">
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($sub['link'])): ?>
                        <a href="<?= htmlspecialchars($sub['link']) ?>" target="_blank" class="participation-link">
                            <i class="bi bi-box-arrow-up-right"></i> Voir le projet
                        </a>
                        <?php endif; ?>
                    </div>

                    <div class="participation-footer">
                        <div class="participation-stats">
                            <span class="vote-count <?= isset($sub['user_voted']) && $sub['user_voted'] ? 'voted' : '' ?>">
                                <i class="bi bi-hand-thumbs-up-fill"></i> <?= $sub['votes_count'] ?> vote<?= $sub['votes_count'] > 1 ? 's' : '' ?>
                            </span>
                            <span class="comment-count">
                                <i class="bi bi-chat-fill"></i> 
                                <?= isset($sub['comments_count']) ? $sub['comments_count'] : 0 ?> commentaire<?= isset($sub['comments_count']) && $sub['comments_count'] > 1 ? 's' : '' ?>
                            </span>
                        </div>
                        
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $sub['user_id']): ?>
                            <button class="vote-action-btn <?= isset($sub['user_voted']) && $sub['user_voted'] ? 'voted' : '' ?>" 
                                    onclick="voteSubmission(<?= $sub['id'] ?>, this)">
                                <i class="bi bi-hand-thumbs-up-fill"></i> 
                                <span><?= isset($sub['user_voted']) && $sub['user_voted'] ? 'Voté' : 'Voter' ?></span>
                            </button>
                        <?php endif; ?>
                        
                        <button class="comment-toggle-btn" onclick="toggleComments(<?= $sub['id'] ?>)">
                            <i class="bi bi-chat-fills"></i> Commentaires
                        </button>
                    </div>

                    <!-- SECTION COMMENTAIRES (cachée par défaut) -->
                    <div class="comments-section" id="comments-<?= $sub['id'] ?>" style="display: none;">
                        <div class="comments-list" id="comments-list-<?= $sub['id'] ?>">
                            <!-- Les commentaires seront chargés dynamiquement -->
                        </div>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="comment-form-container">
                            <div class="comment-avatar-small">
                                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                            </div>
                            <form class="comment-form" onsubmit="addComment(event, <?= $sub['id'] ?>)">
                                <input type="text" id="comment-input-<?= $sub['id'] ?>" placeholder="Écrire un commentaire..." required>
                                <button type="submit"><i class="bi bi-send-fill"></i></button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- SECTION COMMENTAIRES DU DÉFI -->
    <div class="challenge-comments-section">
        <div class="section-header">
            <h2><i class="bi bi-chat-fills"></i> Discussion sur le défi</h2>
        </div>

        <?php if(isset($_SESSION['user_id'])): ?>
        <div class="challenge-comment-form">
            <div class="comment-avatar-medium">
                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
            </div>
            <div class="comment-input-wrapper">
                <textarea id="challenge-comment-input" placeholder="Donnez votre avis sur ce défi..." rows="2"></textarea>
                <button onclick="addChallengeComment(<?= $challenge['id'] ?>)" class="btn-send-comment">
                    <i class="bi bi-send-fill"></i> Envoyer
                </button>
            </div>
        </div>
        <?php endif; ?>

        <div class="challenge-comments-list" id="challenge-comments-list">
            <!-- Les commentaires du défi seront chargés ici -->
        </div>
    </div>

</div>

<!-- MODAL DE SUPPRESSION -->
<div id="deleteModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <h3>Supprimer ce défi ?</h3>
        <p>Cette action est irréversible. Toutes les participations et commentaires seront supprimés.</p>
        <form action="index.php?action=deleteChallenge" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="challenge_id" value="<?= $challenge['id'] ?>">
            <div class="modal-actions">
                <button type="button" onclick="document.getElementById('deleteModal').style.display='none'" class="btn-cancel">Annuler</button>
                <button type="submit" class="btn-confirm-delete">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ===== STYLES PRINCIPAUX ===== */
.challenge-detail-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

/* ===== EN-TÊTE ===== */
.challenge-header {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.challenge-meta-top {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.category-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.challenge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.challenge-status.open {
    background: #d4edda;
    color: #155724;
}

.challenge-status.closed {
    background: #f8d7da;
    color: #721c24;
}

.challenge-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: #222;
    margin-bottom: 15px;
    line-height: 1.2;
    word-break: break-word;
}

.challenge-author-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.author-avatar img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #667eea;
}

.avatar-initials {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 700;
}

.author-details {
    display: flex;
    flex-direction: column;
}

.author-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #333;
    text-decoration: none;
}

.author-name:hover {
    color: #667eea;
}

.publication-date {
    font-size: 0.85rem;
    color: #888;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ===== IMAGE ===== */
.challenge-image-container {
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.challenge-image {
    width: 100%;
    max-height: 450px;
    object-fit: cover;
    display: block;
}

/* ===== DESCRIPTION ===== */
.challenge-description-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
}

.challenge-description-card h2 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.challenge-description-card h2 i {
    color: #667eea;
}

.description-content {
    color: #555;
    line-height: 1.8;
    font-size: 0.98rem;
    white-space: pre-wrap;
    word-break: break-word;
}

/* ===== GRILLE D'INFORMATIONS ===== */
.challenge-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
}

.info-item i {
    font-size: 1.3rem;
    color: #667eea;
    width: 30px;
    text-align: center;
}

.info-label {
    display: block;
    font-size: 0.75rem;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    display: block;
    font-size: 1rem;
    font-weight: 600;
    color: #333;
}

.deadline-remaining {
    font-size: 0.8rem;
    color: #f59e0b;
    font-weight: 500;
    margin-left: 5px;
}

/* ===== BOUTONS D'ACTION ===== */
.challenge-action-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.btn-participate {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.btn-participate:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102,126,234,0.4);
}

.btn-edit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #ede9fe;
    color: #667eea;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-delete {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #fee2e2;
    color: #ef4444;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.already-participated {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #d4edda;
    color: #155724;
    border-radius: 10px;
    font-weight: 600;
}

.challenge-closed-message {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #f8d7da;
    color: #721c24;
    border-radius: 10px;
    font-weight: 600;
}

/* ===== SECTION PARTICIPATIONS ===== */
.participations-section {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.section-header h2 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-header h2 i {
    color: #667eea;
}

.sort-participations {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.85rem;
}

.sort-participations label {
    color: #888;
}

.sort-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 14px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    color: #666;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.2s;
}

.sort-link:hover {
    background: #ede9fe;
    border-color: #667eea;
    color: #667eea;
}

.sort-link.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-color: transparent;
    color: white;
}

/* ===== PARTICIPATION CARD ===== */
.participations-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.participation-card {
    border: 1px solid #f0f0f0;
    border-radius: 14px;
    overflow: hidden;
    transition: all 0.3s;
}

.participation-card:hover {
    border-color: #c4b5fd;
    box-shadow: 0 4px 15px rgba(102,126,234,0.1);
}

.participation-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    background: #fafafa;
    border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap;
    gap: 10px;
}

.participant-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.participant-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
}

.participant-details {
    display: flex;
    flex-direction: column;
}

.participant-name {
    font-weight: 700;
    color: #333;
    text-decoration: none;
    font-size: 0.95rem;
}

.participant-name:hover {
    color: #667eea;
}

.participation-date {
    font-size: 0.75rem;
    color: #888;
    display: flex;
    align-items: center;
    gap: 4px;
}

.btn-view-participation {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #ede9fe;
    color: #667eea;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-view-participation:hover {
    background: #ddd6fe;
}

.participation-content {
    padding: 20px;
}

.participation-description {
    color: #555;
    line-height: 1.7;
    font-size: 0.92rem;
    margin-bottom: 15px;
}

.participation-thumbnail {
    margin: 10px 0;
    border-radius: 8px;
    overflow: hidden;
    max-width: 200px;
}

.participation-thumbnail img {
    width: 100%;
    height: auto;
    display: block;
}

.participation-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #667eea;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 10px;
}

.participation-footer {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 20px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    flex-wrap: wrap;
}

.participation-stats {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
}

.vote-count, .comment-count {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.85rem;
    color: #666;
}

.vote-count i {
    color: #ef4444;
}

.vote-count.voted i {
    color: #ef4444;
}

.comment-count i {
    color: #667eea;
}

.vote-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 30px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
    color: #666;
    transition: all 0.2s;
}

.vote-action-btn:hover {
    border-color: #ef4444;
    color: #ef4444;
    background: #fff5f5;
}

.vote-action-btn.voted {
    background: #fef2f2;
    border-color: #ef4444;
    color: #ef4444;
}

.comment-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: none;
    border: none;
    color: #888;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.comment-toggle-btn:hover {
    color: #667eea;
}

/* ===== COMMENTAIRES ===== */
.comments-section {
    padding: 20px;
    background: #f8f9fa;
    border-top: 1px solid #f0f0f0;
}

.comments-list {
    margin-bottom: 15px;
    max-height: 300px;
    overflow-y: auto;
}

.comment-item {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
}

.comment-avatar-small {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    flex-shrink: 0;
}

.comment-bubble {
    flex: 1;
    background: white;
    border-radius: 12px;
    padding: 10px 14px;
    border: 1px solid #f0f0f0;
}

.comment-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 5px;
    flex-wrap: wrap;
}

.comment-author {
    font-weight: 700;
    color: #333;
    font-size: 0.85rem;
}

.comment-date {
    font-size: 0.7rem;
    color: #aaa;
}

.comment-text {
    font-size: 0.85rem;
    color: #555;
    line-height: 1.5;
    margin: 0;
}

.delete-comment-btn {
    background: none;
    border: none;
    color: #ddd;
    cursor: pointer;
    font-size: 0.7rem;
    margin-left: auto;
}

.delete-comment-btn:hover {
    color: #ef4444;
}

.comment-form-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}

.comment-form {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 30px;
    padding: 5px 5px 5px 15px;
}

.comment-form input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 0.9rem;
    padding: 8px 0;
    background: transparent;
}

.comment-form button {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.comment-form button:hover {
    transform: scale(1.05);
}

/* ===== COMMENTAIRES DU DÉFI ===== */
.challenge-comments-section {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
}

.challenge-comment-form {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.comment-avatar-medium {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
    flex-shrink: 0;
}

.comment-input-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.comment-input-wrapper textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e8e8e8;
    border-radius: 12px;
    font-size: 0.9rem;
    resize: vertical;
    font-family: inherit;
    transition: border-color 0.3s;
}

.comment-input-wrapper textarea:focus {
    outline: none;
    border-color: #667eea;
}

.btn-send-comment {
    align-self: flex-end;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-send-comment:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
}

.challenge-comments-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 20px;
}

.challenge-comment-item {
    display: flex;
    gap: 12px;
}

.challenge-comment-bubble {
    flex: 1;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px 16px;
}

.challenge-comment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 5px;
}

.challenge-comment-author {
    font-weight: 700;
    color: #333;
    font-size: 0.9rem;
}

.challenge-comment-date {
    font-size: 0.75rem;
    color: #888;
}

.challenge-comment-content {
    font-size: 0.9rem;
    color: #555;
    line-height: 1.6;
    margin: 0;
}

/* ===== ÉTAT VIDE ===== */
.empty-participations {
    text-align: center;
    padding: 50px 20px;
    background: #f9f9f9;
    border-radius: 12px;
    border: 2px dashed #e0e0e0;
}

.empty-participations i {
    font-size: 2.5rem;
    color: #ccc;
    margin-bottom: 15px;
    display: block;
}

.empty-participations h3 {
    color: #555;
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.empty-participations p {
    color: #888;
    margin-bottom: 20px;
}

.btn-participate-small {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.btn-participate-small:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
}

/* ===== MODAL ===== */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-box {
    background: white;
    padding: 30px;
    border-radius: 20px;
    max-width: 400px;
    width: 90%;
    text-align: center;
}

.modal-icon {
    font-size: 2.5rem;
    color: #f59e0b;
    margin-bottom: 15px;
}

.modal-box h3 {
    font-size: 1.2rem;
    color: #333;
    margin-bottom: 10px;
}

.modal-box p {
    color: #666;
    margin-bottom: 20px;
    font-size: 0.9rem;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.btn-cancel {
    padding: 10px 24px;
    background: #f0f0f0;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
}

.btn-confirm-delete {
    padding: 10px 24px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .challenge-title {
        font-size: 1.8rem;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .participation-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .btn-view-participation {
        width: 100%;
        justify-content: center;
    }
    
    .participation-footer {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .vote-action-btn {
        width: 100%;
        justify-content: center;
    }
    
    .challenge-comment-form {
        flex-direction: column;
    }
    
    .comment-avatar-medium {
        align-self: flex-start;
    }
}

/* ============================================================
   NIGHT MODE — overrides locaux (priorité sur external CSS)
   ============================================================ */
body.night-mode .challenge-title {
    color: #e8ffe8 !important;
}
body.night-mode .author-name {
    color: #b0d4b0 !important;
}
body.night-mode .publication-date {
    color: #5a8a5a !important;
}
body.night-mode .challenge-description-card {
    background: #111811 !important;
    border-color: #2a4a2a !important;
    color: #b0d4b0 !important;
}
body.night-mode .challenge-description-card h2 {
    color: #00ff88 !important;
}
body.night-mode .description-content {
    color: #b0d4b0 !important;
}
body.night-mode .info-item {
    background: #162016 !important;
    border-color: #2a4a2a !important;
}
body.night-mode .info-label {
    color: #5a8a5a !important;
}
body.night-mode .info-value {
    color: #e8ffe8 !important;
}
body.night-mode .challenge-status.open {
    background: rgba(0,255,136,0.12) !important;
    color: #00ff88 !important;
    border-color: rgba(0,255,136,0.3) !important;
}
body.night-mode .challenge-status.closed {
    background: rgba(255,68,68,0.12) !important;
    color: #ff4444 !important;
}
body.night-mode .participations-section {
    background: #111811 !important;
    border-color: #2a4a2a !important;
}
body.night-mode .section-header h2 {
    color: #e8ffe8 !important;
}
body.night-mode .sort-participations label {
    color: #5a8a5a !important;
}
body.night-mode .sort-link {
    background: #162016 !important;
    color: #b0d4b0 !important;
    border-color: #1a2e1a !important;
}
body.night-mode .sort-link:hover,
body.night-mode .sort-link.active {
    background: rgba(0,255,136,0.1) !important;
    color: #00ff88 !important;
    border-color: rgba(0,255,136,0.3) !important;
}
body.night-mode .participation-card {
    background: #111811 !important;
    border-color: #1a2e1a !important;
}
body.night-mode .participation-card:hover {
    border-color: #2a4a2a !important;
    box-shadow: 0 0 20px rgba(0,255,136,0.08) !important;
}
body.night-mode .participation-header {
    background: #162016 !important;
    border-color: #1a2e1a !important;
}
body.night-mode .participant-name {
    color: #e8ffe8 !important;
}
body.night-mode .participation-date {
    color: #5a8a5a !important;
}
body.night-mode .participation-description {
    color: #b0d4b0 !important;
}
body.night-mode .btn-view-participation {
    background: rgba(0,255,136,0.1) !important;
    color: #00ff88 !important;
    border-color: rgba(0,255,136,0.2) !important;
}
body.night-mode .btn-view-participation:hover {
    background: rgba(0,255,136,0.18) !important;
}
body.night-mode .participation-footer {
    background: #162016 !important;
    border-color: #1a2e1a !important;
}
body.night-mode .vote-action-btn {
    background: #162016 !important;
    border-color: #2a4a2a !important;
    color: #b0d4b0 !important;
}
body.night-mode .vote-action-btn:hover {
    background: rgba(0,255,136,0.08) !important;
    border-color: #005c30 !important;
    color: #00ff88 !important;
}
body.night-mode .vote-action-btn.voted {
    background: rgba(255,68,68,0.1) !important;
    color: #ff4444 !important;
    border-color: rgba(255,68,68,0.2) !important;
}
body.night-mode .comment-toggle-btn {
    color: #5a8a5a !important;
}
body.night-mode .comments-section {
    background: #0f1a0f !important;
    border-color: #1a2e1a !important;
}
body.night-mode .comment-bubble {
    background: #162016 !important;
    border-color: #1a2e1a !important;
}
body.night-mode .comment-author {
    color: #e8ffe8 !important;
}
body.night-mode .comment-text {
    color: #b0d4b0 !important;
}
body.night-mode .comment-form {
    background: #0f1a0f !important;
    border-color: #1a2e1a !important;
}
body.night-mode .comment-form textarea,
body.night-mode .comment-form input[type="text"] {
    background: #162016 !important;
    border-color: #2a4a2a !important;
    color: #e8ffe8 !important;
}
body.night-mode .comment-form textarea::placeholder,
body.night-mode .comment-form input::placeholder {
    color: #3a5a3a !important;
}
body.night-mode .challenge-comments-section {
    background: #111811 !important;
    border-color: #2a4a2a !important;
}
body.night-mode .challenge-comment-bubble {
    background: #162016 !important;
    border-color: #1a2e1a !important;
}
body.night-mode .challenge-comment-author {
    color: #e8ffe8 !important;
}
body.night-mode .challenge-comment-date {
    color: #5a8a5a !important;
}
body.night-mode .challenge-comment-content {
    color: #b0d4b0 !important;
}
body.night-mode .empty-participations {
    background: #162016 !important;
    border-color: #1a2e1a !important;
}
body.night-mode .empty-participations h3 {
    color: #b0d4b0 !important;
}
body.night-mode .empty-participations p {
    color: #5a8a5a !important;
}
body.night-mode .modal-box {
    background: #111811 !important;
    border-color: #2a4a2a !important;
}
body.night-mode .modal-box h3 {
    color: #e8ffe8 !important;
}
body.night-mode .modal-box p {
    color: #b0d4b0 !important;
}
body.night-mode .btn-cancel {
    background: #162016 !important;
    border-color: #2a4a2a !important;
    color: #b0d4b0 !important;
}
</style>

<script>
// ===== VARIABLES GLOBALES =====
const CHALLENGE_ID = <?= $challenge['id'] ?>;
const LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
const CURRENT_USER = "<?= isset($_SESSION['username']) ? addslashes($_SESSION['username']) : '' ?>";
const USER_INITIAL = "<?= isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : '' ?>";

// ===== FONCTIONS POUR LES VOTES =====
function voteSubmission(submissionId, btn) {
    if (!LOGGED_IN) {
        window.location.href = 'index.php?action=showLogin';
        return;
    }
    
    fetch('index.php?action=vote', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'submission_id=' + submissionId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour le compteur de votes
            const voteCountSpan = document.querySelector(`#submission-${submissionId} .vote-count span`);
            if (voteCountSpan) {
                voteCountSpan.textContent = data.new_count;
            }
            
            // Mettre à jour le bouton
            btn.classList.toggle('voted', data.action === 'added');
            btn.querySelector('span').textContent = data.action === 'added' ? 'Voté' : 'Voter';
        } else {
            alert(data.message || 'Erreur lors du vote');
        }
    })
    .catch(err => console.error('Erreur:', err));
}

// ===== FONCTIONS POUR LES COMMENTAIRES DES PARTICIPATIONS =====
function toggleComments(submissionId) {
    const commentsSection = document.getElementById('comments-' + submissionId);
    
    if (commentsSection.style.display === 'none') {
        commentsSection.style.display = 'block';
        loadSubmissionComments(submissionId);
    } else {
        commentsSection.style.display = 'none';
    }
}

function loadSubmissionComments(submissionId) {
    fetch('index.php?action=getComments&submission_id=' + submissionId)
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const commentsList = document.getElementById('comments-list-' + submissionId);
            commentsList.innerHTML = '';
            
            if (data.comments.length === 0) {
                commentsList.innerHTML = '<p style="color:#aaa; text-align:center; padding:10px;">Aucun commentaire</p>';
                return;
            }
            
            data.comments.forEach(comment => {
                const commentHtml = `
                    <div class="comment-item" id="comment-${comment.id}">
                        <div class="comment-avatar-small">${comment.username.charAt(0).toUpperCase()}</div>
                        <div class="comment-bubble">
                            <div class="comment-meta">
                                <span class="comment-author">${comment.username}</span>
                                <span class="comment-date">${new Date(comment.created_at).toLocaleDateString('fr-FR')}</span>
                                ${LOGGED_IN && comment.user_id == <?= $_SESSION['user_id'] ?? 0 ?> ? 
                                    '<button class="delete-comment-btn" onclick="deleteComment(' + comment.id + ', ' + submissionId + ')"><i class="bi bi-x-circle-fill"></i></button>' : ''}
                            </div>
                            <p class="comment-text">${comment.content}</p>
                        </div>
                    </div>
                `;
                commentsList.insertAdjacentHTML('beforeend', commentHtml);
            });
        }
    });
}

function addComment(event, submissionId) {
    event.preventDefault();
    
    const input = document.getElementById('comment-input-' + submissionId);
    const content = input.value.trim();
    
    if (!content) return;
    
    fetch('index.php?action=addComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'submission_id=' + submissionId + '&content=' + encodeURIComponent(content)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadSubmissionComments(submissionId);
        } else {
            alert(data.message || 'Erreur');
        }
    });
}

function deleteComment(commentId, submissionId) {
    if (!confirm('Supprimer ce commentaire ?')) return;
    
    fetch('index.php?action=deleteComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'comment_id=' + commentId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadSubmissionComments(submissionId);
        }
    });
}

// ===== FONCTIONS POUR LES COMMENTAIRES DU DÉFI =====
function loadChallengeComments() {
    fetch('index.php?action=getChallengeComments&challenge_id=' + CHALLENGE_ID)
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const commentsList = document.getElementById('challenge-comments-list');
            commentsList.innerHTML = '';
            
            if (data.comments.length === 0) {
                commentsList.innerHTML = '<p style="color:#aaa; text-align:center; padding:20px;">Aucune discussion pour le moment</p>';
                return;
            }
            
            data.comments.forEach(comment => {
                const commentHtml = `
                    <div class="challenge-comment-item">
                        <div class="comment-avatar-small">${comment.username.charAt(0).toUpperCase()}</div>
                        <div class="challenge-comment-bubble">
                            <div class="challenge-comment-header">
                                <span class="challenge-comment-author">${comment.username}</span>
                                <span class="challenge-comment-date">${new Date(comment.created_at).toLocaleDateString('fr-FR')}</span>
                            </div>
                            <p class="challenge-comment-content">${comment.content}</p>
                        </div>
                    </div>
                `;
                commentsList.insertAdjacentHTML('beforeend', commentHtml);
            });
        }
    });
}

function addChallengeComment(challengeId) {
    if (!LOGGED_IN) {
        window.location.href = 'index.php?action=showLogin';
        return;
    }
    
    const input = document.getElementById('challenge-comment-input');
    const content = input.value.trim();
    
    if (!content) return;
    
    fetch('index.php?action=addChallengeComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'challenge_id=' + challengeId + '&content=' + encodeURIComponent(content)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadChallengeComments();
        } else {
            alert(data.message || 'Erreur');
        }
    });
}

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    loadChallengeComments();
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>