<?php
$title       = htmlspecialchars($user['username']) . " — ChallengeHub";
$active_page = "";
$userSubmissions     = $userSubmissions     ?? [];
$userChallenges      = $userChallenges      ?? [];
$totalVotesReceived  = $totalVotesReceived  ?? 0;
ob_start();
$currentUserId = $_SESSION['user_id'] ?? null;
?>

<!-- ══════════════════════════════════════════════════════
     PUBLIC PROFILE
══════════════════════════════════════════════════════ -->
<div class="xp-root">

    <!-- BANNER -->
    <div class="xp-banner">
        <div class="xp-banner-gfx"></div>
        <div class="xp-banner-inner">
            <div class="xp-av-ring">
                <?php if(!empty($user['avatar'])): ?>
                    <img src="public/<?= htmlspecialchars($user['avatar']) ?>" class="xp-av-img" alt="">
                <?php else: ?>
                    <div class="xp-av-letter"><?= strtoupper(substr($user['username'],0,1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="xp-banner-info">
                <h1 class="xp-username"><?= htmlspecialchars($user['username']) ?></h1>
                <p class="xp-since">Membre depuis <?= date('M Y', strtotime($user['created_at'])) ?></p>
                <?php if(!empty($user['bio'])): ?>
                    <p class="xp-bio"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                <?php endif; ?>
            </div>
            <div class="xp-stats">
                <div class="xp-stat">
                    <span class="xp-stat-n"><?= count($userChallenges) ?></span>
                    <span class="xp-stat-l">Défis créés</span>
                </div>
                <div class="xp-stat-sep"></div>
                <div class="xp-stat">
                    <span class="xp-stat-n"><?= count($userSubmissions) ?></span>
                    <span class="xp-stat-l">Participations</span>
                </div>
                <div class="xp-stat-sep"></div>
                <div class="xp-stat">
                    <span class="xp-stat-n"><?= $totalVotesReceived ?></span>
                    <span class="xp-stat-l">Votes reçus</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div class="xp-tabs" role="tablist">
        <button class="xp-tab xp-tab--active" onclick="xpTab('defis',this)" role="tab">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Défis créés
            <span class="xp-tab-pill"><?= count($userChallenges) ?></span>
        </button>
        <button class="xp-tab" onclick="xpTab('participations',this)" role="tab">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Participations
            <span class="xp-tab-pill"><?= count($userSubmissions) ?></span>
        </button>
    </div>

    <!-- ───────── TAB : DÉFIS ───────── -->
    <div id="xp-panel-defis" class="xp-panel xp-panel--active">
        <?php if(empty($userChallenges)): ?>
            <div class="xp-empty">
                <span class="xp-empty-ico">🏆</span>
                <p><?= htmlspecialchars($user['username']) ?> n'a pas encore créé de défi.</p>
            </div>
        <?php else: ?>
            <div class="xp-ch-list">
                <?php foreach($userChallenges as $ch): ?>
                <a href="index.php?action=showChallenge&id=<?= $ch['id'] ?>" class="xp-ch-card">
                    <?php if(!empty($ch['image'])): ?>
                        <div class="xp-ch-thumb"><img src="public/<?= htmlspecialchars($ch['image']) ?>" alt=""></div>
                    <?php else: ?>
                        <div class="xp-ch-thumb xp-ch-thumb--empty">🏆</div>
                    <?php endif; ?>
                    <div class="xp-ch-body">
                        <div class="xp-ch-toprow">
                            <span class="xp-badge xp-badge--cat"><?= htmlspecialchars($ch['category']) ?></span>
                            <span class="xp-ch-date"><?= date('d/m/Y', strtotime($ch['created_at'])) ?></span>
                        </div>
                        <h3 class="xp-ch-title"><?= htmlspecialchars($ch['title']) ?></h3>
                        <p class="xp-ch-desc"><?= htmlspecialchars(mb_substr($ch['description'],0,120)) ?>…</p>
                        <!-- Créateur clairement visible -->
                        <div class="xp-ch-creator">
                            <div class="xp-ch-creator-av">
                                <?php if(!empty($user['avatar'])): ?>
                                    <img src="public/<?= htmlspecialchars($user['avatar']) ?>" alt="">
                                <?php else: ?>
                                    <?= strtoupper(substr($user['username'],0,1)) ?>
                                <?php endif; ?>
                            </div>
                            <span>Publié par <strong><?= htmlspecialchars($user['username']) ?></strong></span>
                            <span class="xp-ch-arrow">→</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ───────── TAB : PARTICIPATIONS ───────── -->
    <div id="xp-panel-participations" class="xp-panel">
        <?php if(empty($userSubmissions)): ?>
            <div class="xp-empty">
                <span class="xp-empty-ico">✈️</span>
                <p><?= htmlspecialchars($user['username']) ?> n'a pas encore participé.</p>
            </div>
        <?php else: ?>
            <div class="xp-sub-feed">
                <?php foreach($userSubmissions as $i => $sub):
                    $sid     = $sub['id'];
                    $voted   = $sub['user_voted'];
                    $vcount  = (int)$sub['votes_count'];
                    $ccount  = (int)$sub['comments_count'];
                    $voters  = $sub['voters'];
                    $comms   = $sub['comments'];
                    $isOwn   = ($currentUserId == $sub['user_id']);
                    $canVote = ($currentUserId && !$isOwn);
                ?>
                <div class="xp-sub-card" id="sub-<?= $sid ?>">

                    <!-- ── Header sub ── -->
                    <div class="xp-sub-hd">
                        <div class="xp-sub-hd-left">
                            <!-- Avatar auteur -->
                            <?php if(!empty($user['avatar'])): ?>
                                <img src="public/<?= htmlspecialchars($user['avatar']) ?>" class="xp-sub-av" alt="">
                            <?php else: ?>
                                <div class="xp-sub-av xp-sub-av--letter"><?= strtoupper(substr($user['username'],0,1)) ?></div>
                            <?php endif; ?>
                            <div class="xp-sub-hd-text">
                                <span class="xp-sub-author"><?= htmlspecialchars($user['username']) ?></span>
                                <span class="xp-sub-ts"><?= date('d/m/Y à H:i', strtotime($sub['created_at'])) ?></span>
                            </div>
                        </div>
                        <!-- Tag défi parachuté — très visible -->
                        <a href="index.php?action=showChallenge&id=<?= $sub['challenge_id'] ?>" class="xp-defi-tag" title="Voir le défi">
                            <span class="xp-defi-tag-icon">🏆</span>
                            <span class="xp-defi-tag-text"><?= htmlspecialchars($sub['challenge_title']) ?></span>
                        </a>
                    </div>

                    <!-- ── Image ── -->
                    <?php if(!empty($sub['image'])): ?>
                    <div class="xp-sub-img-wrap">
                        <img src="public/<?= htmlspecialchars($sub['image']) ?>" class="xp-sub-img" alt="">
                    </div>
                    <?php endif; ?>

                    <!-- ── Corps ── -->
                    <div class="xp-sub-body">
                        <p class="xp-sub-desc"><?= nl2br(htmlspecialchars(mb_substr($sub['description'],0,250))) ?><?= mb_strlen($sub['description'])>250?'…':'' ?></p>
                        <?php if(!empty($sub['link'])): ?>
                        <a href="<?= htmlspecialchars($sub['link']) ?>" target="_blank" rel="noopener" class="xp-sub-extlink">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            Voir le projet
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- ── Barre d'actions ── -->
                    <div class="xp-actions">
                        <!-- VOTE — une seule fois -->
                        <?php if($canVote): ?>
                            <button class="xp-vote-btn<?= $voted?' xp-vote-btn--on':'' ?>"
                                    id="vbtn-<?= $sid ?>"
                                    data-sid="<?= $sid ?>"
                                    onclick="xpVote(this)"
                                    <?= $voted?'disabled title="Vous avez déjà voté"':'' ?>>
                                <svg width="15" height="15" viewBox="0 0 24 24"
                                     fill="<?= $voted?'currentColor':'none' ?>"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3H14z"/>
                                    <path d="M7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/>
                                </svg>
                                <span id="vcnt-<?= $sid ?>"><?= $vcount ?></span>
                                <span class="xp-vote-lbl"><?= $voted?'Voté ✓':'Voter' ?></span>
                            </button>
                        <?php elseif($isOwn): ?>
                            <div class="xp-vote-display">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="#6366f1" stroke="none"><path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3H14z"/></svg>
                                <span id="vcnt-<?= $sid ?>"><?= $vcount ?></span> vote<?= $vcount!=1?'s':'' ?>
                            </div>
                        <?php else: ?>
                            <div class="xp-vote-display xp-vote-display--muted">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3H14z"/><path d="M7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/></svg>
                                <span id="vcnt-<?= $sid ?>"><?= $vcount ?></span> vote<?= $vcount!=1?'s':'' ?>
                                <a href="index.php?action=showLogin" class="xp-login-hint">Connectez-vous pour voter</a>
                            </div>
                        <?php endif; ?>

                        <!-- QUI A VOTÉ -->
                        <button class="xp-action-btn" onclick="xpToggle('voters-<?= $sid ?>')">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                            <span id="vcnt2-<?= $sid ?>"><?= count($voters) ?></span> votant<?= count($voters)!=1?'s':'' ?>
                        </button>

                        <!-- COMMENTAIRES -->
                        <button class="xp-action-btn" onclick="xpToggle('comments-<?= $sid ?>')">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                            <span id="ccnt-<?= $sid ?>"><?= $ccount ?></span> commentaire<?= $ccount!=1?'s':'' ?>
                        </button>

                        <!-- VOIR COMPLET -->
                        <a href="index.php?action=showSubmission&id=<?= $sid ?>" class="xp-voir-link">
                            Voir tout →
                        </a>
                    </div>

                    <!-- ── Panel : QUI A VOTÉ ── -->
                    <div class="xp-panel-sub" id="voters-<?= $sid ?>" style="display:none">
                        <div class="xp-panel-sub-title">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3H14z"/></svg>
                            Personnes qui ont voté
                        </div>
                        <div class="xp-voters-wrap" id="voters-list-<?= $sid ?>">
                            <?php if(empty($voters)): ?>
                                <p class="xp-panel-empty">Aucun vote pour l'instant</p>
                            <?php else: ?>
                                <?php foreach($voters as $v): ?>
                                <a href="index.php?action=viewProfile&id=<?= $v['id'] ?>" class="xp-voter-chip" data-uid="<?= $v['id'] ?>">
                                    <?php if(!empty($v['avatar'])): ?>
                                        <img src="public/<?= htmlspecialchars($v['avatar']) ?>" class="xp-voter-thumb" alt="">
                                    <?php else: ?>
                                        <div class="xp-voter-thumb xp-voter-thumb--letter"><?= strtoupper(substr($v['username'],0,1)) ?></div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($v['username']) ?></span>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ── Panel : COMMENTAIRES ── -->
                    <div class="xp-panel-sub xp-panel-sub--comments" id="comments-<?= $sid ?>" style="display:none">
                        <div class="xp-panel-sub-title">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                            Commentaires
                        </div>
                        <!-- Formulaire (si connecté) -->
                        <?php if($currentUserId): ?>
                        <div class="xp-cf">
                            <div class="xp-cf-av"><?= strtoupper(substr($_SESSION['username'],0,1)) ?></div>
                            <div class="xp-cf-right">
                                <textarea id="ctxt-<?= $sid ?>" class="xp-cf-txt" placeholder="Écrire un commentaire…" rows="2"
                                          onkeydown="if(event.ctrlKey&&event.key==='Enter')xpComment(<?= $sid ?>)"></textarea>
                                <div class="xp-cf-footer">
                                    <span class="xp-cf-hint">Ctrl+Entrée pour envoyer</span>
                                    <button onclick="xpComment(<?= $sid ?>)" class="xp-cf-send" id="csend-<?= $sid ?>">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                        Envoyer
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <p class="xp-login-cta"><a href="index.php?action=showLogin">Connectez-vous</a> pour commenter.</p>
                        <?php endif; ?>

                        <!-- Liste commentaires -->
                        <div class="xp-comm-list" id="clist-<?= $sid ?>">
                            <?php if(empty($comms)): ?>
                                <p class="xp-panel-empty">Aucun commentaire</p>
                            <?php else: ?>
                                <?php foreach($comms as $cm): ?>
                                <div class="xp-comm-item">
                                    <div class="xp-comm-av">
                                        <?php if(!empty($cm['avatar'])): ?>
                                            <img src="public/<?= htmlspecialchars($cm['avatar']) ?>" alt="">
                                        <?php else: ?>
                                            <?= strtoupper(substr($cm['username'],0,1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="xp-comm-bubble">
                                        <div class="xp-comm-meta">
                                            <strong><?= htmlspecialchars($cm['username']) ?></strong>
                                            <span><?= date('d/m/Y H:i', strtotime($cm['created_at'])) ?></span>
                                        </div>
                                        <p><?= nl2br(htmlspecialchars($cm['content'])) ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div><!-- /.xp-root -->

<!-- ══════════════════════════════════════════
     STYLES
══════════════════════════════════════════ -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
.xp-root * { font-family: 'Inter', system-ui, sans-serif; box-sizing: border-box; }

/* ─── Tokens ─── */
:root {
  --xp-indigo: #6366f1;
  --xp-violet: #8b5cf6;
  --xp-red:    #f43f5e;
  --xp-amber:  #f59e0b;
  --xp-ink:    #111827;
  --xp-sub:    #4b5563;
  --xp-faint:  #9ca3af;
  --xp-line:   #e5e7eb;
  --xp-bg:     #f9fafb;
  --xp-white:  #fff;
  --xp-r:      16px;
  --xp-sh:     0 1px 14px rgba(0,0,0,.06);
  --xp-sh-md:  0 4px 28px rgba(0,0,0,.10);
}

.xp-root { max-width: 820px; margin: 0 auto; }

/* ─── BANNER ─── */
.xp-banner { position: relative; border-radius: 22px; overflow: hidden; margin-bottom: 18px; box-shadow: var(--xp-sh-md); }
.xp-banner-gfx { position: absolute; inset: 0; background: linear-gradient(135deg,#1e1b4b 0%,#312e81 35%,#4c1d95 65%,#6d28d9 100%); }
.xp-banner-gfx::before { content:''; position:absolute; inset:0;
  background:
    radial-gradient(circle at 20% 50%, rgba(99,102,241,.35) 0%, transparent 50%),
    radial-gradient(circle at 80% 30%, rgba(139,92,246,.25) 0%, transparent 45%); }
.xp-banner-gfx::after { content:''; position:absolute; inset:0;
  background:url("data:image/svg+xml,%3Csvg width='120' height='120' viewBox='0 0 120 120' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='60' cy='60' r='50' fill='none' stroke='%23fff' stroke-opacity='0.04' stroke-width='1'/%3E%3C/svg%3E") repeat; }

.xp-banner-inner { position:relative; display:flex; align-items:center; gap:24px; padding:34px 38px; flex-wrap:wrap; }

.xp-av-ring { flex-shrink:0; width:100px; height:100px; border-radius:50%;
  background:linear-gradient(135deg,rgba(255,255,255,.3),rgba(255,255,255,.1));
  padding:3px; box-shadow:0 0 0 3px rgba(255,255,255,.2); }
.xp-av-img  { width:100%; height:100%; border-radius:50%; object-fit:cover; display:block; }
.xp-av-letter { width:100%; height:100%; border-radius:50%; background:rgba(255,255,255,.15);
  display:flex; align-items:center; justify-content:center;
  font-size:2.5rem; font-weight:900; color:#fff; }

.xp-banner-info { flex:1; color:#fff; min-width:180px; }
.xp-username { font-size:1.9rem; font-weight:900; margin:0 0 5px; letter-spacing:-.5px; }
.xp-since    { font-size:.8rem; opacity:.65; margin:0 0 8px; }
.xp-bio      { font-size:.9rem; opacity:.85; margin:0; line-height:1.5; max-width:380px; }

.xp-stats { display:flex; align-items:center; gap:0; background:rgba(255,255,255,.12);
  backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,.2); border-radius:16px;
  overflow:hidden; margin-left:auto; }
.xp-stat { padding:18px 26px; text-align:center; color:#fff; }
.xp-stat-n { display:block; font-size:1.9rem; font-weight:900; line-height:1; }
.xp-stat-l { display:block; font-size:.68rem; opacity:.75; margin-top:4px; text-transform:uppercase; letter-spacing:.5px; }
.xp-stat-sep { width:1px; background:rgba(255,255,255,.18); align-self:stretch; }

/* ─── TABS ─── */
.xp-tabs { display:flex; gap:5px; background:var(--xp-white); border-radius:14px;
  padding:5px; box-shadow:var(--xp-sh); border:1px solid var(--xp-line); margin-bottom:18px; }
.xp-tab { flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
  padding:11px 18px; border:none; background:none; cursor:pointer; font-size:.88rem;
  font-weight:600; color:var(--xp-faint); border-radius:10px; transition:all .2s; }
.xp-tab:hover { background:var(--xp-bg); color:var(--xp-sub); }
.xp-tab--active { background:linear-gradient(135deg,var(--xp-indigo),var(--xp-violet));
  color:#fff !important; box-shadow:0 4px 18px rgba(99,102,241,.38); }
.xp-tab-pill { background:rgba(0,0,0,.12); color:inherit; font-size:.7rem; font-weight:700;
  padding:2px 8px; border-radius:20px; }
.xp-tab--active .xp-tab-pill { background:rgba(255,255,255,.25); }

/* ─── PANELS ─── */
.xp-panel { display:none; }
.xp-panel--active { display:block; }

/* ─── EMPTY ─── */
.xp-empty { text-align:center; padding:56px 20px; color:var(--xp-faint);
  background:var(--xp-white); border-radius:var(--xp-r); border:1px solid var(--xp-line); }
.xp-empty-ico { display:block; font-size:2.8rem; margin-bottom:12px; }

/* ─── BADGE ─── */
.xp-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
.xp-badge--cat { background:#ede9fe; color:var(--xp-indigo); }

/* ══════════════════════════════════
   DÉFIS
══════════════════════════════════ */
.xp-ch-list { display:flex; flex-direction:column; gap:12px; }
.xp-ch-card { display:flex; background:var(--xp-white); border-radius:var(--xp-r);
  border:2px solid var(--xp-line); overflow:hidden; text-decoration:none; color:inherit;
  transition:all .25s; }
.xp-ch-card:hover { border-color:#a5b4fc; transform:translateY(-2px); box-shadow:var(--xp-sh-md); }

.xp-ch-thumb { width:160px; flex-shrink:0; overflow:hidden; }
.xp-ch-thumb img { width:100%; height:100%; object-fit:cover; transition:transform .35s; display:block; }
.xp-ch-card:hover .xp-ch-thumb img { transform:scale(1.06); }
.xp-ch-thumb--empty { background:linear-gradient(135deg,#ede9fe,#e0e7ff);
  display:flex; align-items:center; justify-content:center; font-size:2.5rem; }

.xp-ch-body { flex:1; padding:18px 22px; display:flex; flex-direction:column; gap:6px; }
.xp-ch-toprow { display:flex; align-items:center; justify-content:space-between; }
.xp-ch-date { font-size:.74rem; color:var(--xp-faint); }
.xp-ch-title { font-size:1rem; font-weight:800; color:var(--xp-ink); margin:0; line-height:1.3; overflow-wrap:break-word; }
.xp-ch-desc  { font-size:.83rem; color:var(--xp-sub); line-height:1.55; margin:0; flex:1; overflow-wrap:break-word; }

.xp-ch-creator { display:flex; align-items:center; gap:8px; margin-top:auto; padding-top:10px;
  border-top:1px solid var(--xp-line); font-size:.8rem; color:var(--xp-sub); }
.xp-ch-creator-av { width:26px; height:26px; border-radius:50%; overflow:hidden;
  background:linear-gradient(135deg,var(--xp-indigo),var(--xp-violet));
  color:#fff; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800; flex-shrink:0; }
.xp-ch-creator-av img { width:100%; height:100%; object-fit:cover; }
.xp-ch-arrow { margin-left:auto; color:#a5b4fc; font-size:1rem; transition:transform .2s; }
.xp-ch-card:hover .xp-ch-arrow { transform:translateX(4px); color:var(--xp-indigo); }

/* ══════════════════════════════════
   PARTICIPATIONS FEED
══════════════════════════════════ */
.xp-sub-feed { display:flex; flex-direction:column; gap:18px; }
.xp-sub-card { background:var(--xp-white); border-radius:var(--xp-r);
  border:2px solid var(--xp-line); overflow:hidden;
  transition:border-color .2s, box-shadow .2s; }
.xp-sub-card:hover { border-color:#c4b5fd; box-shadow:var(--xp-sh); }

/* Header */
.xp-sub-hd { display:flex; align-items:center; justify-content:space-between;
  padding:13px 16px; background:var(--xp-bg); border-bottom:1px solid var(--xp-line); flex-wrap:wrap; gap:10px; }
.xp-sub-hd-left { display:flex; align-items:center; gap:10px; }
.xp-sub-av { width:38px; height:38px; border-radius:50%; object-fit:cover; flex-shrink:0; }
.xp-sub-av--letter { background:linear-gradient(135deg,var(--xp-indigo),var(--xp-violet));
  color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.9rem; }
.xp-sub-author { display:block; font-size:.88rem; font-weight:700; color:var(--xp-ink); }
.xp-sub-ts     { display:block; font-size:.72rem; color:var(--xp-faint); }

/* Tag défi — bien parachuté et visible */
.xp-defi-tag { display:inline-flex; align-items:center; gap:7px; max-width:260px;
  padding:7px 14px; border-radius:30px; text-decoration:none; transition:all .2s;
  background:linear-gradient(135deg,#fef3c7,#fde68a); border:1.5px solid #fbbf24;
  font-size:.79rem; font-weight:700; color:#78350f;
  overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.xp-defi-tag:hover { background:linear-gradient(135deg,#fde68a,#f59e0b); transform:translateY(-1px); box-shadow:0 4px 12px rgba(245,158,11,.3); }
.xp-defi-tag-icon { flex-shrink:0; }
.xp-defi-tag-text { overflow:hidden; text-overflow:ellipsis; }

/* Image */
.xp-sub-img-wrap { border-top:1px solid var(--xp-line); }
.xp-sub-img { width:100%; max-height:400px; object-fit:cover; display:block; }

/* Corps */
.xp-sub-body { padding:15px 17px; }
.xp-sub-desc { font-size:.92rem; color:var(--xp-sub); line-height:1.7; margin:0 0 10px; overflow-wrap:break-word; word-break:break-word; }
.xp-sub-extlink { display:inline-flex; align-items:center; gap:6px;
  color:var(--xp-indigo); font-size:.82rem; font-weight:600; text-decoration:none;
  padding:5px 12px; background:#ede9fe; border-radius:6px; transition:background .2s; }
.xp-sub-extlink:hover { background:#ddd6fe; }

/* ─── Actions bar ─── */
.xp-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap;
  padding:10px 16px; background:var(--xp-bg); border-top:1px solid var(--xp-line); }

/* Vote btn — une seule fois */
.xp-vote-btn { display:inline-flex; align-items:center; gap:7px; padding:8px 16px;
  border:2px solid var(--xp-line); background:var(--xp-white); border-radius:30px;
  cursor:pointer; font-size:.84rem; font-weight:700; color:var(--xp-sub);
  transition:all .2s; }
.xp-vote-btn:hover:not(:disabled) { border-color:var(--xp-red); color:var(--xp-red); background:#fff1f3; }
.xp-vote-btn--on { border-color:var(--xp-red) !important; color:var(--xp-red) !important;
  background:#fff1f3 !important; cursor:not-allowed !important; }
.xp-vote-btn--on svg { fill:var(--xp-red); }
.xp-vote-btn:disabled { opacity:.8; }
.xp-vote-btn--loading { opacity:.6; cursor:wait !important; }

.xp-vote-display { display:inline-flex; align-items:center; gap:6px; font-size:.84rem; font-weight:600; color:var(--xp-indigo); }
.xp-vote-display--muted { color:var(--xp-faint); }
.xp-login-hint { font-size:.76rem; font-weight:500; color:var(--xp-indigo); text-decoration:underline; margin-left:4px; }

.xp-action-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 13px;
  border:none; background:none; border-radius:8px; cursor:pointer;
  font-size:.82rem; font-weight:600; color:var(--xp-sub); transition:all .2s; }
.xp-action-btn:hover { background:#f3f4f6; color:var(--xp-indigo); }

.xp-voir-link { margin-left:auto; display:inline-flex; align-items:center; gap:5px;
  padding:8px 16px; background:linear-gradient(135deg,var(--xp-indigo),var(--xp-violet));
  color:#fff; border-radius:8px; text-decoration:none; font-size:.82rem; font-weight:700;
  transition:all .2s; white-space:nowrap; }
.xp-voir-link:hover { transform:translateY(-1px); box-shadow:0 4px 16px rgba(99,102,241,.38); }

/* ─── Panels expansibles ─── */
.xp-panel-sub { padding:14px 17px; border-top:1px solid var(--xp-line); background:#fafaff; }
.xp-panel-sub--comments { background:#f8faff; }
.xp-panel-sub-title { display:flex; align-items:center; gap:7px; font-size:.8rem;
  font-weight:700; color:var(--xp-indigo); margin-bottom:12px; }
.xp-panel-empty { font-size:.82rem; color:var(--xp-faint); text-align:center; padding:10px 0; margin:0; }

/* Voters */
.xp-voters-wrap { display:flex; flex-wrap:wrap; gap:7px; }
.xp-voter-chip { display:inline-flex; align-items:center; gap:7px; padding:6px 12px;
  background:var(--xp-white); border:1.5px solid var(--xp-line); border-radius:30px;
  text-decoration:none; color:var(--xp-ink); font-size:.8rem; font-weight:600; transition:all .2s; }
.xp-voter-chip:hover { border-color:var(--xp-indigo); color:var(--xp-indigo); }
.xp-voter-thumb { width:24px; height:24px; border-radius:50%; object-fit:cover; flex-shrink:0; }
.xp-voter-thumb--letter { background:linear-gradient(135deg,var(--xp-indigo),var(--xp-violet));
  color:#fff; display:flex; align-items:center; justify-content:center; font-size:.68rem; font-weight:800; }

/* Comment form */
.xp-cf { display:flex; align-items:flex-start; gap:10px; margin-bottom:14px; }
.xp-cf-av { width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,var(--xp-indigo),var(--xp-violet));
  color:#fff; display:flex; align-items:center; justify-content:center;
  font-size:.8rem; font-weight:800; flex-shrink:0; margin-top:2px; }
.xp-cf-right { flex:1; background:var(--xp-white); border:2px solid var(--xp-line);
  border-radius:14px; overflow:hidden; transition:border-color .2s; }
.xp-cf-right:focus-within { border-color:var(--xp-indigo); }
.xp-cf-txt { width:100%; border:none; background:transparent; font-size:.87rem;
  color:var(--xp-ink); padding:11px 14px 6px; resize:none; outline:none;
  font-family:inherit; line-height:1.5; }
.xp-cf-txt::placeholder { color:var(--xp-faint); }
.xp-cf-footer { display:flex; align-items:center; justify-content:space-between;
  padding:6px 10px 8px; }
.xp-cf-hint { font-size:.7rem; color:var(--xp-faint); }
.xp-cf-send { display:inline-flex; align-items:center; gap:6px; padding:6px 14px;
  background:linear-gradient(135deg,var(--xp-indigo),var(--xp-violet)); color:#fff;
  border:none; border-radius:8px; cursor:pointer; font-size:.8rem; font-weight:700;
  transition:all .2s; }
.xp-cf-send:hover { box-shadow:0 3px 12px rgba(99,102,241,.4); }
.xp-cf-send:disabled { opacity:.6; cursor:wait; }
.xp-login-cta { font-size:.84rem; color:var(--xp-faint); text-align:center; padding:8px 0; margin:0 0 12px; }
.xp-login-cta a { color:var(--xp-indigo); font-weight:600; }

/* Comment list */
.xp-comm-list { display:flex; flex-direction:column; gap:10px; }
.xp-comm-item { display:flex; gap:9px; }
.xp-comm-av { width:32px; height:32px; border-radius:50%; overflow:hidden;
  background:linear-gradient(135deg,var(--xp-indigo),var(--xp-violet));
  color:#fff; display:flex; align-items:center; justify-content:center;
  font-size:.74rem; font-weight:800; flex-shrink:0; }
.xp-comm-av img { width:100%; height:100%; object-fit:cover; }
.xp-comm-bubble { flex:1; background:var(--xp-white); border:1px solid var(--xp-line); border-radius:12px; padding:10px 14px; }
.xp-comm-meta { display:flex; align-items:baseline; gap:8px; margin-bottom:5px; }
.xp-comm-meta strong { font-size:.82rem; color:var(--xp-ink); }
.xp-comm-meta span   { font-size:.72rem; color:var(--xp-faint); }
.xp-comm-bubble p    { font-size:.86rem; color:var(--xp-sub); line-height:1.55; margin:0; overflow-wrap:break-word; }

/* ─── Toast ─── */
.xp-toast { position:fixed; bottom:28px; right:28px; z-index:9999;
  padding:12px 20px; border-radius:12px; font-size:.87rem; font-weight:600;
  color:#fff; box-shadow:0 6px 24px rgba(0,0,0,.18);
  transform:translateY(10px); opacity:0; pointer-events:none;
  transition:all .25s; }
.xp-toast--show { transform:translateY(0); opacity:1; }
.xp-toast--ok  { background:linear-gradient(135deg,#10b981,#059669); }
.xp-toast--err { background:linear-gradient(135deg,var(--xp-red),#be123c); }
.xp-toast--info{ background:linear-gradient(135deg,var(--xp-indigo),var(--xp-violet)); }

/* ─── Responsive ─── */
@media(max-width:680px){
  .xp-banner-inner { padding:22px 18px; flex-direction:column; align-items:flex-start; }
  .xp-stats { margin-left:0; width:100%; border-radius:14px; }
  .xp-stat { flex:1; padding:14px 12px; }
  .xp-ch-thumb { width:100px; }
  .xp-sub-hd { flex-direction:column; align-items:flex-start; }
  .xp-actions { gap:5px; }
  .xp-voir-link { margin-left:0; width:100%; justify-content:center; }
  .xp-defi-tag { max-width:100%; }
}
</style>

<!-- Toast DOM -->
<div class="xp-toast" id="xp-toast"></div>

<!-- ══════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════ -->
<script>
/* ── Tabs ── */
function xpTab(name, btn) {
    document.querySelectorAll('.xp-panel').forEach(p => p.classList.remove('xp-panel--active'));
    document.querySelectorAll('.xp-tab').forEach(t => t.classList.remove('xp-tab--active'));
    document.getElementById('xp-panel-' + name).classList.add('xp-panel--active');
    btn.classList.add('xp-tab--active');
}

/* ── Toggle panels ── */
function xpToggle(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

/* ── Toast ── */
let _toastTimer;
function xpToast(msg, type='ok') {
    const t = document.getElementById('xp-toast');
    clearTimeout(_toastTimer);
    t.textContent = msg;
    t.className = 'xp-toast xp-toast--' + type + ' xp-toast--show';
    _toastTimer = setTimeout(() => { t.className = 'xp-toast'; }, 3000);
}

/* ── VOTE (une seule fois) ── */
function xpVote(btn) {
    if(btn.disabled || btn.classList.contains('xp-vote-btn--loading')) return;
    const sid = btn.dataset.sid;
    btn.classList.add('xp-vote-btn--loading');
    btn.disabled = true;

    fetch('index.php?action=vote', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'submission_id=' + encodeURIComponent(sid)
    })
    .then(r => r.json())
    .then(data => {
        btn.classList.remove('xp-vote-btn--loading');

        if(data.action === 'added' && data.success) {
            // Vote accepté — bloquer définitivement
            const cnt = data.new_count ?? 0;
            document.getElementById('vcnt-' + sid).textContent = cnt;

            const vcnt2 = document.getElementById('vcnt2-' + sid);
            if(vcnt2) vcnt2.textContent = parseInt(vcnt2.textContent||0) + 1;

            btn.classList.add('xp-vote-btn--on');
            btn.querySelector('svg').setAttribute('fill', 'currentColor');
            btn.querySelector('.xp-vote-lbl').textContent = 'Voté ✓';
            btn.disabled = true;
            btn.title = 'Vous avez déjà voté';

            // Ajouter dans le panel votants
            _addVoterChip(sid,
                '<?= addslashes($_SESSION["user_id"] ?? "") ?>',
                '<?= addslashes($_SESSION["username"] ?? "") ?>'
            );
            xpToast('Votre vote a été enregistré !', 'ok');

        } else if(data.action === 'already_voted') {
            btn.classList.add('xp-vote-btn--on');
            btn.querySelector('svg').setAttribute('fill', 'currentColor');
            btn.querySelector('.xp-vote-lbl').textContent = 'Voté ✓';
            btn.disabled = true;
            xpToast('Vous avez déjà voté pour cette participation', 'info');

        } else if(data.message) {
            btn.disabled = false; // remettre si erreur
            xpToast(data.message, 'err');
        } else {
            btn.disabled = false;
            xpToast('Erreur inconnue', 'err');
        }
    })
    .catch(() => {
        btn.classList.remove('xp-vote-btn--loading');
        btn.disabled = false;
        xpToast('Erreur réseau', 'err');
    });
}

function _addVoterChip(sid, uid, username) {
    const wrap = document.getElementById('voters-list-' + sid);
    if(!wrap) return;
    // Supprimer "aucun vote"
    const empty = wrap.querySelector('.xp-panel-empty');
    if(empty) empty.remove();
    // Ne pas dupliquer
    if(wrap.querySelector('[data-uid="'+uid+'"]')) return;
    const chip = document.createElement('a');
    chip.href = 'index.php?action=viewProfile&id=' + uid;
    chip.className = 'xp-voter-chip';
    chip.dataset.uid = uid;
    chip.innerHTML =
        '<div class="xp-voter-thumb xp-voter-thumb--letter">' +
        username.charAt(0).toUpperCase() + '</div><span>' +
        _esc(username) + '</span>';
    wrap.prepend(chip);
}

/* ── COMMENTAIRE ── */
function xpComment(sid) {
    const txt = document.getElementById('ctxt-' + sid);
    const content = txt.value.trim();
    if(!content) { xpToast('Écrivez un commentaire d\'abord', 'err'); return; }

    const btn = document.getElementById('csend-' + sid);
    btn.disabled = true;

    fetch('index.php?action=addComment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'submission_id=' + encodeURIComponent(sid)
             + '&content=' + encodeURIComponent(content)
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if(data.success) {
            txt.value = '';
            const list = document.getElementById('clist-' + sid);
            const empty = list.querySelector('.xp-panel-empty');
            if(empty) empty.remove();

            const username = '<?= addslashes($_SESSION["username"] ?? "") ?>';
            const avatar   = <?= !empty($_SESSION['avatar']) ? '"'.addslashes($_SESSION['avatar']).'"' : 'null' ?>;
            const today = new Date().toLocaleDateString('fr-FR') + ' ' +
                          new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
            const avatarHtml = avatar
                ? '<img src="public/' + _esc(avatar) + '" alt="">'
                : username.charAt(0).toUpperCase();

            const item = document.createElement('div');
            item.className = 'xp-comm-item';
            item.innerHTML =
                '<div class="xp-comm-av">' + avatarHtml + '</div>' +
                '<div class="xp-comm-bubble">' +
                  '<div class="xp-comm-meta"><strong>' + _esc(username) + '</strong><span>' + today + '</span></div>' +
                  '<p>' + _esc(content).replace(/\n/g,'<br>') + '</p>' +
                '</div>';
            list.prepend(item);

            const ccnt = document.getElementById('ccnt-' + sid);
            if(ccnt) {
                const n = parseInt(ccnt.textContent||0) + 1;
                ccnt.textContent = n;
            }
            xpToast('Commentaire ajouté !', 'ok');
        } else {
            const msg = (data.errors && data.errors[0]) || data.message || 'Erreur';
            xpToast(msg, 'err');
        }
    })
    .catch(() => { btn.disabled = false; xpToast('Erreur réseau', 'err'); });
}

/* ── Escape HTML ── */
function _esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>