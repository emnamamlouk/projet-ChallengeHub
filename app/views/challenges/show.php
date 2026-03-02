<?php
$title = htmlspecialchars($challenge['title']) . " - ChallengeHub";
$active_page = "challenge";
ob_start();
?>

<div class="cs-page">

    <!-- HEADER -->
    <div class="cs-header">
        <div class="cs-meta">
            <span class="cs-cat"><?= htmlspecialchars($challenge['category']) ?></span>
            <span class="cs-by">
                <i class="fas fa-user"></i>
                <a href="index.php?action=viewProfile&id=<?= $challenge['user_id'] ?>" class="cs-creator-link">
                    <?= htmlspecialchars($challenge['creator_name']) ?>
                </a>
            </span>
            <span class="cs-date"><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($challenge['created_at'])) ?></span>
        </div>
        <h1 class="cs-title"><?= htmlspecialchars($challenge['title']) ?></h1>
    </div>

    <!-- IMAGE -->
    <?php if(!empty($challenge['image'])): ?>
    <div class="cs-img-wrap">
        <img src="public/<?= htmlspecialchars($challenge['image']) ?>" alt="">
    </div>
    <?php endif; ?>

    <!-- DESCRIPTION -->
    <div class="cs-card">
        <h2 class="cs-card-title"><i class="fas fa-align-left"></i> Description du défi</h2>
        <p class="cs-desc"><?= nl2br(htmlspecialchars($challenge['description'])) ?></p>
    </div>

    <!-- ACTIONS -->
    <div class="cs-actions">
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['user_id'] == $challenge['user_id']): ?>
                <a href="index.php?action=editChallengeForm&id=<?= $challenge['id'] ?>" class="cs-btn-edit">
                    <i class="fas fa-edit"></i> Modifier
                </a>
                <button onclick="document.getElementById('deleteModal').style.display='flex'" class="cs-btn-delete">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            <?php else: ?>
                <a href="index.php?action=createSubmissionForm&challenge_id=<?= $challenge['id'] ?>" class="cs-btn-primary">
                    <i class="fas fa-plus-circle"></i> Participer
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a href="index.php?action=showLogin" class="cs-btn-primary">
                <i class="fas fa-sign-in-alt"></i> Connectez-vous pour participer
            </a>
        <?php endif; ?>
    </div>

    <!-- PARTICIPATIONS -->
    <div class="cs-card">
        <div class="cs-subs-header">
            <h2 class="cs-card-title" style="margin:0"><i class="fas fa-users"></i> Participations (<?= count($submissions) ?>)</h2>
        </div>
        <?php if(empty($submissions)): ?>
            <div class="cs-empty">
                <i class="fas fa-paper-plane"></i>
                <p>Aucune participation pour le moment — soyez le premier !</p>
            </div>
        <?php else: ?>
            <div class="cs-subs-list">
                <?php foreach($submissions as $sub): ?>
                <div class="cs-sub-item">
                    <div class="cs-sub-author">
                        <?php if(!empty($sub['avatar'])): ?>
                            <img src="public/<?= htmlspecialchars($sub['avatar']) ?>" class="cs-sub-avatar" alt="">
                        <?php else: ?>
                            <div class="cs-sub-avatar-init"><?= strtoupper(substr($sub['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <div>
                            <a href="index.php?action=viewProfile&id=<?= $sub['user_id'] ?>" class="cs-sub-name">
                                <?= htmlspecialchars($sub['username']) ?>
                            </a>
                            <div class="cs-sub-date"><i class="fas fa-clock"></i> <?= date('d/m/Y', strtotime($sub['created_at'])) ?></div>
                        </div>
                        <a href="index.php?action=showSubmission&id=<?= $sub['id'] ?>" class="cs-sub-voir">
                            <i class="fas fa-eye"></i> Voir
                        </a>
                    </div>
                    <div class="cs-sub-content">
                        <p><?= nl2br(htmlspecialchars($sub['description'])) ?></p>
                        <?php if(!empty($sub['image'])): ?>
                            <img src="public/<?= htmlspecialchars($sub['image']) ?>" class="cs-sub-img" alt="">
                        <?php endif; ?>
                        <?php if(!empty($sub['link'])): ?>
                            <a href="<?= htmlspecialchars($sub['link']) ?>" target="_blank" class="cs-sub-link">
                                <i class="fas fa-external-link-alt"></i> Voir le projet
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="cs-sub-footer">
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $sub['user_id']): ?>
                            <button class="cs-vote-btn <?= $sub['user_voted'] ? 'voted' : '' ?>"
                                    onclick="voteSubmission(<?= $sub['id'] ?>, this)">
                                <i class="fas fa-thumbs-up"></i>
                                <span id="votes-<?= $sub['id'] ?>"><?= $sub['votes_count'] ?></span> votes
                            </button>
                        <?php else: ?>
                            <span class="cs-vote-display">
                                <i class="fas fa-thumbs-up"></i>
                                <span id="votes-<?= $sub['id'] ?>"><?= $sub['votes_count'] ?></span> votes
                            </span>
                        <?php endif; ?>
                        <button class="cs-comment-toggle" onclick="toggleComments(<?= $sub['id'] ?>)">
                            <i class="fas fa-comment"></i> Commentaires
                        </button>
                    </div>
                    <div class="cs-comments" id="comments-<?= $sub['id'] ?>" style="display:none;">
                        <div class="cs-comments-list" id="comments-list-<?= $sub['id'] ?>"></div>
                        <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="cs-comment-form">
                            <div class="cs-comment-init"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                            <input type="text" id="comment-input-<?= $sub['id'] ?>" placeholder="Ajouter un commentaire..." />
                            <button onclick="addComment(<?= $sub['id'] ?>)"><i class="fas fa-paper-plane"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- COMMENTAIRES DU DÉFI -->
    <div class="cs-card">
        <h2 class="cs-card-title"><i class="fas fa-comments"></i> Discussion sur ce défi <span class="cs-comment-badge" id="challengeCommentCount"></span></h2>

        <?php if(isset($_SESSION['user_id'])): ?>
        <div class="cs-challenge-comment-form">
            <div class="cs-comment-init"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
            <input type="text" id="challenge-comment-input" placeholder="Donnez votre avis sur ce défi...">
            <button onclick="addChallengeComment(<?= $challenge['id'] ?>)"><i class="fas fa-paper-plane"></i></button>
        </div>
        <?php endif; ?>

        <div class="cs-challenge-comments-list" id="challenge-comments-list"></div>
    </div>

</div>

<!-- Modal suppression -->
<div id="deleteModal" class="cs-modal" onclick="if(event.target===this)this.style.display='none'">
    <div class="cs-modal-box">
        <div class="cs-modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h3>Supprimer ce défi ?</h3>
        <p>Cette action est irréversible. Toutes les participations seront supprimées.</p>
        <form action="index.php?action=deleteChallenge" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="challenge_id" value="<?= $challenge['id'] ?>">
            <div class="cs-modal-actions">
                <button type="button" onclick="document.getElementById('deleteModal').style.display='none'" class="cs-modal-cancel">Annuler</button>
                <button type="submit" class="cs-modal-confirm">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<style>
.cs-page { max-width: 800px; margin: 0 auto; }
.cs-header { margin-bottom: 20px; }
.cs-meta { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:10px; }
.cs-cat { background:linear-gradient(135deg,#667eea,#764ba2); color:white; padding:4px 14px; border-radius:20px; font-size:0.85rem; font-weight:700; }
.cs-by { font-size:0.9rem; color:#666; display:flex; align-items:center; gap:5px; }
.cs-by i { color:#667eea; }
.cs-creator-link { color:#667eea; text-decoration:none; font-weight:700; }
.cs-creator-link:hover { text-decoration:underline; }
.cs-date { font-size:0.88rem; color:#aaa; display:flex; align-items:center; gap:5px; }
.cs-title { font-size:2rem; font-weight:900; color:#222; line-height:1.2; overflow-wrap:break-word; word-break:break-word; }
.cs-img-wrap { border-radius:16px; overflow:hidden; margin-bottom:20px; box-shadow:0 4px 20px rgba(0,0,0,0.1); }
.cs-img-wrap img { width:100%; max-height:450px; object-fit:cover; display:block; }
.cs-card { background:white; border-radius:16px; padding:24px; margin-bottom:20px; box-shadow:0 2px 16px rgba(0,0,0,0.06); border:1px solid #f0f0f0; }
.cs-card-title { font-size:1.1rem; font-weight:800; color:#333; margin:0 0 16px; display:flex; align-items:center; gap:8px; }
.cs-card-title i { color:#667eea; }
.cs-desc { color:#555; line-height:1.8; font-size:0.97rem; overflow-wrap:break-word; word-break:break-word; white-space:pre-wrap; margin:0; }
.cs-actions { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
.cs-btn-primary { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border-radius:12px; text-decoration:none; font-weight:700; font-size:0.95rem; transition:all 0.3s; }
.cs-btn-primary:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(102,126,234,0.4); }
.cs-btn-edit { display:inline-flex; align-items:center; gap:8px; padding:12px 20px; background:#ede9fe; color:#667eea; border-radius:12px; text-decoration:none; font-weight:700; }
.cs-btn-delete { display:inline-flex; align-items:center; gap:8px; padding:12px 20px; background:#fee2e2; color:#ef4444; border:none; border-radius:12px; font-weight:700; cursor:pointer; }
.cs-subs-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.cs-empty { text-align:center; padding:40px; color:#ccc; }
.cs-empty i { font-size:2.5rem; margin-bottom:10px; display:block; }
.cs-subs-list { display:flex; flex-direction:column; gap:16px; }
.cs-sub-item { border:2px solid #f0f0f0; border-radius:14px; overflow:hidden; transition:border-color 0.2s; }
.cs-sub-item:hover { border-color:#c4b5fd; }
.cs-sub-author { display:flex; align-items:center; gap:12px; padding:14px 16px; background:#fafafa; border-bottom:1px solid #f0f0f0; }
.cs-sub-avatar { width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #ede9fe; }
.cs-sub-avatar-init { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:1rem; flex-shrink:0; }
.cs-sub-name { font-weight:800; color:#222; text-decoration:none; display:block; font-size:0.95rem; }
.cs-sub-name:hover { color:#667eea; }
.cs-sub-date { font-size:0.75rem; color:#aaa; margin-top:2px; }
.cs-sub-voir { margin-left:auto; display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:#ede9fe; color:#667eea; border-radius:8px; text-decoration:none; font-size:0.82rem; font-weight:700; white-space:nowrap; }
.cs-sub-voir:hover { background:#ddd6fe; }
.cs-sub-content { padding:16px; }
.cs-sub-content p { color:#555; line-height:1.7; font-size:0.93rem; margin:0 0 12px; overflow-wrap:break-word; word-break:break-word; }
.cs-sub-img { max-width:100%; border-radius:10px; margin-top:8px; display:block; }
.cs-sub-link { display:inline-flex; align-items:center; gap:6px; color:#667eea; font-weight:600; text-decoration:none; font-size:0.88rem; margin-top:8px; }
.cs-sub-footer { display:flex; align-items:center; gap:16px; padding:12px 16px; border-top:1px solid #f5f5f5; background:#fafafa; }
.cs-vote-btn { display:inline-flex; align-items:center; gap:7px; padding:8px 16px; background:#f5f5f5; border:2px solid #e0e0e0; border-radius:20px; cursor:pointer; font-weight:700; font-size:0.88rem; color:#666; transition:all 0.2s; }
.cs-vote-btn:hover { border-color:#667eea; color:#667eea; background:#f5f3ff; }
.cs-vote-btn.voted { background:#fef2f2; border-color:#ef4444; color:#ef4444; }
.cs-vote-display { display:inline-flex; align-items:center; gap:7px; font-size:0.88rem; color:#aaa; }
.cs-comment-toggle { display:inline-flex; align-items:center; gap:7px; background:none; border:none; color:#888; font-size:0.88rem; font-weight:600; cursor:pointer; padding:6px 10px; border-radius:8px; transition:all 0.2s; }
.cs-comment-toggle:hover { background:#f5f3ff; color:#667eea; }
.cs-comments { padding:16px; background:#f8f9fa; border-top:1px solid #f0f0f0; }
.cs-comments-list { margin-bottom:12px; }

/* Comment items in submissions */
.cs-comment-item { display:flex; gap:10px; margin-bottom:10px; }
.cs-comment-init-sm { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.78rem; flex-shrink:0; }
.cs-comment-init-xs { width:26px; height:26px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.7rem; flex-shrink:0; }
.cs-comment-body { background:white; border-radius:10px; padding:8px 12px; flex:1; }
.cs-comment-author { font-weight:700; color:#333; font-size:0.82rem; }
.cs-comment-date { font-size:0.72rem; color:#aaa; margin-left:8px; }
.cs-comment-text { font-size:0.85rem; color:#555; margin:4px 0 0; overflow-wrap:break-word; }
.cs-comment-actions-row { display:flex; gap:6px; margin-top:6px; }
.cs-reply-btn-sm { background:none; border:none; color:#667eea; cursor:pointer; font-size:0.75rem; font-weight:700; padding:2px 6px; border-radius:6px; display:flex; align-items:center; gap:3px; }
.cs-reply-btn-sm:hover { background:#ede9fe; }
.cs-del-btn-sm { background:none; border:none; color:#ddd; cursor:pointer; font-size:0.75rem; padding:2px 6px; border-radius:6px; }
.cs-del-btn-sm:hover { color:#ef4444; background:#fee2e2; }
.cs-sub-replies { margin-top:8px; padding-left:10px; border-left:3px solid #ede9fe; display:flex; flex-direction:column; gap:6px; }
.cs-sub-reply { display:flex; gap:8px; }
.cs-sub-reply-body { background:#f5f3ff; border-radius:8px; padding:6px 10px; flex:1; }
.cs-reply-form-inline { display:flex; gap:8px; align-items:center; margin-top:8px; }
.cs-reply-form-inline input { flex:1; padding:6px 12px; border:2px solid #e8e8e8; border-radius:20px; font-size:0.85rem; }
.cs-reply-form-inline input:focus { outline:none; border-color:#667eea; }
.cs-reply-form-inline button { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; cursor:pointer; font-size:0.8rem; flex-shrink:0; }
.cs-reply-form-inline .cs-cancel-sm { background:#fee2e2; color:#ef4444; }

.cs-comment-form { display:flex; gap:8px; align-items:center; }
.cs-comment-form input { flex:1; padding:8px 14px; border:2px solid #e8e8e8; border-radius:20px; font-size:0.88rem; }
.cs-comment-form input:focus { outline:none; border-color:#667eea; }
.cs-comment-form button { width:36px; height:36px; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; border-radius:50%; cursor:pointer; font-size:0.85rem; flex-shrink:0; }
.cs-comment-init { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.78rem; flex-shrink:0; }

/* Challenge comments section */
.cs-comment-badge { background:#667eea; color:white; font-size:0.7rem; font-weight:800; padding:2px 8px; border-radius:20px; margin-left:4px; }
.cs-challenge-comment-form { display:flex; gap:8px; align-items:center; margin-bottom:16px; }
.cs-challenge-comment-form input { flex:1; padding:10px 16px; border:2px solid #e8e8e8; border-radius:25px; font-size:0.9rem; }
.cs-challenge-comment-form input:focus { outline:none; border-color:#667eea; }
.cs-challenge-comment-form button { width:38px; height:38px; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; border-radius:50%; cursor:pointer; font-size:0.88rem; flex-shrink:0; }
.cs-challenge-comments-list { display:flex; flex-direction:column; gap:12px; }
.cs-chall-comment { display:flex; gap:10px; }
.cs-chall-comment-bubble { flex:1; background:#f8f9fb; border-radius:12px; padding:12px 14px; border:1px solid #f0f0f0; }
.cs-chall-comment-meta { display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap; }
.cs-chall-comment-meta strong { font-size:0.85rem; color:#222; }
.cs-chall-comment-meta span { font-size:0.74rem; color:#aaa; }
.cs-chall-comment-meta-actions { margin-left:auto; display:flex; gap:6px; }
.cs-chall-replies { margin-top:10px; padding-left:10px; border-left:3px solid #ede9fe; display:flex; flex-direction:column; gap:8px; }
.cs-chall-reply { display:flex; gap:8px; }
.cs-chall-reply-bubble { flex:1; background:white; border-radius:10px; padding:8px 12px; border:1px solid #eee; }
.cs-chall-reply-bubble p { font-size:0.85rem; color:#555; margin:0; }
.cs-chall-reply-form { display:flex; gap:8px; align-items:center; margin-top:8px; }
.cs-chall-reply-form input { flex:1; padding:6px 12px; border:2px solid #e8e8e8; border-radius:20px; font-size:0.85rem; }
.cs-chall-reply-form input:focus { outline:none; border-color:#667eea; }
.cs-chall-reply-form button { width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; cursor:pointer; font-size:0.78rem; flex-shrink:0; }
.cs-chall-reply-form .cs-cancel-sm { background:#fee2e2; color:#ef4444; }

.cs-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000; }
.cs-modal-box { background:white; padding:32px; border-radius:20px; max-width:400px; width:90%; text-align:center; }
.cs-modal-icon { font-size:2.5rem; color:#f59e0b; margin-bottom:12px; }
.cs-modal-box h3 { font-size:1.2rem; color:#222; margin:0 0 8px; }
.cs-modal-box p { color:#777; font-size:0.9rem; margin:0 0 24px; }
.cs-modal-actions { display:flex; gap:10px; justify-content:center; }
.cs-modal-cancel { padding:10px 24px; background:#f0f0f0; border:none; border-radius:10px; cursor:pointer; font-weight:600; color:#666; }
.cs-modal-confirm { padding:10px 24px; background:#ef4444; color:white; border:none; border-radius:10px; cursor:pointer; font-weight:700; }
@media(max-width:600px) { .cs-title { font-size:1.5rem; } .cs-meta { gap:8px; } }
</style>

<script>
const CHALLENGE_ID = <?= $challenge['id'] ?>;
const LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
const MY_INITIAL_CS = "<?= isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : '' ?>";

// ===================== SUBMISSIONS COMMENTS =====================
function voteSubmission(submissionId, btn) {
    fetch('index.php?action=vote', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'submission_id=' + submissionId
    }).then(r => r.json()).then(data => {
        if(data.success) {
            document.getElementById('votes-' + submissionId).textContent = data.votes_count ?? data.new_count;
            btn.classList.toggle('voted', data.voted ?? data.action === 'added');
        } else if(data.message) alert(data.message);
    });
}

function toggleComments(submissionId) {
    const section = document.getElementById('comments-' + submissionId);
    if(section.style.display === 'none') {
        section.style.display = 'block';
        loadSubmissionComments(submissionId);
    } else {
        section.style.display = 'none';
    }
}

function loadSubmissionComments(submissionId) {
    fetch('index.php?action=getComments&submission_id=' + submissionId)
    .then(r => r.json()).then(data => {
        if(!data.success) return;
        const list = document.getElementById('comments-list-' + submissionId);
        list.innerHTML = '';
        if(data.comments.length === 0) {
            list.innerHTML = '<p style="color:#ccc;font-size:0.85rem;text-align:center;padding:8px 0">Aucun commentaire</p>';
            return;
        }
        data.comments.forEach(c => {
            list.innerHTML += buildSubmissionComment(c, submissionId);
        });
    });
}

function buildSubmissionComment(c, submissionId) {
    const date = new Date(c.created_at).toLocaleDateString('fr-FR');
    const canDelete = LOGGED_IN; // simplification — server checks ownership anyway
    let repliesHtml = '';
    if(c.replies && c.replies.length > 0) {
        repliesHtml = '<div class="cs-sub-replies">' + c.replies.map(r => {
            const rd = new Date(r.created_at).toLocaleDateString('fr-FR');
            return `<div class="cs-sub-reply" id="subcomment-${r.id}">
                <div class="cs-comment-init-xs">${r.username.charAt(0).toUpperCase()}</div>
                <div class="cs-sub-reply-body">
                    <span class="cs-comment-author">${r.username}</span>
                    <span class="cs-comment-date">${rd}</span>
                    ${LOGGED_IN ? `<button class="cs-del-btn-sm" style="float:right" onclick="deleteSubComment(${r.id}, ${submissionId})"><i class="fas fa-trash"></i></button>` : ''}
                    <p class="cs-comment-text">${r.content}</p>
                </div>
            </div>`;
        }).join('') + '</div>';
    }
    return `<div class="cs-comment-item" id="subcomment-${c.id}">
        <div class="cs-comment-init-sm">${c.username.charAt(0).toUpperCase()}</div>
        <div class="cs-comment-body">
            <span class="cs-comment-author">${c.username}</span>
            <span class="cs-comment-date">${date}</span>
            ${LOGGED_IN ? `<button class="cs-del-btn-sm" style="float:right" onclick="deleteSubComment(${c.id}, ${submissionId})"><i class="fas fa-trash"></i></button>` : ''}
            <p class="cs-comment-text">${c.content}</p>
            ${repliesHtml}
            ${LOGGED_IN ? `
            <div class="cs-comment-actions-row">
                <button class="cs-reply-btn-sm" onclick="toggleSubReplyForm(${c.id}, ${submissionId}, '${c.username}')"><i class="fas fa-reply"></i> Répondre</button>
            </div>
            <div class="cs-reply-form-inline" id="sub-reply-form-${c.id}" style="display:none;">
                <div class="cs-comment-init-xs">${MY_INITIAL_CS}</div>
                <input type="text" id="sub-reply-input-${c.id}" placeholder="Répondre à ${c.username}...">
                <button onclick="submitSubReply(${c.id}, ${submissionId})"><i class="fas fa-paper-plane"></i></button>
                <button class="cs-cancel-sm" onclick="document.getElementById('sub-reply-form-${c.id}').style.display='none'"><i class="fas fa-times"></i></button>
            </div>` : ''}
        </div>
    </div>`;
}

function addComment(submissionId) {
    const input = document.getElementById('comment-input-' + submissionId);
    const content = input.value.trim();
    if(!content) return;
    fetch('index.php?action=addComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'submission_id=' + submissionId + '&content=' + encodeURIComponent(content)
    }).then(r => r.json()).then(data => {
        if(data.success) { input.value = ''; loadSubmissionComments(submissionId); }
    });
}

function toggleSubReplyForm(commentId, submissionId, username) {
    document.querySelectorAll('.cs-reply-form-inline').forEach(f => f.style.display = 'none');
    const form = document.getElementById('sub-reply-form-' + commentId);
    if(form) form.style.display = 'flex';
}

function submitSubReply(parentId, submissionId) {
    const input = document.getElementById('sub-reply-input-' + parentId);
    const content = input.value.trim();
    if(!content) return;
    fetch('index.php?action=addComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'submission_id=' + submissionId + '&content=' + encodeURIComponent(content) + '&parent_id=' + parentId
    }).then(r => r.json()).then(data => {
        if(data.success) { loadSubmissionComments(submissionId); }
    });
}

function deleteSubComment(commentId, submissionId) {
    if(!confirm('Supprimer ce commentaire ?')) return;
    fetch('index.php?action=deleteComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'comment_id=' + commentId
    }).then(r => r.json()).then(data => {
        if(data.success) loadSubmissionComments(submissionId);
    });
}

// ===================== CHALLENGE COMMENTS =====================
function loadChallengeComments() {
    fetch('index.php?action=getChallengeComments&challenge_id=' + CHALLENGE_ID)
    .then(r => r.json()).then(data => {
        if(!data.success) return;
        const list = document.getElementById('challenge-comments-list');
        const badge = document.getElementById('challengeCommentCount');
        list.innerHTML = '';
        if(badge) badge.textContent = data.comments.length;
        if(data.comments.length === 0) {
            list.innerHTML = '<p style="color:#ccc;font-size:0.88rem;text-align:center;padding:20px">Aucune discussion pour le moment</p>';
            return;
        }
        data.comments.forEach(c => {
            list.appendChild(buildChallengeComment(c));
        });
    });
}

function buildChallengeComment(c) {
    const div = document.createElement('div');
    div.className = 'cs-chall-comment';
    div.id = 'challcomment-' + c.id;
    const date = new Date(c.created_at).toLocaleDateString('fr-FR');
    let repliesHtml = '';
    if(c.replies && c.replies.length > 0) {
        repliesHtml = '<div class="cs-chall-replies">' + c.replies.map(r => {
            const rd = new Date(r.created_at).toLocaleDateString('fr-FR');
            return `<div class="cs-chall-reply" id="challcomment-${r.id}">
                <div class="cs-comment-init-xs">${r.username.charAt(0).toUpperCase()}</div>
                <div class="cs-chall-reply-bubble">
                    <div class="cs-chall-comment-meta">
                        <strong>${r.username}</strong><span>${rd}</span>
                        <div class="cs-chall-comment-meta-actions">
                            ${LOGGED_IN ? `<button class="cs-del-btn-sm" onclick="deleteChallengeComment(${r.id})"><i class="fas fa-trash"></i></button>` : ''}
                        </div>
                    </div>
                    <p>${r.content}</p>
                </div>
            </div>`;
        }).join('') + '</div>';
    }
    div.innerHTML = `
        <div class="cs-comment-init-sm">${c.username.charAt(0).toUpperCase()}</div>
        <div class="cs-chall-comment-bubble">
            <div class="cs-chall-comment-meta">
                <strong>${c.username}</strong><span>${date}</span>
                <div class="cs-chall-comment-meta-actions">
                    ${LOGGED_IN ? `<button class="cs-reply-btn-sm" onclick="showChallengeReplyForm(${c.id}, '${c.username}')"><i class="fas fa-reply"></i> Répondre</button>` : ''}
                    ${LOGGED_IN ? `<button class="cs-del-btn-sm" onclick="deleteChallengeComment(${c.id})"><i class="fas fa-trash"></i></button>` : ''}
                </div>
            </div>
            <p>${c.content}</p>
            ${repliesHtml}
            ${LOGGED_IN ? `
            <div class="cs-chall-reply-form" id="chall-reply-form-${c.id}" style="display:none;">
                <div class="cs-comment-init-xs">${MY_INITIAL_CS}</div>
                <input type="text" id="chall-reply-input-${c.id}" placeholder="Répondre à ${c.username}...">
                <button onclick="submitChallengeReply(${c.id})"><i class="fas fa-paper-plane"></i></button>
                <button class="cs-cancel-sm" onclick="document.getElementById('chall-reply-form-${c.id}').style.display='none'"><i class="fas fa-times"></i></button>
            </div>` : ''}
        </div>`;
    return div;
}

function addChallengeComment(challengeId) {
    const input = document.getElementById('challenge-comment-input');
    const content = input.value.trim();
    if(!content) return;
    fetch('index.php?action=addChallengeComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'challenge_id=' + challengeId + '&content=' + encodeURIComponent(content)
    }).then(r => r.json()).then(data => {
        if(data.success) { input.value = ''; loadChallengeComments(); }
    });
}

function showChallengeReplyForm(commentId, username) {
    document.querySelectorAll('.cs-chall-reply-form').forEach(f => f.style.display = 'none');
    const form = document.getElementById('chall-reply-form-' + commentId);
    if(form) form.style.display = 'flex';
}

function submitChallengeReply(parentId) {
    const input = document.getElementById('chall-reply-input-' + parentId);
    const content = input.value.trim();
    if(!content) return;
    fetch('index.php?action=addChallengeComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'challenge_id=' + CHALLENGE_ID + '&content=' + encodeURIComponent(content) + '&parent_id=' + parentId
    }).then(r => r.json()).then(data => {
        if(data.success) loadChallengeComments();
    });
}

function deleteChallengeComment(commentId) {
    if(!confirm('Supprimer ce commentaire ?')) return;
    fetch('index.php?action=deleteChallengeComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'comment_id=' + commentId
    }).then(r => r.json()).then(data => {
        if(data.success) loadChallengeComments();
    });
}

// Charger les commentaires du défi au chargement de la page
document.addEventListener('DOMContentLoaded', loadChallengeComments);
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>