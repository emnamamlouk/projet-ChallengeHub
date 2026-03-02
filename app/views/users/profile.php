<?php
$title = "Mon profil - ChallengeHub";
$active_page = "profile";

require_once __DIR__ . '/../../models/Challenge.php';
require_once __DIR__ . '/../../models/Submission.php';
require_once __DIR__ . '/../../models/Vote.php';

$challengeModel  = new Challenge();
$submissionModel = new Submission();
$voteModel       = new Vote();

$userId          = $_SESSION['user_id'];
$userChallenges  = $challengeModel->getChallengesByUser($userId);
$userSubmissions = $submissionModel->getSubmissionsByUser($userId);
$userVotes       = $challengeModel->getLikedChallengesByUser($userId);

$totalChallenges  = count($userChallenges);
$totalSubmissions = count($userSubmissions);
$totalVotes       = count($userVotes);
$votesReceived    = 0;
foreach($userSubmissions as $sub) {
    $votesReceived += $submissionModel->getVoteCount($sub['id']);
}

ob_start();
?>

<div class="profile-modern">

    <!-- ===== BANNIERE PROFIL ===== -->
    <div class="profile-banner">
        <div class="profile-header-content">

            <!-- Avatar -->
            <div class="profile-avatar-wrap">
                <?php if(!empty($userInfo['avatar'])): ?>
                    <img src="public/<?= htmlspecialchars($userInfo['avatar']) ?>"
                         alt="avatar" class="profile-avatar-img">
                <?php else: ?>
                    <div class="profile-avatar-default">
                        <?= strtoupper(substr($userInfo['username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <button class="avatar-camera-btn" onclick="openAvatarModal()" title="Changer la photo">
                    <i class="fas fa-camera"></i>
                </button>
            </div>

            <!-- Infos -->
            <div class="profile-info">
                <h1><?= htmlspecialchars($userInfo['username']) ?></h1>
                <p class="profile-email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($userInfo['email']) ?></p>
                <?php if(!empty($userInfo['bio'])): ?>
                    <p class="profile-bio"><?= htmlspecialchars($userInfo['bio']) ?></p>
                <?php else: ?>
                    <p class="profile-bio empty-bio">Aucune bio — cliquez sur Modifier pour en ajouter une</p>
                <?php endif; ?>
                <p class="profile-since">
                    <i class="fas fa-calendar-alt"></i>
                    Membre depuis <?= date('d/m/Y', strtotime($userInfo['created_at'])) ?>
                </p>
            </div>

            <!-- Boutons actions -->
            <div style="display:flex; gap:10px; margin-left:auto; flex-wrap:wrap;">
                <button class="btn-edit-profile" onclick="openEditModal()">
                    <i class="fas fa-edit"></i> Modifier le profil
                </button>
                <button class="btn-delete-account" onclick="openDeleteAccountModal()">
                    <i class="fas fa-trash-alt"></i> Supprimer le compte
                </button>
            </div>
        </div>
    </div>

    <!-- ===== STATS ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-tasks"></i></div>
            <div><span class="stat-val"><?= $totalChallenges ?></span><span class="stat-lbl">Défis créés</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pink"><i class="fas fa-paper-plane"></i></div>
            <div><span class="stat-val"><?= $totalSubmissions ?></span><span class="stat-lbl">Participations</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-thumbs-up"></i></div>
            <div><span class="stat-val"><?= $votesReceived ?></span><span class="stat-lbl">Votes reçus</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-thumbs-up"></i></div>
            <div><span class="stat-val"><?= $totalVotes ?></span><span class="stat-lbl">Votes donnés</span></div>
        </div>
    </div>

    <!-- ===== ONGLETS ===== -->
    <div class="profile-tabs">
        <button class="tab-btn active" onclick="switchTab('challenges', this)">
            <i class="fas fa-tasks"></i> Mes défis (<?= $totalChallenges ?>)
        </button>
        <button class="tab-btn" onclick="switchTab('submissions', this)">
            <i class="fas fa-paper-plane"></i> Mes participations (<?= $totalSubmissions ?>)
        </button>
        <button class="tab-btn" onclick="switchTab('votes', this)">
            <i class="fas fa-thumbs-up"></i> Mes votes (<?= $totalVotes ?>)
        </button>
    </div>

    <!-- TAB : Défis -->
    <div id="tab-challenges" class="tab-pane active">
        <?php if(empty($userChallenges)): ?>
            <div class="empty-state">
                <i class="fas fa-tasks fa-3x"></i>
                <h3>Aucun défi créé</h3>
                <p>Créez votre premier défi !</p>
                <a href="index.php?action=createChallengeForm" class="btn-primary-sm">
                    <i class="fas fa-plus"></i> Créer un défi
                </a>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach($userChallenges as $ch): ?>
                    <div class="mini-card">
                        <div class="mini-card-img">
                            <?php if(!empty($ch['image'])): ?>
                                <img src="public/<?= htmlspecialchars($ch['image']) ?>" alt="">
                            <?php else: ?>
                                <div class="mini-no-img"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <span class="mini-badge"><?= htmlspecialchars($ch['category']) ?></span>
                        </div>
                        <div class="mini-card-body">
                            <h4><a href="index.php?action=showChallenge&id=<?= $ch['id'] ?>"><?= htmlspecialchars($ch['title']) ?></a></h4>
                            <p><?= htmlspecialchars(substr($ch['description'], 0, 80)) ?>...</p>
                            <div class="mini-card-date"><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($ch['created_at'])) ?></div>
                            <div class="mini-card-actions">
                                <a href="index.php?action=showChallenge&id=<?= $ch['id'] ?>" class="btn-sm blue">Voir</a>
                                <a href="index.php?action=editChallengeForm&id=<?= $ch['id'] ?>" class="btn-sm gray"><i class="fas fa-edit"></i></a>
                                <button onclick="deleteChallengeConfirm(<?= $ch['id'] ?>)" class="btn-sm red"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB : Participations -->
    <div id="tab-submissions" class="tab-pane">
        <?php if(empty($userSubmissions)): ?>
            <div class="empty-state">
                <i class="fas fa-paper-plane fa-3x"></i>
                <h3>Aucune participation</h3>
                <p>Participez à des défis !</p>
                <a href="index.php?action=home" class="btn-primary-sm"><i class="fas fa-search"></i> Explorer</a>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach($userSubmissions as $sub): ?>
                    <div class="mini-card">
                        <div class="mini-card-img">
                            <?php if(!empty($sub['image'])): ?>
                                <img src="public/<?= htmlspecialchars($sub['image']) ?>" alt="">
                            <?php else: ?>
                                <div class="mini-no-img"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <span class="mini-votes"><i class="fas fa-thumbs-up"></i> <?= $submissionModel->getVoteCount($sub['id']) ?></span>
                        </div>
                        <div class="mini-card-body">
                            <h4><a href="index.php?action=showChallenge&id=<?= $sub['challenge_id'] ?>"><?= htmlspecialchars($sub['challenge_title'] ?? 'Défi') ?></a></h4>
                            <p><?= htmlspecialchars(substr($sub['description'], 0, 80)) ?>...</p>
                            <div class="mini-card-date"><i class="fas fa-clock"></i> <?= date('d/m/Y', strtotime($sub['created_at'])) ?></div>
                            <div class="mini-card-actions">
                                <a href="index.php?action=showSubmission&id=<?= $sub['id'] ?>" class="btn-sm blue"><i class="fas fa-eye"></i> Ma participation</a>
                                <a href="index.php?action=showChallenge&id=<?= $sub['challenge_id'] ?>" class="btn-sm purple"><i class="fas fa-trophy"></i> Voir le défi</a>
                                <a href="index.php?action=editSubmissionForm&id=<?= $sub['id'] ?>" class="btn-sm gray"><i class="fas fa-edit"></i></a>
                                <button onclick="deleteSubmissionConfirm(<?= $sub['id'] ?>)" class="btn-sm red"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB : Votes -->
    <div id="tab-votes" class="tab-pane">

        <!-- Votes sur défis -->
        <h4 style="color:#667eea;margin:0 0 12px;font-size:1rem;"><i class="fas fa-heart"></i> Défis likés (<?= count($userVotes) ?>)</h4>
        <?php if(empty($userVotes)): ?>
            <div class="empty-state" style="padding:20px;">
                <p style="color:#aaa;">Aucun défi liké pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="votes-list" style="margin-bottom:24px;">
                <?php foreach($userVotes as $vote): ?>
                    <div class="vote-row">
                        <div class="vote-icon-wrap"><i class="fas fa-heart" style="color:#ef4444;"></i></div>
                        <div class="vote-text">
                            Défi <strong><a href="index.php?action=showChallenge&id=<?= $vote['id'] ?>" style="color:#667eea;text-decoration:none;"><?= htmlspecialchars($vote['title']) ?></a></strong>
                            de <em><?= htmlspecialchars($vote['creator_name']) ?></em>
                        </div>
                        <div class="vote-date"><?= date('d/m/Y', strtotime($vote['liked_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Votes sur participations -->
        <h4 style="color:#667eea;margin:0 0 12px;font-size:1rem;"><i class="fas fa-thumbs-up"></i> Participations votées (<?= $totalVotes ?>)</h4>
        <?php
        $submissionVotes = $voteModel->getUserVotes($userId);
        ?>
        <?php if(empty($submissionVotes)): ?>
            <div class="empty-state" style="padding:20px;">
                <p style="color:#aaa;">Aucun vote sur une participation.</p>
            </div>
        <?php else: ?>
            <div class="votes-list">
                <?php foreach($submissionVotes as $vote): ?>
                    <div class="vote-row">
                        <div class="vote-icon-wrap"><i class="fas fa-thumbs-up"></i></div>
                        <div class="vote-text">
                            Participation de <strong><?= htmlspecialchars($vote['username'] ?? '?') ?></strong>
                            au défi <em><a href="index.php?action=showChallenge&id=<?= $vote['challenge_id'] ?? '' ?>" style="color:#667eea;text-decoration:none;"><?= htmlspecialchars($vote['challenge_title'] ?? '?') ?></a></em>
                        </div>
                        <div class="vote-date"><?= date('d/m/Y', strtotime($vote['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ===== MODAL : MODIFIER PROFIL ===== -->
<div id="editModal" class="modal-overlay" onclick="if(event.target===this)closeEditModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Modifier mon profil</h3>
            <button onclick="closeEditModal()" class="modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form action="index.php?action=updateProfile" method="POST" enctype="multipart/form-data" class="modal-form">
            <?= CSRF::field() ?>

            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom d'utilisateur</label>
                <input type="text" name="username"
                       value="<?= htmlspecialchars($userInfo['username']) ?>" required minlength="3">
            </div>

            <div class="form-group">
                <label><i class="fas fa-quote-left"></i> Bio</label>
                <textarea name="bio" rows="3"
                          placeholder="Parlez-nous de vous..."><?= htmlspecialchars($userInfo['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-camera"></i> Photo de profil</label>
                <div class="avatar-preview-row">
                    <?php if(!empty($userInfo['avatar'])): ?>
                        <img src="public/<?= htmlspecialchars($userInfo['avatar']) ?>"
                             class="current-avatar-preview" id="avatarPreview" alt="">
                    <?php else: ?>
                        <div class="current-avatar-placeholder" id="avatarPreview">
                            <?= strtoupper(substr($userInfo['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="upload-zone" onclick="document.getElementById('avatarInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Choisir une image</span>
                        <small>JPEG, PNG, GIF — Max 2Mo</small>
                        <input type="file" id="avatarInput" name="avatar" accept="image/*"
                               onchange="previewAvatar(this)" style="display:none">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeEditModal()" class="btn-cancel-modal">Annuler</button>
                <button type="submit" class="btn-save-modal">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL : SUPPRIMER DEFI ===== -->
<div id="deleteChallengeModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box modal-small">
        <div class="modal-header danger">
            <h3><i class="fas fa-exclamation-triangle"></i> Supprimer le défi</h3>
        </div>
        <p style="padding:20px;color:#555;">Êtes-vous sûr ? Toutes les participations seront supprimées.</p>
        <form action="index.php?action=deleteChallenge" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="challenge_id" id="deleteChallengeId">
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('deleteChallengeModal').style.display='none'" class="btn-cancel-modal">Annuler</button>
                <button type="submit" class="btn-danger-modal"><i class="fas fa-trash"></i> Supprimer</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL : SUPPRIMER PARTICIPATION ===== -->
<div id="deleteSubmissionModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box modal-small">
        <div class="modal-header danger">
            <h3><i class="fas fa-exclamation-triangle"></i> Supprimer la participation</h3>
        </div>
        <p style="padding:20px;color:#555;">Êtes-vous sûr de vouloir supprimer cette participation ?</p>
        <form action="index.php?action=deleteSubmission" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="submission_id" id="deleteSubmissionId">
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('deleteSubmissionModal').style.display='none'" class="btn-cancel-modal">Annuler</button>
                <button type="submit" class="btn-danger-modal"><i class="fas fa-trash"></i> Supprimer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal suppression compte -->
<div id="deleteAccountModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div style="text-align:center; margin-bottom:16px;">
            <div style="width:60px;height:60px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.6rem;color:#dc2626;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 style="color:#dc2626; margin:0 0 6px;">Supprimer mon compte</h3>
            <p style="color:#666; font-size:0.9rem; margin:0;">Cette action est <strong>irréversible</strong>. Tous vos défis, participations et commentaires seront supprimés.</p>
        </div>
        <form action="index.php?action=deleteAccount" method="POST">
            <?= CSRF::field() ?>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-weight:600; color:#333; margin-bottom:6px;">
                    <i class="fas fa-lock"></i> Confirmez avec votre mot de passe
                </label>
                <input type="password" name="password" placeholder="••••••••" required
                    style="width:100%;padding:10px 14px;border:2px solid #e0e0e0;border-radius:10px;font-size:0.95rem;box-sizing:border-box;">
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('deleteAccountModal').style.display='none'" class="btn-cancel-modal">Annuler</button>
                <button type="submit" style="padding:10px 20px;background:#dc2626;color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer;">
                    <i class="fas fa-trash-alt"></i> Supprimer définitivement
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.profile-modern { max-width: 1100px; margin: 0 auto; }

/* BANNIERE */
.profile-banner {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 20px; padding: 40px; margin-bottom: 28px;
    box-shadow: 0 10px 40px rgba(102,126,234,0.35);
}
.profile-header-content { display:flex; align-items:center; gap:28px; color:white; flex-wrap:wrap; }

.profile-avatar-wrap { position:relative; width:110px; height:110px; flex-shrink:0; }
.profile-avatar-img { width:110px; height:110px; border-radius:50%; object-fit:cover; border:4px solid white; box-shadow:0 4px 15px rgba(0,0,0,0.25); }
.profile-avatar-default { width:110px; height:110px; border-radius:50%; background:white; color:#667eea; display:flex; align-items:center; justify-content:center; font-size:2.8rem; font-weight:800; border:4px solid rgba(255,255,255,0.5); }
.avatar-camera-btn { position:absolute; bottom:4px; right:4px; width:32px; height:32px; border-radius:50%; background:white; border:none; color:#667eea; cursor:pointer; font-size:0.85rem; box-shadow:0 2px 8px rgba(0,0,0,0.2); transition:all 0.2s; display:flex; align-items:center; justify-content:center; }
.avatar-camera-btn:hover { transform:scale(1.1); }

.profile-info { flex:1; min-width:200px; }
.profile-info h1 { font-size:2rem; font-weight:800; margin-bottom:6px; }
.profile-email { opacity:0.85; font-size:0.9rem; margin-bottom:6px; }
.profile-bio { opacity:0.9; font-size:0.95rem; margin-bottom:6px; line-height:1.5; }
.empty-bio { opacity:0.5; font-style:italic; }
.profile-since { font-size:0.82rem; opacity:0.7; }

.btn-edit-profile { margin-left:auto; padding:12px 22px; background:rgba(255,255,255,0.18); border:2px solid rgba(255,255,255,0.5); color:white; border-radius:10px; cursor:pointer; font-weight:700; font-size:0.95rem; transition:all 0.3s; white-space:nowrap; }
.btn-edit-profile:hover { background:white; color:#667eea; }
.btn-delete-account { padding:12px 22px; background:rgba(220,38,38,0.15); border:2px solid rgba(255,100,100,0.5); color:white; border-radius:10px; cursor:pointer; font-weight:700; font-size:0.95rem; transition:all 0.3s; white-space:nowrap; }
.btn-delete-account:hover { background:#dc2626; border-color:#dc2626; color:white; }

/* STATS */
.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
.stat-card { background:white; border-radius:14px; padding:18px 20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 16px rgba(0,0,0,0.06); transition:all 0.3s; }
.stat-card:hover { transform:translateY(-4px); box-shadow:0 10px 25px rgba(0,0,0,0.1); }
.stat-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; color:white; flex-shrink:0; }
.stat-icon.purple { background:linear-gradient(135deg,#667eea,#764ba2); }
.stat-icon.pink { background:linear-gradient(135deg,#f093fb,#f5576c); }
.stat-icon.red { background:linear-gradient(135deg,#ff6b6b,#ee0979); }
.stat-icon.green { background:linear-gradient(135deg,#43e97b,#38f9d7); }
.stat-val { display:block; font-size:1.8rem; font-weight:800; color:#222; line-height:1.1; }
.stat-lbl { font-size:0.82rem; color:#888; }

/* ONGLETS */
.profile-tabs { display:flex; gap:8px; margin-bottom:20px; border-bottom:2px solid #e8e8e8; padding-bottom:0; }
.tab-btn { padding:12px 22px; background:none; border:none; border-bottom:3px solid transparent; margin-bottom:-2px; color:#777; font-size:0.95rem; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px; border-radius:8px 8px 0 0; }
.tab-btn:hover { color:#667eea; background:#f5f5ff; }
.tab-btn.active { color:#667eea; border-bottom-color:#667eea; background:#f0eeff; }

.tab-pane { display:none; padding-top:20px; }
.tab-pane.active { display:block; }

/* CARDS */
.cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px; }
.mini-card { background:white; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.06); transition:all 0.3s; }
.mini-card:hover { transform:translateY(-5px); box-shadow:0 10px 25px rgba(0,0,0,0.1); }
.mini-card-img { height:160px; position:relative; overflow:hidden; }
.mini-card-img img { width:100%; height:100%; object-fit:cover; }
.mini-no-img { width:100%; height:100%; background:linear-gradient(135deg,#667eea,#764ba2); display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.25); font-size:2.5rem; }
.mini-badge { position:absolute; top:10px; right:10px; background:rgba(255,255,255,0.92); color:#667eea; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700; }
.mini-votes { position:absolute; bottom:10px; right:10px; background:rgba(0,0,0,0.6); color:white; padding:4px 10px; border-radius:20px; font-size:0.8rem; }
.mini-votes i { color:#ff6b6b; }
.mini-card-body { padding:16px; }
.mini-card-body h4 { margin-bottom:6px; font-size:0.97rem; }
.mini-card-body h4 a { color:#222; text-decoration:none; font-weight:700; }
.mini-card-body h4 a:hover { color:#667eea; }
.mini-card-body p { font-size:0.83rem; color:#666; line-height:1.5; margin-bottom:10px; }
.mini-card-date { font-size:0.78rem; color:#aaa; margin-bottom:10px; }
.mini-card-actions { display:flex; gap:6px; }
.btn-sm { padding:6px 14px; border-radius:7px; font-size:0.82rem; font-weight:600; border:none; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:5px; transition:all 0.2s; }
.btn-sm.blue { background:#667eea; color:white; flex:1; justify-content:center; }
.btn-sm.blue:hover { background:#5a6fd8; }
.btn-sm.gray { background:#f0f0f0; color:#555; }
.btn-sm.gray:hover { background:#e0e0e0; }
.btn-sm.red { background:#fee2e2; color:#ef4444; }
.btn-sm.red:hover { background:#fecaca; }

/* VOTES */
.votes-list { background:white; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.06); }
.vote-row { display:flex; align-items:center; gap:16px; padding:16px 20px; border-bottom:1px solid #f0f0f0; }
.vote-row:last-child { border-bottom:none; }
.vote-icon-wrap { width:38px; height:38px; background:#fee2e2; color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.vote-text { flex:1; font-size:0.9rem; color:#444; }
.vote-date { font-size:0.78rem; color:#aaa; white-space:nowrap; }

/* EMPTY STATE */
.empty-state { text-align:center; padding:50px 20px; background:#f9f9f9; border-radius:14px; border:2px dashed #e0e0e0; }
.empty-state i { color:#ccc; margin-bottom:16px; display:block; }
.empty-state h3 { color:#333; margin-bottom:8px; }
.empty-state p { color:#777; margin-bottom:18px; }
.btn-primary-sm { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border-radius:10px; text-decoration:none; font-weight:700; font-size:0.9rem; transition:all 0.3s; }
.btn-primary-sm:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(102,126,234,0.4); }

/* MODALES */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:white; border-radius:18px; width:100%; max-width:500px; box-shadow:0 20px 60px rgba(0,0,0,0.2); overflow:hidden; }
.modal-small { max-width:400px; }
.modal-header { display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-bottom:1px solid #f0f0f0; }
.modal-header h3 { font-size:1.1rem; color:#222; display:flex; align-items:center; gap:8px; }
.modal-header.danger { background:#fff5f5; }
.modal-header.danger h3 { color:#ef4444; }
.modal-close { background:none; border:none; font-size:1.1rem; color:#aaa; cursor:pointer; padding:4px; }
.modal-close:hover { color:#333; }
.modal-form { padding:24px; }
.form-group { margin-bottom:18px; }
.form-group label { display:flex; align-items:center; gap:8px; font-weight:600; color:#333; margin-bottom:8px; font-size:0.9rem; }
.form-group label i { color:#667eea; }
.form-group input, .form-group textarea { width:100%; padding:11px 14px; border:2px solid #e8e8e8; border-radius:10px; font-size:0.93rem; font-family:inherit; transition:border-color 0.3s; resize:vertical; }
.form-group input:focus, .form-group textarea:focus { outline:none; border-color:#667eea; }

/* Upload avatar */
.avatar-preview-row { display:flex; align-items:center; gap:16px; }
.current-avatar-preview { width:70px; height:70px; border-radius:50%; object-fit:cover; border:3px solid #e8e8e8; }
.current-avatar-placeholder { width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; display:flex; align-items:center; justify-content:center; font-size:1.8rem; font-weight:800; }
.upload-zone { flex:1; border:2px dashed #d0d0d0; border-radius:10px; padding:14px; text-align:center; cursor:pointer; transition:all 0.3s; }
.upload-zone:hover { border-color:#667eea; background:#f8f7ff; }
.upload-zone i { font-size:1.4rem; color:#bbb; display:block; margin-bottom:4px; }
.upload-zone span { display:block; color:#667eea; font-weight:600; font-size:0.88rem; }
.upload-zone small { color:#aaa; font-size:0.78rem; }

.modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:16px 24px; border-top:1px solid #f0f0f0; background:#fafafa; }
.btn-cancel-modal { padding:10px 20px; background:#f0f0f0; border:none; border-radius:8px; color:#555; font-weight:600; cursor:pointer; transition:all 0.2s; }
.btn-cancel-modal:hover { background:#e0e0e0; }
.btn-save-modal { padding:10px 24px; background:linear-gradient(135deg,#667eea,#764ba2); color:white; border:none; border-radius:8px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px; transition:all 0.3s; }
.btn-save-modal:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(102,126,234,0.4); }
.btn-danger-modal { padding:10px 20px; background:#ef4444; color:white; border:none; border-radius:8px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px; }
.btn-danger-modal:hover { background:#dc2626; }

@media(max-width:768px){
    .stats-grid { grid-template-columns:repeat(2,1fr); }
    .profile-header-content { flex-direction:column; text-align:center; }
    .btn-edit-profile { margin:0 auto; }
    .cards-grid { grid-template-columns:1fr; }
}
</style>

<script>
// Onglets
function switchTab(name, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

// Modal modifier profil
function openDeleteAccountModal() {
    document.getElementById('deleteAccountModal').style.display = 'flex';
}
function openEditModal() {
    document.getElementById('editModal').classList.add('open');
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}

// Bouton caméra ouvre aussi le modal
function openAvatarModal() {
    openEditModal();
    // Scroll jusqu'au champ avatar
    setTimeout(() => {
        document.getElementById('avatarInput').click();
    }, 300);
}

// Aperçu avatar avant upload
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('avatarPreview');
            preview.outerHTML = '<img src="' + e.target.result + '" class="current-avatar-preview" id="avatarPreview" alt="">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Supprimer défi
function deleteChallengeConfirm(id) {
    document.getElementById('deleteChallengeId').value = id;
    document.getElementById('deleteChallengeModal').style.display = 'flex';
}

// Supprimer participation
function deleteSubmissionConfirm(id) {
    document.getElementById('deleteSubmissionId').value = id;
    document.getElementById('deleteSubmissionModal').style.display = 'flex';
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>