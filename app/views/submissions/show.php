<?php
$title = "Participation de " . htmlspecialchars($submission['username']) . " — ChallengeHub";
$active_page = "";
ob_start();
?>

<div class="sv-page">

    <!-- BREADCRUMB -->
    <div class="sv-breadcrumb">
        <a href="index.php?action=home"><i class="fas fa-home"></i> Accueil</a>
        <i class="fas fa-chevron-right sv-sep"></i>
        <a href="index.php?action=showChallenge&id=<?= $submission['challenge_id'] ?>"><i class="fas fa-trophy"></i> Le défi</a>
        <i class="fas fa-chevron-right sv-sep"></i>
        <span><?= htmlspecialchars($submission['username']) ?></span>
    </div>

    <div class="sv-grid">
        <div class="sv-main">

            <!-- AUTEUR -->
            <div class="sv-card sv-author-card">
                <div class="sv-author-row">
                    <?php if(!empty($submission['avatar'])): ?>
                        <img src="public/<?= htmlspecialchars($submission['avatar']) ?>" class="sv-avatar" alt="">
                    <?php else: ?>
                        <div class="sv-avatar-init"><?= strtoupper(substr($submission['username'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="sv-author-info">
                        <a href="index.php?action=viewProfile&id=<?= $submission['user_id'] ?>" class="sv-author-name">
                            <?= htmlspecialchars($submission['username']) ?>
                        </a>
                        <span class="sv-author-date">
                            <i class="far fa-clock"></i>
                            <?= date('d/m/Y à H:i', strtotime($submission['created_at'])) ?>
                        </span>
                    </div>
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $submission['user_id']): ?>
                    <div class="sv-owner-btns">
                        <a href="index.php?action=editSubmissionForm&id=<?= $submission['id'] ?>" class="sv-btn-edit">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <form action="index.php?action=deleteSubmission" method="POST" style="display:inline" onsubmit="return confirm('Supprimer ?')">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                            <button type="submit" class="sv-btn-del"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- IMAGE -->
            <?php if(!empty($submission['image'])): ?>
            <div class="sv-card sv-img-card">
                <img src="public/<?= htmlspecialchars($submission['image']) ?>" alt="Participation" class="sv-img">
            </div>
            <?php endif; ?>

            <!-- DESCRIPTION -->
            <div class="sv-card">
                <div class="sv-card-label"><i class="fas fa-align-left"></i> Description</div>
                <p class="sv-desc"><?= nl2br(htmlspecialchars($submission['description'])) ?></p>
                <?php if(!empty($submission['link'])): ?>
                <a href="<?= htmlspecialchars($submission['link']) ?>" target="_blank" rel="noopener" class="sv-proj-link">
                    <i class="fas fa-external-link-alt"></i> Voir le projet
                </a>
                <?php endif; ?>
            </div>

            <!-- VOTE + LIEN DÉFI -->
            <div class="sv-card sv-action-row">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['user_id'] != $submission['user_id']): ?>
                    <button class="sv-vote-btn <?= $submission['user_voted'] ? 'sv-voted' : '' ?>"
                            data-id="<?= $submission['id'] ?>" onclick="doVote(this)">
                        <i class="<?= $submission['user_voted'] ? 'fas' : 'far' ?> fa-thumbs-up"></i>
                        <span class="sv-vote-count"><?= $submission['votes_count'] ?></span>
                        <span class="sv-vote-lbl"><?= $submission['user_voted'] ? 'Voté' : 'Voter' ?></span>
                    </button>
                    <?php else: ?>
                    <div class="sv-vote-own">
                        <i class="fas fa-thumbs-up"></i>
                        <strong><?= $submission['votes_count'] ?></strong>
                        vote<?= $submission['votes_count'] > 1 ? 's' : '' ?> reçus
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="index.php?action=showLogin" class="sv-vote-btn">
                        <i class="fas fa-sign-in-alt"></i> Connectez-vous pour voter
                    </a>
                <?php endif; ?>
                <a href="index.php?action=showChallenge&id=<?= $submission['challenge_id'] ?>" class="sv-defi-btn">
                    <i class="fas fa-trophy"></i> Voir le défi
                </a>
            </div>

            <!-- COMMENTAIRES -->
            <div class="sv-card sv-comments-card">
                <div class="sv-card-label">
                    <i class="fas fa-comments"></i> Commentaires
                    <span class="sv-comment-count" id="mainCommentCount"><?= count($comments) ?></span>
                </div>

                <?php if(isset($_SESSION['user_id'])): ?>
                <form id="commentForm" class="sv-comment-form">
                    <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                    <div class="sv-comment-input-row">
                        <div class="sv-av-sm"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                        <input type="text" name="content" id="commentContent" placeholder="Écrire un commentaire..." required class="sv-comment-input">
                        <button type="submit" class="sv-comment-send"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
                <?php endif; ?>

                <div class="sv-comments-list" id="sv-comments-list">
                    <?php if(!empty($comments)): ?>
                        <?php foreach($comments as $c): ?>
                        <div class="sv-comment" id="comment-<?= $c['id'] ?>">
                            <div class="sv-av-sm"><?= strtoupper(substr($c['username'], 0, 1)) ?></div>
                            <div class="sv-comment-bubble">
                                <div class="sv-comment-meta">
                                    <strong><?= htmlspecialchars($c['username']) ?></strong>
                                    <span><?= date('d/m/Y', strtotime($c['created_at'])) ?></span>
                                    <div class="sv-comment-actions">
                                        <?php if(isset($_SESSION['user_id'])): ?>
                                        <button class="sv-reply-btn" onclick="showReplyForm(<?= $c['id'] ?>, '<?= htmlspecialchars($c['username']) ?>')">
                                            <i class="fas fa-reply"></i> Répondre
                                        </button>
                                        <?php endif; ?>
                                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $c['user_id']): ?>
                                        <button class="sv-del-comment" onclick="deleteComment(<?= $c['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p><?= htmlspecialchars($c['content']) ?></p>

                                <!-- Réponses -->
                                <?php if(!empty($c['replies'])): ?>
                                <div class="sv-replies">
                                    <?php foreach($c['replies'] as $r): ?>
                                    <div class="sv-reply" id="comment-<?= $r['id'] ?>">
                                        <div class="sv-av-sm sv-av-xs"><?= strtoupper(substr($r['username'], 0, 1)) ?></div>
                                        <div class="sv-reply-bubble">
                                            <div class="sv-comment-meta">
                                                <strong><?= htmlspecialchars($r['username']) ?></strong>
                                                <span><?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
                                                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $r['user_id']): ?>
                                                <button class="sv-del-comment" onclick="deleteComment(<?= $r['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                            <p><?= htmlspecialchars($r['content']) ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <!-- Formulaire de réponse -->
                                <?php if(isset($_SESSION['user_id'])): ?>
                                <div class="sv-reply-form" id="reply-form-<?= $c['id'] ?>" style="display:none;">
                                    <div class="sv-comment-input-row" style="margin-top:8px;">
                                        <div class="sv-av-sm sv-av-xs"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                                        <input type="text" id="reply-input-<?= $c['id'] ?>" class="sv-comment-input" placeholder="Répondre...">
                                        <button class="sv-comment-send" onclick="submitReply(<?= $c['id'] ?>, <?= $submission['id'] ?>)"><i class="fas fa-paper-plane"></i></button>
                                        <button class="sv-cancel-reply" onclick="hideReplyForm(<?= $c['id'] ?>)"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="sv-no-comments" id="sv-no-comments">
                            <i class="fas fa-comment-slash"></i>
                            <p>Aucun commentaire — soyez le premier !</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- SIDEBAR -->
        <aside class="sv-sidebar">
            <div class="sv-card sv-sidebar-card">
                <div class="sv-sidebar-title">À propos du défi</div>
                <div class="sv-sidebar-defi-link">
                    <i class="fas fa-trophy"></i>
                    <a href="index.php?action=showChallenge&id=<?= $submission['challenge_id'] ?>">Voir le défi complet</a>
                </div>
                <hr class="sv-hr">
                <div class="sv-sidebar-stats">
                    <div class="sv-stat">
                        <span class="sv-stat-num"><?= $submission['votes_count'] ?></span>
                        <span class="sv-stat-lbl">Votes</span>
                    </div>
                    <div class="sv-stat">
                        <span class="sv-stat-num" id="sidebarCommentCount"><?= count($comments) ?></span>
                        <span class="sv-stat-lbl">Commentaires</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<style>
.sv-page { max-width: 1000px; margin: 0 auto; }
.sv-breadcrumb { display:flex; align-items:center; gap:8px; font-size:0.83rem; color:#aaa; margin-bottom:22px; flex-wrap:wrap; }
.sv-breadcrumb a { color:#667eea; text-decoration:none; display:flex; align-items:center; gap:5px; font-weight:600; }
.sv-breadcrumb a:hover { text-decoration:underline; }
.sv-sep { font-size:0.65rem; color:#ddd; }
.sv-grid { display:grid; grid-template-columns: 1fr 280px; gap:20px; align-items:start; }
.sv-card { background:white; border-radius:14px; padding:20px; margin-bottom:14px; box-shadow:0 2px 12px rgba(0,0,0,0.06); border:1px solid #f0f0f0; }
.sv-card-label { display:flex; align-items:center; gap:8px; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#888; margin-bottom:14px; }
.sv-card-label i { color:#667eea; }
.sv-author-row { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
.sv-avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; border:3px solid #ede9fe; }
.sv-avatar-init { width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; display:flex; align-items:center; justify-content:center; font-size:1.2rem; font-weight:900; flex-shrink:0; }
.sv-author-info { flex:1; }
.sv-author-name { font-size:1.05rem; font-weight:800; color:#222; text-decoration:none; display:block; }
.sv-author-name:hover { color:#667eea; }
.sv-author-date { font-size:0.78rem; color:#aaa; display:flex; align-items:center; gap:5px; margin-top:3px; }
.sv-owner-btns { display:flex; gap:8px; margin-left:auto; }
.sv-btn-edit { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; background:#ede9fe; color:#667eea; border-radius:8px; text-decoration:none; font-size:0.82rem; font-weight:700; }
.sv-btn-del { display:inline-flex; align-items:center; padding:7px 12px; background:#fee2e2; color:#ef4444; border:none; border-radius:8px; cursor:pointer; font-size:0.85rem; }
.sv-img-card { padding:0; overflow:hidden; }
.sv-img { width:100%; max-height:460px; object-fit:cover; display:block; }
.sv-desc { font-size:0.97rem; color:#444; line-height:1.85; margin:0 0 14px; overflow-wrap:break-word; word-break:break-word; }
.sv-proj-link { display:inline-flex; align-items:center; gap:7px; color:#667eea; font-weight:700; text-decoration:none; padding:8px 16px; background:#f5f3ff; border-radius:8px; font-size:0.88rem; }
.sv-proj-link:hover { background:#ede9fe; }
.sv-action-row { display:flex; align-items:center; gap:14px; flex-wrap:wrap; padding:16px 20px; }
.sv-vote-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 20px; background:none; border:1px solid #e0e0e0; border-radius:8px; cursor:pointer; font-size:0.92rem; font-weight:600; color:#555; transition:all 0.2s; text-decoration:none; }
.sv-vote-btn i { color:#667eea; font-size:1rem; transition:transform 0.2s; }
.sv-vote-btn:hover { background:#f4f6f8; color:#667eea; }
.sv-vote-btn:hover i { transform:scale(1.2); }
.sv-vote-btn.sv-voted { color:#ef4444; border-color:#fca5a5; background:#fff5f5; }
.sv-vote-btn.sv-voted i { color:#ef4444; }
.sv-vote-count { font-weight:800; font-size:1rem; }
.sv-vote-own { display:flex; align-items:center; gap:7px; color:#667eea; font-size:0.9rem; font-weight:600; }
.sv-vote-own i { color:#667eea; }
.sv-defi-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 20px; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.9rem; margin-left:auto; transition:all 0.25s; }
.sv-defi-btn:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(102,126,234,0.35); }
.sv-comment-count { background:#667eea; color:white; font-size:0.7rem; font-weight:800; padding:2px 8px; border-radius:20px; margin-left:4px; }
.sv-comment-form { margin-bottom:18px; }
.sv-comment-input-row { display:flex; align-items:center; gap:10px; background:#f8f8fc; border:2px solid #eeeefc; border-radius:30px; padding:5px 5px 5px 14px; transition:border-color 0.2s; }
.sv-comment-input-row:focus-within { border-color:#667eea; }
.sv-av-sm { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.8rem; flex-shrink:0; }
.sv-av-xs { width:26px; height:26px; font-size:0.7rem; }
.sv-comment-input { flex:1; border:none; background:transparent; font-size:0.88rem; outline:none; color:#333; }
.sv-comment-input::placeholder { color:#bbb; }
.sv-comment-send { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.82rem; flex-shrink:0; transition:transform 0.2s; }
.sv-comment-send:hover { transform:scale(1.1); }
.sv-cancel-reply { width:30px; height:30px; border-radius:50%; background:#fee2e2; color:#ef4444; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.75rem; flex-shrink:0; }
.sv-comments-list { display:flex; flex-direction:column; gap:10px; }
.sv-comment { display:flex; gap:10px; }
.sv-comment-bubble { flex:1; background:#f8f9fb; border-radius:12px; padding:12px 14px; border:1px solid #f0f0f0; }
.sv-comment-meta { display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap; }
.sv-comment-meta strong { font-size:0.85rem; color:#222; }
.sv-comment-meta span { font-size:0.74rem; color:#aaa; }
.sv-comment-bubble p { font-size:0.88rem; color:#444; line-height:1.55; margin:0; overflow-wrap:break-word; }
.sv-comment-actions { margin-left:auto; display:flex; gap:6px; align-items:center; }
.sv-reply-btn { background:none; border:none; color:#667eea; cursor:pointer; font-size:0.75rem; font-weight:700; padding:2px 6px; border-radius:6px; display:flex; align-items:center; gap:4px; }
.sv-reply-btn:hover { background:#ede9fe; }
.sv-del-comment { background:none; border:none; color:#ddd; cursor:pointer; padding:2px 4px; font-size:0.75rem; border-radius:6px; }
.sv-del-comment:hover { color:#ef4444; background:#fee2e2; }
.sv-replies { margin-top:10px; display:flex; flex-direction:column; gap:8px; padding-left:10px; border-left:3px solid #ede9fe; }
.sv-reply { display:flex; gap:8px; }
.sv-reply-bubble { flex:1; background:white; border-radius:10px; padding:8px 12px; border:1px solid #eee; }
.sv-reply-bubble p { font-size:0.85rem; color:#555; margin:0; }
.sv-no-comments { text-align:center; padding:28px; color:#ccc; }
.sv-no-comments i { font-size:1.8rem; margin-bottom:8px; display:block; }
.sv-no-comments p { font-size:0.88rem; }
.sv-sidebar-card { margin-bottom:0; }
.sv-sidebar-title { font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#888; margin-bottom:14px; }
.sv-sidebar-defi-link { display:flex; align-items:center; gap:10px; padding:12px; background:#f5f3ff; border-radius:10px; margin-bottom:14px; }
.sv-sidebar-defi-link i { color:#667eea; font-size:1rem; }
.sv-sidebar-defi-link a { color:#667eea; font-weight:700; text-decoration:none; font-size:0.9rem; }
.sv-sidebar-defi-link a:hover { text-decoration:underline; }
.sv-hr { border:none; border-top:1px solid #f0f0f0; margin:14px 0; }
.sv-sidebar-stats { display:flex; gap:10px; }
.sv-stat { flex:1; text-align:center; padding:12px; background:#f8f9fc; border-radius:10px; }
.sv-stat-num { display:block; font-size:1.5rem; font-weight:900; color:#667eea; line-height:1; }
.sv-stat-lbl { display:block; font-size:0.72rem; color:#aaa; margin-top:4px; text-transform:uppercase; letter-spacing:0.5px; }
@media(max-width:700px) { .sv-grid { grid-template-columns:1fr; } .sv-sidebar { display:none; } }
</style>

<script>
const LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
const MY_INITIAL = "<?= isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : '' ?>";

function doVote(btn) {
    const id = btn.dataset.id;
    fetch('index.php?action=vote', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'submission_id=' + id
    }).then(r => r.json()).then(data => {
        if(data.success) {
            const voted = data.action === 'added' || data.voted;
            const count = data.new_count ?? data.votes_count;
            btn.querySelector('.sv-vote-count').textContent = count;
            btn.querySelector('.sv-vote-lbl').textContent = voted ? 'Voté' : 'Voter';
            btn.querySelector('i').className = voted ? 'fas fa-thumbs-up' : 'far fa-thumbs-up';
            btn.classList.toggle('sv-voted', voted);
        }
    });
}

// Ajouter commentaire principal
const commentForm = document.getElementById('commentForm');
if(commentForm) {
    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('commentContent');
        const content = input.value.trim();
        if(!content) return;
        const submissionId = this.querySelector('[name="submission_id"]').value;
        fetch('index.php?action=addComment', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'submission_id=' + encodeURIComponent(submissionId) + '&content=' + encodeURIComponent(content)
        }).then(r => r.json()).then(data => {
            if(data.success) {
                input.value = '';
                document.getElementById('sv-no-comments')?.remove();
                appendComment(data, submissionId);
                updateCounters(1);
            }
        });
    });
}

function appendComment(data, submissionId) {
    const list = document.getElementById('sv-comments-list');
    const date = new Date(data.created_at).toLocaleDateString('fr-FR');
    const div = document.createElement('div');
    div.className = 'sv-comment';
    div.id = 'comment-' + data.comment_id;
    div.innerHTML = `
        <div class="sv-av-sm">${data.username.charAt(0).toUpperCase()}</div>
        <div class="sv-comment-bubble">
            <div class="sv-comment-meta">
                <strong>${data.username}</strong>
                <span>${date}</span>
                <div class="sv-comment-actions">
                    ${LOGGED_IN ? `<button class="sv-reply-btn" onclick="showReplyForm(${data.comment_id}, '${data.username}')"><i class="fas fa-reply"></i> Répondre</button>` : ''}
                    <button class="sv-del-comment" onclick="deleteComment(${data.comment_id})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <p>${data.content}</p>
            <div class="sv-replies" id="replies-${data.comment_id}"></div>
            <div class="sv-reply-form" id="reply-form-${data.comment_id}" style="display:none;">
                <div class="sv-comment-input-row" style="margin-top:8px;">
                    <div class="sv-av-sm sv-av-xs">${MY_INITIAL}</div>
                    <input type="text" id="reply-input-${data.comment_id}" class="sv-comment-input" placeholder="Répondre...">
                    <button class="sv-comment-send" onclick="submitReply(${data.comment_id}, ${submissionId})"><i class="fas fa-paper-plane"></i></button>
                    <button class="sv-cancel-reply" onclick="hideReplyForm(${data.comment_id})"><i class="fas fa-times"></i></button>
                </div>
            </div>
        </div>`;
    list.appendChild(div);
}

function showReplyForm(commentId, username) {
    document.querySelectorAll('.sv-reply-form').forEach(f => f.style.display = 'none');
    const form = document.getElementById('reply-form-' + commentId);
    if(form) {
        form.style.display = 'block';
        const input = document.getElementById('reply-input-' + commentId);
        if(input) { input.focus(); input.placeholder = 'Répondre à ' + username + '...'; }
    }
}

function hideReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if(form) form.style.display = 'none';
}

function submitReply(parentId, submissionId) {
    const input = document.getElementById('reply-input-' + parentId);
    const content = input.value.trim();
    if(!content) return;
    fetch('index.php?action=addComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'submission_id=' + submissionId + '&content=' + encodeURIComponent(content) + '&parent_id=' + parentId
    }).then(r => r.json()).then(data => {
        if(data.success) {
            input.value = '';
            hideReplyForm(parentId);
            let repliesDiv = document.getElementById('replies-' + parentId);
            if(!repliesDiv) {
                const bubble = document.querySelector('#comment-' + parentId + ' .sv-comment-bubble');
                repliesDiv = document.createElement('div');
                repliesDiv.className = 'sv-replies';
                repliesDiv.id = 'replies-' + parentId;
                bubble.appendChild(repliesDiv);
            }
            const date = new Date(data.created_at).toLocaleDateString('fr-FR');
            const div = document.createElement('div');
            div.className = 'sv-reply';
            div.id = 'comment-' + data.comment_id;
            div.innerHTML = `
                <div class="sv-av-sm sv-av-xs">${data.username.charAt(0).toUpperCase()}</div>
                <div class="sv-reply-bubble">
                    <div class="sv-comment-meta">
                        <strong>${data.username}</strong>
                        <span>${date}</span>
                        <button class="sv-del-comment" onclick="deleteComment(${data.comment_id})"><i class="fas fa-trash"></i></button>
                    </div>
                    <p>${data.content}</p>
                </div>`;
            repliesDiv.appendChild(div);
        }
    });
}

function deleteComment(commentId) {
    if(!confirm('Supprimer ce commentaire ?')) return;
    fetch('index.php?action=deleteComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'comment_id=' + commentId
    }).then(r => r.json()).then(data => {
        if(data.success) {
            const el = document.getElementById('comment-' + commentId);
            if(el) { el.remove(); updateCounters(-1); }
        }
    });
}

function updateCounters(delta) {
    const main = document.getElementById('mainCommentCount');
    const side = document.getElementById('sidebarCommentCount');
    if(main) main.textContent = Math.max(0, parseInt(main.textContent || 0) + delta);
    if(side) side.textContent = Math.max(0, parseInt(side.textContent || 0) + delta);
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>