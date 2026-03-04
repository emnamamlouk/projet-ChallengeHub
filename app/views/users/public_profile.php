<?php
$title       = htmlspecialchars($user['username']) . " — ChallengeHub";
$active_page = "";
$userSubmissions    = $userSubmissions    ?? [];
$userChallenges     = $userChallenges     ?? [];
ob_start();
$currentUserId = $_SESSION['user_id'] ?? null;
?>

<div class="profile-container">

    <!-- BANNIERE DU PROFIL -->
    <div class="profile-banner">
        <div class="banner-bg"></div>
        <div class="banner-content">
            <div class="profile-avatar">
                <?php if(!empty($user['avatar'])): ?>
                    <img src="public/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="avatar-img">
                <?php else: ?>
                    <div class="avatar-initials"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h1 class="profile-username"><?= htmlspecialchars($user['username']) ?></h1>
                <p class="profile-member-since">Membre depuis <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                <?php if(!empty($user['bio'])): ?>
                    <p class="profile-bio"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                <?php endif; ?>
            </div>

            <!-- STATS : seulement Défis créés + Participations (votes reçus supprimé) -->
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-value"><?= count($userChallenges) ?></span>
                    <span class="stat-label">Défis créés</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($userSubmissions) ?></span>
                    <span class="stat-label">Participations</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS DE NAVIGATION -->
    <div class="profile-tabs">
        <button class="tab-btn active" onclick="showTab('challenges', event)">
            <i class="bi bi-trophy-fill"></i> Défis créés
            <span class="tab-count"><?= count($userChallenges) ?></span>
        </button>
        <button class="tab-btn" onclick="showTab('submissions', event)">
            <i class="bi bi-send-fill"></i> Participations
            <span class="tab-count"><?= count($userSubmissions) ?></span>
        </button>
    </div>

    <!-- TAB : Défis -->
    <div id="tab-challenges" class="tab-content active">
        <?php if(empty($userChallenges)): ?>
            <div class="empty-state">
                <i class="bi bi-trophy-fill"></i>
                <p><?= htmlspecialchars($user['username']) ?> n'a pas encore créé de défi.</p>
            </div>
        <?php else: ?>
            <div class="challenges-grid">
                <?php foreach($userChallenges as $challenge): ?>
                    <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="challenge-card">
                        <div class="card-category"><?= htmlspecialchars($challenge['category']) ?></div>
                        <?php if(!empty($challenge['image'])): ?>
                            <div class="card-image">
                                <img src="public/<?= htmlspecialchars($challenge['image']) ?>" alt="">
                            </div>
                        <?php else: ?>
                            <div class="card-image-placeholder">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($challenge['title']) ?></h3>
                            <p class="card-description"><?= htmlspecialchars(mb_substr($challenge['description'], 0, 100)) ?>...</p>
                            <div class="card-footer">
                                <span class="card-date">
                                    <i class="bi bi-calendar-fill"></i> <?= date('d/m/Y', strtotime($challenge['created_at'])) ?>
                                </span>
                                <span class="card-arrow"><i class="bi bi-arrow-right"></i></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB : Participations -->
    <div id="tab-submissions" class="tab-content">
        <?php if(empty($userSubmissions)): ?>
            <div class="empty-state">
                <i class="bi bi-send-fill"></i>
                <p><?= htmlspecialchars($user['username']) ?> n'a pas encore participé à des défis.</p>
            </div>
        <?php else: ?>
            <div class="submissions-list">
                <?php foreach($userSubmissions as $submission):
                    $subId    = $submission['id'];
                    $voteCount = $submission['votes_count'] ?? 0;
                    $userVoted = $submission['user_voted'] ?? false;
                    $canVote   = $currentUserId && $currentUserId != $submission['user_id'];
                ?>
                    <div class="submission-card" id="submission-<?= $subId ?>">
                        <!-- En-tête -->
                        <div class="submission-header">
                            <div class="submission-author">
                                <div class="author-avatar">
                                    <?php if(!empty($user['avatar'])): ?>
                                        <img src="public/<?= htmlspecialchars($user['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <div class="avatar-small"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="author-info">
                                    <span class="author-name"><?= htmlspecialchars($user['username']) ?></span>
                                    <span class="submission-date">
                                        <i class="bi bi-clock-fill"></i> <?= date('d/m/Y à H:i', strtotime($submission['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                            <a href="index.php?action=showChallenge&id=<?= $submission['challenge_id'] ?>" class="challenge-link">
                                <i class="bi bi-trophy-fill"></i>
                                <span><?= htmlspecialchars($submission['challenge_title']) ?></span>
                            </a>
                        </div>

                        <!-- Contenu -->
                        <div class="submission-content">
                            <?php if(!empty($submission['image'])): ?>
                                <div class="submission-image">
                                    <img src="public/<?= htmlspecialchars($submission['image']) ?>" alt="">
                                </div>
                            <?php endif; ?>
                            <div class="submission-description">
                                <p><?= nl2br(htmlspecialchars($submission['description'])) ?></p>
                                <?php if(!empty($submission['link'])): ?>
                                    <a href="<?= htmlspecialchars($submission['link']) ?>" target="_blank" class="submission-link">
                                        <i class="bi bi-box-arrow-up-right"></i> Voir le projet
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="submission-actions">
                            <div class="actions-left">
                                <?php if($canVote): ?>
                                    <button class="vote-btn <?= $userVoted ? 'voted' : '' ?>"
                                            onclick="voteSubmission(<?= $subId ?>, this)"
                                            data-id="<?= $subId ?>">
                                        <i class="bi bi-hand-thumbs-up-fill"></i>
                                        <span class="vote-count"><?= $voteCount ?></span>
                                        <span class="vote-label"><?= $userVoted ? 'Voté' : 'Voter' ?></span>
                                    </button>
                                <?php else: ?>
                                    <div class="vote-display">
                                        <i class="bi bi-hand-thumbs-up-fill"></i>
                                        <span><?= $voteCount ?></span>
                                    </div>
                                <?php endif; ?>
                                <button class="comments-toggle" onclick="toggleComments(<?= $subId ?>)">
                                    <i class="bi bi-chat-fill"></i>
                                    <span class="comments-count"><?= $submission['comments_count'] ?? 0 ?></span>
                                    <span>Commentaires</span>
                                </button>
                            </div>
                            <a href="index.php?action=showSubmission&id=<?= $subId ?>" class="view-link">
                                Voir en détail <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>

                        <!-- Section commentaires -->
                        <div class="comments-section" id="comments-<?= $subId ?>" style="display:none;">
                            <div class="comments-list" id="comments-list-<?= $subId ?>"></div>
                            <?php if($currentUserId): ?>
                            <div class="comment-form-wrapper">
                                <div class="comment-avatar-mini"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                                <form class="comment-form" onsubmit="addComment(event, <?= $subId ?>)">
                                    <input type="text" id="comment-input-<?= $subId ?>" placeholder="Écrire un commentaire..." required>
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

</div>

<style>
:root {
    --primary: #6366f1; --primary-dark: #4f52e0; --primary-light: #e0e7ff;
    --secondary: #8b5cf6; --success: #10b981; --danger: #ef4444;
    --dark: #1f2937; --light: #f9fafb; --gray: #6b7280; --border: #e5e7eb;
}
.profile-container { max-width: 1000px; margin: 0 auto; padding: 20px; }
.profile-banner { position:relative; border-radius:16px; overflow:hidden; margin-bottom:25px; box-shadow:0 10px 25px rgba(99,102,241,0.2); }
.banner-bg { position:absolute; inset:0; background:linear-gradient(135deg,var(--primary),var(--secondary)); z-index:1; }
.banner-content { position:relative; z-index:2; display:flex; align-items:center; gap:30px; padding:35px 30px; color:white; flex-wrap:wrap; background:rgba(0,0,0,0.1); }
.profile-avatar { flex-shrink:0; }
.avatar-img { width:100px; height:100px; border-radius:50%; border:4px solid rgba(255,255,255,0.3); object-fit:cover; }
.avatar-initials { width:100px; height:100px; border-radius:50%; background:rgba(255,255,255,0.2); border:4px solid rgba(255,255,255,0.3); display:flex; align-items:center; justify-content:center; font-size:2.5rem; font-weight:700; }
.profile-info { flex:1; }
.profile-username { font-size:2rem; font-weight:800; margin-bottom:5px; }
.profile-member-since { font-size:0.9rem; opacity:0.9; margin-bottom:10px; }
.profile-bio { font-size:0.95rem; opacity:0.95; line-height:1.6; max-width:400px; }
.profile-stats { display:flex; align-items:center; gap:20px; background:rgba(255,255,255,0.15); border-radius:12px; padding:15px 25px; }
.stat-item { text-align:center; }
.stat-value { display:block; font-size:1.8rem; font-weight:800; line-height:1; }
.stat-label { display:block; font-size:0.75rem; opacity:0.8; text-transform:uppercase; letter-spacing:0.5px; }
.stat-divider { width:1px; height:40px; background:rgba(255,255,255,0.3); }
.profile-tabs { display:flex; gap:10px; margin-bottom:25px; border-bottom:2px solid var(--border); }
.tab-btn { display:flex; align-items:center; gap:8px; padding:12px 24px; background:none; border:none; border-bottom:3px solid transparent; margin-bottom:-2px; color:var(--gray); font-size:0.95rem; font-weight:600; cursor:pointer; transition:all 0.2s; }
.tab-btn:hover { color:var(--primary); background:var(--primary-light); border-radius:8px 8px 0 0; }
.tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); background:var(--primary-light); }
.tab-count { background:#e5e7eb; color:var(--dark); padding:2px 8px; border-radius:20px; font-size:0.7rem; font-weight:700; }
.tab-btn.active .tab-count { background:var(--primary); color:white; }
.tab-content { display:none; }
.tab-content.active { display:block; }
.empty-state { text-align:center; padding:60px 20px; background:var(--light); border-radius:12px; border:2px dashed var(--border); }
.empty-state i { font-size:3rem; color:var(--gray); margin-bottom:15px; opacity:0.5; }
.empty-state p { color:var(--gray); }
.challenges-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; }
.challenge-card { background:white; border:1px solid var(--border); border-radius:12px; overflow:hidden; text-decoration:none; color:inherit; transition:all 0.3s; position:relative; }
.challenge-card:hover { transform:translateY(-4px); box-shadow:0 10px 25px rgba(99,102,241,0.15); border-color:var(--primary); }
.card-category { position:absolute; top:10px; right:10px; background:var(--primary); color:white; padding:4px 12px; border-radius:20px; font-size:0.7rem; font-weight:600; z-index:2; }
.card-image { height:150px; overflow:hidden; }
.card-image img { width:100%; height:100%; object-fit:cover; transition:transform 0.3s; }
.challenge-card:hover .card-image img { transform:scale(1.05); }
.card-image-placeholder { height:150px; background:linear-gradient(135deg,var(--primary-light),#c7d2fe); display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:var(--primary); }
.card-body { padding:16px; }
.card-title { font-size:1rem; font-weight:700; color:var(--dark); margin-bottom:8px; }
.card-description { font-size:0.85rem; color:var(--gray); line-height:1.5; margin-bottom:15px; }
.card-footer { display:flex; align-items:center; justify-content:space-between; padding-top:12px; border-top:1px solid var(--border); }
.card-date { font-size:0.8rem; color:var(--gray); display:flex; align-items:center; gap:4px; }
.card-date i { color:var(--primary); }
.card-arrow { color:var(--primary); transition:transform 0.2s; }
.challenge-card:hover .card-arrow { transform:translateX(5px); }
.submissions-list { display:flex; flex-direction:column; gap:20px; }
.submission-card { background:white; border:1px solid var(--border); border-radius:12px; overflow:hidden; transition:all 0.3s; }
.submission-card:hover { border-color:var(--primary); box-shadow:0 5px 15px rgba(99,102,241,0.1); }
.submission-header { display:flex; align-items:center; justify-content:space-between; padding:15px 20px; background:#f9fafb; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:15px; }
.submission-author { display:flex; align-items:center; gap:12px; }
.author-avatar img { width:40px; height:40px; border-radius:50%; object-fit:cover; }
.avatar-small { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--secondary)); color:white; display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:700; }
.author-info { display:flex; flex-direction:column; }
.author-name { font-weight:700; color:var(--dark); font-size:0.95rem; }
.submission-date { font-size:0.75rem; color:var(--gray); display:flex; align-items:center; gap:4px; }
.challenge-link { display:inline-flex; align-items:center; gap:8px; padding:6px 14px; background:#fef3c7; border:1px solid #fbbf24; border-radius:30px; text-decoration:none; color:#92400e; font-size:0.8rem; font-weight:600; transition:all 0.2s; max-width:300px; }
.challenge-link:hover { background:#fde68a; }
.submission-content { display:flex; gap:20px; padding:20px; flex-wrap:wrap; }
.submission-image { flex:0 0 150px; }
.submission-image img { width:150px; height:150px; object-fit:cover; border-radius:8px; border:1px solid var(--border); }
.submission-description { flex:1; }
.submission-description p { color:#374151; line-height:1.7; font-size:0.95rem; margin-bottom:10px; }
.submission-link { display:inline-flex; align-items:center; gap:6px; color:var(--primary); text-decoration:none; font-size:0.85rem; font-weight:600; padding:5px 12px; background:var(--primary-light); border-radius:6px; transition:all 0.2s; }
.submission-link:hover { background:var(--primary); color:white; }
.submission-actions { display:flex; align-items:center; justify-content:space-between; padding:15px 20px; background:#f9fafb; border-top:1px solid var(--border); flex-wrap:wrap; gap:15px; }
.actions-left { display:flex; align-items:center; gap:15px; flex-wrap:wrap; }
.vote-btn { display:flex; align-items:center; gap:6px; padding:8px 16px; border:2px solid #d1d5db; border-radius:30px; background:white; color:#4b5563; font-size:0.85rem; font-weight:600; cursor:pointer; transition:all 0.2s; }
.vote-btn:hover, .vote-btn.voted { border-color:var(--danger); color:var(--danger); background:#fef2f2; }
.vote-btn i, .vote-display i { color:var(--danger); }
.vote-display { display:flex; align-items:center; gap:6px; padding:8px 16px; background:#f3f4f6; border-radius:30px; color:#4b5563; font-size:0.85rem; font-weight:600; }
.comments-toggle { display:flex; align-items:center; gap:6px; padding:8px 16px; background:none; border:none; color:#6b7280; font-size:0.85rem; font-weight:600; cursor:pointer; transition:all 0.2s; }
.comments-toggle:hover { color:var(--primary); }
.comments-toggle i { color:var(--primary); }
.view-link { display:inline-flex; align-items:center; gap:6px; padding:8px 20px; background:linear-gradient(135deg,var(--primary),var(--secondary)); color:white; border-radius:8px; text-decoration:none; font-size:0.85rem; font-weight:600; transition:all 0.3s; }
.view-link:hover { transform:translateX(5px); box-shadow:0 5px 15px rgba(99,102,241,0.3); }
.comments-section { padding:20px; background:#f9fafb; border-top:1px solid var(--border); }
.comments-list { max-height:300px; overflow-y:auto; margin-bottom:15px; }
.comment-item { display:flex; gap:12px; margin-bottom:15px; }
.comment-avatar-mini { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--secondary)); color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; flex-shrink:0; }
.comment-bubble { flex:1; background:white; border-radius:12px; padding:10px 14px; border:1px solid var(--border); }
.comment-meta { display:flex; align-items:center; gap:8px; margin-bottom:5px; }
.comment-author { font-weight:700; color:var(--dark); font-size:0.8rem; }
.comment-date { font-size:0.7rem; color:var(--gray); }
.comment-text { font-size:0.85rem; color:#374151; line-height:1.5; margin:0; }
.comment-form-wrapper { display:flex; align-items:center; gap:10px; margin-top:15px; }
.comment-form { flex:1; display:flex; align-items:center; gap:8px; background:white; border:1px solid #d1d5db; border-radius:30px; padding:4px 4px 4px 16px; }
.comment-form input { flex:1; border:none; outline:none; font-size:0.9rem; padding:8px 0; background:transparent; }
.comment-form button { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--secondary)); color:white; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 0.2s; }
.comment-form button:hover { transform:scale(1.05); }
@media(max-width:768px){
    .banner-content { flex-direction:column; text-align:center; }
    .profile-stats { width:100%; justify-content:center; }
    .profile-tabs { flex-direction:column; }
    .tab-btn { width:100%; justify-content:center; }
    .submission-content { flex-direction:column; }
    .submission-image { flex:none; width:100%; }
    .submission-image img { width:100%; height:auto; }
}
</style>

<script>
const LOGGED_IN = <?= $currentUserId ? 'true' : 'false' ?>;

function showTab(tabName, e) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tabName).classList.add('active');
    e.currentTarget.classList.add('active');
}

function voteSubmission(submissionId, btn) {
    if (!LOGGED_IN) { window.location.href = 'index.php?action=showLogin'; return; }
    fetch('index.php?action=vote', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'submission_id=' + submissionId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const countSpan = btn.querySelector('.vote-count');
            if (countSpan) countSpan.textContent = data.new_count;
            btn.classList.toggle('voted', data.action === 'added');
            btn.querySelector('.vote-label').textContent = data.action === 'added' ? 'Voté' : 'Voter';
        }
    });
}

function toggleComments(id) {
    const s = document.getElementById('comments-' + id);
    if (s.style.display === 'none') { s.style.display = 'block'; loadComments(id); }
    else { s.style.display = 'none'; }
}

function loadComments(id) {
    fetch('index.php?action=getComments&submission_id=' + id)
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('comments-list-' + id);
        list.innerHTML = '';
        if (!data.success || data.comments.length === 0) {
            list.innerHTML = '<p style="color:#6b7280;text-align:center;padding:10px;">Aucun commentaire</p>'; return;
        }
        data.comments.forEach(c => {
            list.insertAdjacentHTML('beforeend', `
                <div class="comment-item">
                    <div class="comment-avatar-mini">${c.username.charAt(0).toUpperCase()}</div>
                    <div class="comment-bubble">
                        <div class="comment-meta">
                            <span class="comment-author">${c.username}</span>
                            <span class="comment-date">${new Date(c.created_at).toLocaleDateString('fr-FR')}</span>
                        </div>
                        <p class="comment-text">${c.content}</p>
                    </div>
                </div>`);
        });
    });
}

function addComment(e, id) {
    e.preventDefault();
    const input = document.getElementById('comment-input-' + id);
    const content = input.value.trim();
    if (!content) return;
    fetch('index.php?action=addComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'submission_id=' + id + '&content=' + encodeURIComponent(content)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadComments(id);
            const c = document.querySelector(`#submission-${id} .comments-count`);
            if (c) c.textContent = parseInt(c.textContent || 0) + 1;
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>