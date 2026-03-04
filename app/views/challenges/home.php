<?php
$title      = "Accueil - ChallengeHub";
$active_page = "home";

$current_category = $_GET['category'] ?? 'all';
$current_search   = $_GET['search']   ?? '';
$current_sort     = $_GET['sort']     ?? 'recent';

ob_start();
?>

<?php if(isset($_SESSION['user_id'])): ?>
<!-- ===================== VUE CONNECTÉ : STYLE FACEBOOK ===================== -->
<div class="fb-layout">

    <!-- COLONNE GAUCHE -->
    <aside class="fb-sidebar-left">
        <div class="sidebar-user-card">
            <div class="sidebar-avatar">
                <?php if(!empty($_SESSION['avatar'])): ?>
                    <img src="public/<?= htmlspecialchars($_SESSION['avatar']) ?>" alt="avatar">
                <?php else: ?>
                    <div class="avatar-initials"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="sidebar-user-info">
                <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                <a href="index.php?action=profile">Voir mon profil</a>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php?action=home" class="snav-item active">
                <i class="bi bi-house-fill"></i> Accueil
            </a>
            <a href="index.php?action=createChallengeForm" class="snav-item">
                <i class="bi bi-plus-lg-circle"></i> Créer un défi
            </a>
            <a href="index.php?action=ranking" class="snav-item">
                <i class="bi bi-trophy-fill"></i> Classement
            </a>
            <a href="index.php?action=search" class="snav-item">
                <i class="bi bi-search"></i> Recherche
            </a>
            <a href="index.php?action=profile" class="snav-item">
                <i class="bi bi-person-fill"></i> Mon profil
            </a>
        </nav>
    </aside>

    <!-- COLONNE CENTRE : FEED -->
    <main class="fb-feed">

        <!-- Barre de tri -->
        <div class="feed-sort-bar">
            <span class="sort-label"><i class="bi bi-funnel-fill"></i> Trier par :</span>
            <a href="?action=home&sort=recent&category=<?= urlencode($current_category) ?>&search=<?= urlencode($current_search) ?>"
               class="sort-btn <?= $current_sort === 'recent' ? 'active' : '' ?>">
                <i class="bi bi-clock-fill"></i> Plus récents
            </a>
            <a href="?action=home&sort=likes&category=<?= urlencode($current_category) ?>&search=<?= urlencode($current_search) ?>"
               class="sort-btn <?= $current_sort === 'likes' ? 'active' : '' ?>">
                <i class="bi bi-hand-thumbs-up-fill"></i> Plus votés
            </a>
            <a href="?action=home&sort=participations&category=<?= urlencode($current_category) ?>&search=<?= urlencode($current_search) ?>"
               class="sort-btn <?= $current_sort === 'participations' ? 'active' : '' ?>">
                <i class="bi bi-person-fills"></i> Plus participés
            </a>
        </div>

        <!-- FEED DES DÉFIS -->
        <?php if(isset($challenges) && !empty($challenges)): ?>
            <?php foreach($challenges as $challenge): ?>
            <div class="post-card" id="post-<?= $challenge['id'] ?>">

                <!-- En-tête du post -->
                <div class="post-header">
                    <div class="post-author-avatar">
                        <?php if(!empty($challenge['creator_avatar'])): ?>
                            <img src="public/<?= htmlspecialchars($challenge['creator_avatar']) ?>" alt="avatar">
                        <?php else: ?>
                            <div class="avatar-initials sm"><?= strtoupper(substr($challenge['creator_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="post-author-info">
                        <a href="index.php?action=viewProfile&id=<?= $challenge['user_id'] ?>" class="creator-link"><strong><?= htmlspecialchars($challenge['creator_name']) ?></strong></a>
                        <span class="post-meta">
                            <span class="category-tag"><?= htmlspecialchars($challenge['category']) ?></span>
                            · <?= date('d/m/Y à H:i', strtotime($challenge['created_at'])) ?>
                        </span>
                    </div>
                    <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="post-link-btn" title="Voir le défi">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>

                <!-- Contenu du post -->
                <div class="post-body">
                    <h3 class="post-title">
                        <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>">
                            <?= htmlspecialchars($challenge['title']) ?>
                        </a>
                    </h3>
                    <p class="post-description">
                        <?= htmlspecialchars(substr($challenge['description'], 0, 200)) ?>
                        <?= strlen($challenge['description']) > 200 ? '...' : '' ?>
                    </p>

                    <?php if(!empty($challenge['image'])): ?>
                    <div class="post-image">
                        <img src="public/<?= htmlspecialchars($challenge['image']) ?>"
                             alt="<?= htmlspecialchars($challenge['title']) ?>">
                    </div>
                    <?php endif; ?>

                    <?php 
                    $dl = $challenge['deadline'] ?? '';
                    $dl_ts = $dl ? strtotime($dl) : 0;
                    if(!empty($dl) && $dl_ts > 0 && $dl_ts > mktime(0,0,0,1,1,2000)):
                    ?>
                    <div class="post-deadline">
                        <i class="bi bi-clock-fill"></i>
                        Date limite : <strong><?= date('d/m/Y', $dl_ts) ?></strong>
                        <?php
                            $diff = $dl_ts - time();
                            if ($diff > 0 && $diff < 86400 * 3) echo '<span class="deadline-urgent">· Urgent !</span>';
                            elseif ($diff < 0) echo '<span class="deadline-passed">· Terminé</span>';
                        ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Compteurs -->
                <div class="post-counts">
                    <span>
                        <i class="bi bi-hand-thumbs-up-fill text-red"></i>
                        <span id="like-count-<?= $challenge['id'] ?>"><?= isset($challenge['likes_count']) ? $challenge['likes_count'] : 0 ?></span> voter
                    </span>
                    <span>
                        <i class="bi bi-person-fills text-blue"></i>
                        <?= isset($challenge['submissions_count']) ? $challenge['submissions_count'] : 0 ?> participation<?= (isset($challenge['submissions_count']) && $challenge['submissions_count'] > 1) ? 's' : '' ?>
                    </span>
                    <span>
                        <i class="bi bi-chat-fill text-gray"></i>
                        <span id="comment-count-<?= $challenge['id'] ?>"><?= isset($challenge['comments_count']) ? $challenge['comments_count'] : 0 ?></span> commentaire<?= (isset($challenge['comments_count']) && $challenge['comments_count'] > 1) ? 's' : '' ?>
                    </span>
                </div>

                <!-- Actions -->
                <div class="post-actions">
                    <button class="action-btn vote-btn <?= !empty($challenge['user_liked']) ? 'liked' : '' ?>"
                            data-id="<?= $challenge['id'] ?>"
                            onclick="toggleLike(<?= $challenge['id'] ?>)">
                        <i class="<?= !empty($challenge['user_liked']) ? 'fas' : 'far' ?> fa-thumbs-up"></i>
                        <span><?= $challenge['user_liked'] ? "Voter" : "Voter" ?></span>
                    </button>

                    <button class="action-btn comment-btn" onclick="toggleComments(<?= $challenge['id'] ?>)">
                        <i class="bi bi-chat-fill"></i>
                        <span>Commenter</span>
                    </button>

                    <?php if(empty($challenge['user_participated'])): ?>
                    <a href="index.php?action=createSubmissionForm&challenge_id=<?= $challenge['id'] ?>" class="action-btn participate-btn">
                        <i class="bi bi-flag-fill"></i>
                        <span>Participer</span>
                    </a>
                    <?php else: ?>
                    <span class="action-btn participated-badge">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Participé !</span>
                    </span>
                    <?php endif; ?>

                    <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="action-btn view-btn">
                        <i class="bi bi-eye-fill"></i>
                        <span>Voir tout</span>
                    </a>
                </div>

                <!-- Section commentaires -->
                <div class="post-comments" id="comments-<?= $challenge['id'] ?>" style="display:none;">
                    <div class="comments-list" id="comments-list-<?= $challenge['id'] ?>">
                        <?php if(!empty($challenge['comments'])): ?>
                            <?php foreach(isset($challenge['comments']) ? $challenge['comments'] : [] as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-avatar">
                                    <?php if(!empty($comment['avatar'])): ?>
                                        <img src="public/<?= htmlspecialchars($comment['avatar']) ?>" alt="avatar">
                                    <?php else: ?>
                                        <div class="avatar-initials xs"><?= strtoupper(substr($comment['username'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="comment-bubble">
                                    <strong><?= htmlspecialchars($comment['username']) ?></strong>
                                    <p><?= htmlspecialchars($comment['content']) ?></p>
                                    <small><?= date('d/m à H:i', strtotime($comment['created_at'])) ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-comments">Aucun commentaire. Soyez le premier !</p>
                        <?php endif; ?>
                    </div>

                    <!-- Formulaire commentaire -->
                    <div class="comment-form">
                        <div class="comment-avatar">
                            <div class="avatar-initials xs"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                        </div>
                        <div class="comment-input-wrap">
                            <input type="text"
                                   id="comment-input-<?= $challenge['id'] ?>"
                                   placeholder="Écrire un commentaire..."
                                   onkeypress="submitComment(event, <?= $challenge['id'] ?>)">
                            <button onclick="submitComment(null, <?= $challenge['id'] ?>, true)">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-feed">
                <i class="bi bi-trophy-fill fa-3x"></i>
                <h3>Aucun défi pour l'instant</h3>
                <p>Soyez le premier à créer un défi !</p>
                <a href="index.php?action=createChallengeForm" class="btn-create">
                    <i class="bi bi-plus-lg"></i> Créer un défi
                </a>
            </div>
        <?php endif; ?>
    </main>

    <!-- COLONNE DROITE -->
    <aside class="fb-sidebar-right">
        <div class="widget">
            <h4 class="widget-title"><i class="bi bi-fire"></i> Top défis</h4>
            <?php if(!empty($challenges)): ?>
                <?php $top = array_slice($challenges, 0, 5); ?>
                <?php foreach($top as $i => $c): ?>
                <a href="index.php?action=showChallenge&id=<?= $c['id'] ?>" class="top-challenge-item">
                    <span class="rank-number"><?= $i + 1 ?></span>
                    <span class="rank-title"><?= htmlspecialchars(substr($c['title'], 0, 30)) ?><?= strlen($c['title']) > 30 ? '...' : '' ?></span>
                    <span class="rank-likes"><i class="bi bi-hand-thumbs-up-fill"></i> <?= $c['likes_count'] ?></span>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="widget">
            <h4 class="widget-title"><i class="bi bi-search"></i> Filtres</h4>
            <form action="index.php" method="GET">
                <input type="hidden" name="action" value="home">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($current_sort) ?>">
                <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($current_search) ?>" class="widget-search">
                <select name="category" class="widget-select">
                    <option value="all" <?= $current_category === 'all' ? 'selected' : '' ?>>Toutes les catégories</option>
                    <option value="Design"    <?= $current_category === 'Design'    ? 'selected' : '' ?>>🎨 Design</option>
                    <option value="Code"      <?= $current_category === 'Code'      ? 'selected' : '' ?>>💻 Code / Programmation</option>
                    <option value="Art"       <?= $current_category === 'Art'       ? 'selected' : '' ?>>🖼️ Art / Illustration</option>
                    <option value="Écriture"  <?= $current_category === 'Écriture'  ? 'selected' : '' ?>>✍️ Écriture</option>
                    <option value="Photo"     <?= $current_category === 'Photo'     ? 'selected' : '' ?>>📷 Photographie</option>
                    <option value="Vidéo"     <?= $current_category === 'Vidéo'     ? 'selected' : '' ?>>🎬 Vidéo</option>
                    <option value="Musique"   <?= $current_category === 'Musique'   ? 'selected' : '' ?>>🎵 Musique</option>
                    <option value="Autre"     <?= $current_category === 'Autre'     ? 'selected' : '' ?>>📦 Autre</option>
                </select>
                <button type="submit" class="widget-btn"><i class="bi bi-search"></i> Filtrer</button>
            </form>
        </div>
    </aside>

</div><!-- end fb-layout -->
<?php else: ?>
<!-- ===================== VUE DÉCONNECTÉ ===================== -->
<div class="guest-layout">
        <div class="hero-section">
            <div class="hero-inner">
                <div class="hero-badge"><i class="bi bi-trophy-fill"></i> Plateforme de défis créatifs</div>
                <h1 class="hero-title">Challenge<span>Hub</span></h1>

                <div class="hero-features">
                    <div class="hero-feature"><i class="bi bi-flag-fill"></i><span>Défis variés</span></div>
                    <div class="hero-feature"><i class="bi bi-person-fills"></i><span>Communauté active</span></div>
                    <div class="hero-feature"><i class="bi bi-hand-thumbs-up-fill"></i><span>Système de votes</span></div>
                    <div class="hero-feature"><i class="bi bi-award-fill"></i><span>Classements</span></div>
                </div>
                <div class="hero-cta">
                    <a href="index.php?action=showRegister" class="btn-hero-primary"><i class="bi bi-person-plus-fill"></i> Rejoindre la communauté</a>
                    <a href="index.php?action=showLogin" class="btn-hero-secondary"><i class="bi bi-box-arrow-in-right"></i> Se connecter</a>
                </div>
            </div>
        </div>

        <div class="about-section">
            <div class="about-grid">
                <div class="about-card"><div class="about-icon"><i class="bi bi-flag-fill"></i></div><h3>Relevez des défis</h3><p>Des dizaines de défis créatifs dans des catégories variées.</p></div>
                <div class="about-card"><div class="about-icon"><i class="bi bi-upload"></i></div><h3>Partagez vos créations</h3><p>Soumettez vos réalisations et recevez des retours.</p></div>
                <div class="about-card"><div class="about-icon"><i class="bi bi-hand-thumbs-up-fill"></i></div><h3>Votez & encouragez</h3><p>Likez les soumissions qui vous inspirent.</p></div>
                <div class="about-card"><div class="about-icon"><i class="bi bi-trophy-fill"></i></div><h3>Grimpez en classement</h3><p>Accumulez des votes et montez dans le classement.</p></div>
            </div>
        </div>

        <div class="filters-section">
            <h2 class="section-title"><i class="bi bi-fire"></i> Les défis en cours</h2>
            <form action="index.php" method="GET" class="filters-form">
                <input type="hidden" name="action" value="home">
                <div class="filters-grid">
                    <div class="filter-group">
                        <i class="bi bi-search filter-icon"></i>
                        <input type="text" name="search" placeholder="Rechercher un défi..." value="<?= htmlspecialchars($current_search) ?>">
                    </div>
                    <div class="filter-group">
                        <select name="category">
                            <option value="all">Toutes les catégories</option>
                            <?php if(isset($categories)): foreach($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $current_category === $cat['category'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="sort">
                            <option value="recent" <?= $current_sort === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                            <option value="likes" <?= $current_sort === 'likes' ? 'selected' : '' ?>>Plus votés</option>
                            <option value="participations" <?= $current_sort === 'participations' ? 'selected' : '' ?>>Plus participés</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter"><i class="bi bi-search"></i> Filtrer</button>
                </div>
            </form>
        </div>

        <div class="challenges-grid">
            <?php if(isset($challenges) && !empty($challenges)): ?>
                <?php foreach($challenges as $challenge): ?>
                <div class="challenge-card">
                    <div class="card-image">
                        <?php if(!empty($challenge['image'])): ?>
                            <img src="public/<?= htmlspecialchars($challenge['image']) ?>" alt="<?= htmlspecialchars($challenge['title']) ?>">
                        <?php else: ?>
                            <div class="no-image"><i class="bi bi-card-image"></i></div>
                        <?php endif; ?>
                        <span class="category-badge"><?= htmlspecialchars($challenge['category']) ?></span>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title"><a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>"><?= htmlspecialchars($challenge['title']) ?></a></h3>
                        <p class="card-description"><?= htmlspecialchars(substr($challenge['description'], 0, 120)) ?>...</p>
                        <div class="card-meta">
                            <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($challenge['creator_name']) ?></span>
                            <span><i class="bi bi-hand-thumbs-up-fill"></i> <?= $challenge['likes_count'] ?? 0 ?></span>
                            <span><i class="bi bi-person-fills"></i> <?= $challenge['submissions_count'] ?? 0 ?></span>
                        </div>
                        <div class="card-footer">
                            <a href="index.php?action=showChallenge&id=<?= $challenge['id'] ?>" class="btn-view">Voir le défi <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-challenges">
                    <i class="bi bi-list-check fa-4x"></i>
                    <h3>Aucun défi trouvé</h3>
                    <p>Inscrivez-vous pour créer le premier défi !</p>
                    <a href="index.php?action=showRegister" class="btn-primary"><i class="bi bi-person-plus-fill"></i> S'inscrire</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ===================== STYLES ===================== -->
<style>
/* ===== LAYOUT FACEBOOK ===== */
.fb-layout {
    display: grid;
    grid-template-columns: 260px 1fr 280px;
    gap: 24px;
    align-items: start;
    max-width: 1200px;
    margin: 0 auto;
    overflow-x: hidden;
}

/* ===== SIDEBAR GAUCHE ===== */
.fb-sidebar-left {
    position: sticky;
    top: 80px;
}

.sidebar-user-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    margin-bottom: 8px;
    border: 1px solid #f0f0f0;
}

.sidebar-avatar img,
.sidebar-avatar .avatar-initials {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-initials {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    border-radius: 50%;
    width: 42px;
    height: 42px;
}
.avatar-initials.sm { width: 36px; height: 36px; font-size: 0.95rem; }
.avatar-initials.xs { width: 30px; height: 30px; font-size: 0.8rem; }

.sidebar-user-info strong { display: block; font-size: 0.95rem; color: #222; }
.sidebar-user-info a { font-size: 0.8rem; color: #667eea; text-decoration: none; }
.sidebar-user-info a:hover { text-decoration: underline; }

.sidebar-nav { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); padding: 8px; border: 1px solid #f0f0f0; }

.snav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 8px;
    color: #444;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.2s;
}
.snav-item i { width: 20px; text-align: center; color: #667eea; }
.snav-item:hover, .snav-item.active { background: #f0f2ff; color: #667eea; }

/* ===== FEED CENTRAL ===== */
.fb-feed { display: flex; flex-direction: column; gap: 16px;     overflow: hidden;
    min-width: 0;
}

.feed-sort-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 12px 16px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    flex-wrap: wrap;
}

.sort-label { font-size: 0.85rem; color: #888; font-weight: 500; }

.sort-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #555;
    text-decoration: none;
    background: #f4f6f8;
    transition: all 0.2s;
}
.sort-btn:hover { background: #e8eaff; color: #667eea; }
.sort-btn.active { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }

/* ===== POST CARD ===== */
.post-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    overflow: hidden;
    transition: box-shadow 0.3s;
}
.post-card:hover { box-shadow: 0 6px 20px rgba(102,126,234,0.12); }

.post-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 16px 0;
}

.post-author-avatar img,
.post-author-avatar .avatar-initials {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.post-author-info { flex: 1; }
.post-author-info strong { display: block; font-size: 0.95rem; color: #222; }
.post-meta { font-size: 0.78rem; color: #999; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

.category-tag {
    background: #f0f2ff;
    color: #667eea;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.72rem;
    font-weight: 700;
}

.post-link-btn {
    color: #aaa;
    font-size: 0.85rem;
    padding: 6px 10px;
    border-radius: 8px;
    transition: all 0.2s;
    text-decoration: none;
}
.post-link-btn:hover { background: #f4f6f8; color: #667eea; }

.post-body { padding: 12px 16px; overflow: hidden; max-width: 100%; }

.post-title { margin-bottom: 6px;     overflow-wrap: break-word;
    word-break: break-word;
}
.post-title a { font-size: 1.05rem; font-weight: 700; color: #222; text-decoration: none; transition: color 0.2s; }
.post-title a:hover { color: #667eea; }

.creator-link { color: inherit; text-decoration: none; }
.creator-link:hover strong { color: #667eea; text-decoration: underline; }
.post-description { color: #555; font-size: 0.92rem; line-height: 1.6; margin-bottom: 10px; overflow-wrap: break-word; word-break: break-word; }

.post-image { margin: 10px -0px; border-radius: 8px; overflow: hidden; }
.post-image img { width: 100%; max-height: 380px; object-fit: cover; display: block; }

.post-deadline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    color: #888;
    background: #f8f9fa;
    padding: 5px 12px;
    border-radius: 20px;
    margin-top: 8px;
}
.deadline-urgent { color: #f59e0b; font-weight: 700; }
.deadline-passed { color: #dc3545; font-weight: 700; }

.post-counts {
    display: flex;
    gap: 16px;
    padding: 8px 16px;
    font-size: 0.82rem;
    color: #888;
    border-bottom: 1px solid #f0f0f0;
}
.post-counts span { display: flex; align-items: center; gap: 5px; }
.text-red { color: #ef4444; }
.text-blue { color: #3b82f6; }
.text-gray { color: #9ca3af; }

/* ===== ACTIONS ===== */
.post-actions {
    display: flex;
    border-bottom: 1px solid #f0f0f0;
}

.action-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 0;
    font-size: 0.88rem;
    font-weight: 600;
    color: #555;
    background: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    border-radius: 0;
}
.action-btn:hover { background: #f4f6f8; color: #667eea; }

.vote-btn.liked { color: #ef4444; }
.vote-btn.liked i { color: #ef4444; }

.participate-btn:hover { color: #10b981; }
.participated-badge { color: #10b981; cursor: default; }
.participated-badge:hover { background: none; }

/* ===== COMMENTAIRES ===== */
.post-comments { padding: 12px 16px; background: #fafafa; }

.comments-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px; }

.comment-item { display: flex; gap: 8px; }
.comment-avatar img,
.comment-avatar .avatar-initials { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }

.comment-bubble {
    background: white;
    border-radius: 12px;
    padding: 8px 12px;
    font-size: 0.86rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    flex: 1;
}
.comment-bubble strong { display: block; font-size: 0.82rem; color: #333; margin-bottom: 2px; }
.comment-bubble p { color: #444; margin: 0 0 3px; line-height: 1.4; }
.comment-bubble small { color: #bbb; font-size: 0.74rem; }

.no-comments { text-align: center; color: #bbb; font-size: 0.85rem; padding: 10px; }

.comment-form { display: flex; gap: 8px; align-items: center; }

.comment-input-wrap {
    flex: 1;
    display: flex;
    align-items: center;
    background: white;
    border: 1.5px solid #e0e0e0;
    border-radius: 24px;
    overflow: hidden;
    padding: 0 6px 0 14px;
    transition: border-color 0.2s;
}
.comment-input-wrap:focus-within { border-color: #667eea; }
.comment-input-wrap input { flex: 1; border: none; outline: none; font-size: 0.88rem; padding: 8px 0; background: transparent; }
.comment-input-wrap button {
    background: none;
    border: none;
    color: #667eea;
    font-size: 1rem;
    cursor: pointer;
    padding: 6px 8px;
    transition: color 0.2s;
}
.comment-input-wrap button:hover { color: #764ba2; }

/* ===== SIDEBAR DROITE ===== */
.fb-sidebar-right { position: sticky; top: 80px; }

.widget {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 16px;
    margin-bottom: 16px;
    border: 1px solid #f0f0f0;
}

.widget-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.widget-title i { color: #667eea; }

.top-challenge-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
    text-decoration: none;
    color: #333;
    font-size: 0.85rem;
    transition: color 0.2s;
}
.top-challenge-item:last-child { border-bottom: none; }
.top-challenge-item:hover { color: #667eea; }

.rank-number {
    width: 22px;
    height: 22px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 700;
    flex-shrink: 0;
}
.rank-title { flex: 1; line-height: 1.3; }
.rank-likes { font-size: 0.78rem; color: #ef4444; white-space: nowrap; }
.rank-likes i { margin-right: 3px; }

.widget-search, .widget-select {
    width: 100%;
    padding: 8px 12px;
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.88rem;
    margin-bottom: 10px;
    outline: none;
    transition: border-color 0.2s;
}
.widget-search:focus, .widget-select:focus { border-color: #667eea; }

.widget-btn {
    width: 100%;
    padding: 9px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: opacity 0.2s;
}
.widget-btn:hover { opacity: 0.9; }

/* ===== EMPTY FEED ===== */
.empty-feed {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}
.empty-feed i { color: #ddd; margin-bottom: 16px; display: block; }
.empty-feed h3 { color: #333; margin-bottom: 8px; }
.empty-feed p { color: #888; margin-bottom: 20px; }
.btn-create {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
}

/* ===== VUE DÉCONNECTÉ ===== */
.guest-layout {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
}
.hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; margin-bottom: 40px; padding: 70px 40px; text-align: center; color: white; position: relative; overflow: hidden; }
.hero-section::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 60%); pointer-events: none; }
.hero-inner { position: relative; z-index: 1; }
.hero-badge { display: inline-block; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); padding: 6px 18px; border-radius: 30px; font-size: 0.85rem; margin-bottom: 20px; }
.hero-title { font-size: 3.5rem; font-weight: 800; margin-bottom: 16px; }
.hero-title span { color: #ffd700; }
.hero-subtitle { font-size: 1.1rem; opacity: 0.9; max-width: 560px; margin: 0 auto 30px; line-height: 1.7; }
.hero-features { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 30px; }
.hero-feature { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); padding: 7px 16px; border-radius: 30px; font-size: 0.88rem; }
.hero-feature i { color: #ffd700; }
.hero-cta { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
.btn-hero-primary { display: inline-flex; align-items: center; gap: 8px; padding: 13px 28px; background: #ffd700; color: #333; border-radius: 10px; font-weight: 700; text-decoration: none; transition: all 0.3s; }
.btn-hero-primary:hover { background: #ffcc00; transform: translateY(-2px); }
.btn-hero-secondary { display: inline-flex; align-items: center; gap: 8px; padding: 13px 28px; background: rgba(255,255,255,0.15); color: white; border: 2px solid rgba(255,255,255,0.4); border-radius: 10px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
.btn-hero-secondary:hover { background: rgba(255,255,255,0.25); transform: translateY(-2px); }
.about-section { margin-bottom: 50px; }
.about-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
.about-card { background: white; border-radius: 14px; padding: 26px 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; transition: all 0.3s; }
.about-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(102,126,234,0.15); }
.about-icon { width: 56px; height: 56px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 1.3rem; color: white; }
.about-card h3 { font-size: 0.95rem; font-weight: 700; color: #333; margin-bottom: 8px; }
.about-card p { font-size: 0.85rem; color: #666; line-height: 1.6; }
.section-title { font-size: 1.4rem; font-weight: 700; color: #333; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; }
.section-title i { color: #667eea; }
.filters-section { background: white; padding: 22px; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 28px; border: 1px solid #f0f0f0; }
.filters-grid { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px; align-items: center; }
.filter-group { position: relative; }
.filter-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 0.82rem; pointer-events: none; }
.filter-group input { width: 100%; padding: 10px 12px 10px 34px; border: 2px solid #e8e8e8; border-radius: 9px; font-size: 0.92rem; transition: border-color 0.3s; }
.filter-group select { width: 100%; padding: 10px 12px; border: 2px solid #e8e8e8; border-radius: 9px; font-size: 0.92rem; background: white; }
.filter-group input:focus, .filter-group select:focus { outline: none; border-color: #667eea; }
.btn-filter { padding: 10px 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 9px; cursor: pointer; font-weight: 600; font-size: 0.92rem; white-space: nowrap; display: flex; align-items: center; gap: 7px; transition: all 0.3s; }
.btn-filter:hover { transform: translateY(-2px); box-shadow: 0 5px 16px rgba(102,126,234,0.4); }
.challenges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 24px; }
.challenge-card { background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: all 0.3s; border: 1px solid #f0f0f0; }
.challenge-card:hover { transform: translateY(-6px); box-shadow: 0 14px 35px rgba(102,126,234,0.16); }
.card-image { height: 190px; position: relative; overflow: hidden; }
.card-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
.challenge-card:hover .card-image img { transform: scale(1.05); }
.no-image { width: 100%; height: 100%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.2); font-size: 3.5rem; }
.category-badge { position: absolute; top: 12px; right: 12px; padding: 3px 12px; background: rgba(255,255,255,0.92); border-radius: 18px; font-size: 0.75rem; font-weight: 700; color: #667eea; }
.card-content { padding: 18px; }
.card-title { margin-bottom: 8px; }
.card-title a { font-size: 1rem; color: #222; text-decoration: none; font-weight: 700; transition: color 0.2s; }
.card-title a:hover { color: #667eea; }
.card-description { color: #666; margin-bottom: 12px; line-height: 1.6; font-size: 0.88rem; }
.card-meta { display: flex; gap: 14px; font-size: 0.8rem; color: #999; margin-bottom: 12px; flex-wrap: wrap; }
.card-meta i { margin-right: 3px; color: #667eea; }
.card-footer { padding-top: 12px; border-top: 1px solid #f0f0f0; }
.btn-view { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: transparent; border: 2px solid #667eea; color: #667eea; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.85rem; transition: all 0.3s; }
.btn-view:hover { background: #667eea; color: white; }
.no-challenges { grid-column: 1/-1; text-align: center; padding: 50px 30px; background: #f8f9fa; border-radius: 14px; }
.no-challenges i { color: #ccc; margin-bottom: 16px; display: block; }
.no-challenges h3 { color: #333; margin-bottom: 8px; }
.no-challenges p { color: #666; margin-bottom: 20px; }
.btn-primary { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 9px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 7px 18px rgba(102,126,234,0.4); }

/* ===== RESPONSIVE ===== */
@media (max-width: 1100px) {
    .fb-layout { grid-template-columns: 220px 1fr; }
    .fb-sidebar-right { display: none; }
}

@media (max-width: 768px) {
    .fb-layout { grid-template-columns: 1fr; }
    .fb-sidebar-left { display: none; }
    .filters-grid { grid-template-columns: 1fr; }
    .hero-title { font-size: 2.2rem; }
    .post-actions { flex-wrap: wrap; }
    .action-btn { min-width: 50%; }
}
</style>

<!-- ===================== JAVASCRIPT ===================== -->
<script>
// Toggle like sur un défi
function toggleLike(challengeId) {
    fetch('index.php?action=toggleChallengeLike', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'challenge_id=' + challengeId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success === false && data.redirect) {
            window.location.href = data.redirect;
            return;
        }
        const btn = document.querySelector('.vote-btn[data-id="' + challengeId + '"]');
        const countEl = document.getElementById('like-count-' + challengeId);
        if (data.action === 'liked') {
            btn.classList.add('liked');
            btn.querySelector('i').className = 'bi bi-hand-thumbs-up-fill';
        } else {
            btn.classList.remove('liked');
            btn.querySelector('i').className = 'bi bi-hand-thumbs-up-fill';
        }
        countEl.textContent = data.count;
    })
    .catch(e => console.error(e));
}

// Charger les commentaires depuis le serveur
function loadChallengeComments(challengeId) {
    fetch('index.php?action=getChallengeComments&challenge_id=' + challengeId)
    .then(r => r.json())
    .then(data => {
        if(!data.success) return;
        const list = document.getElementById('comments-list-' + challengeId);
        list.innerHTML = '';
        if(data.comments.length === 0) {
            list.innerHTML = '<p class="no-comments">Aucun commentaire</p>';
            return;
        }
        data.comments.forEach(c => {
            const initial = c.username ? c.username.charAt(0).toUpperCase() : '?';
            list.innerHTML += `
                <div class="comment-item">
                    <div class="comment-avatar">
                        <div class="avatar-initials xs">${initial}</div>
                    </div>
                    <div class="comment-bubble">
                        <strong>${c.username}</strong>
                        <p>${c.content}</p>
                        <small>${new Date(c.created_at).toLocaleDateString('fr-FR')}</small>
                    </div>
                </div>`;
        });
        // Mettre à jour le compteur
        const counter = document.getElementById('comment-count-' + challengeId);
        if(counter) counter.textContent = data.comments.length;
    })
    .catch(e => console.error(e));
}

// Afficher/masquer les commentaires
function toggleComments(challengeId) {
    const section = document.getElementById('comments-' + challengeId);
    if (section.style.display === 'none') {
        section.style.display = 'block';
        loadChallengeComments(challengeId);
        document.getElementById('comment-input-' + challengeId)?.focus();
    } else {
        section.style.display = 'none';
    }
}

// Soumettre un commentaire
function submitComment(event, challengeId, force = false) {
    if (event && event.key !== 'Enter') return;

    const input = document.getElementById('comment-input-' + challengeId);
    const content = input.value.trim();
    if (!content) return;

    fetch('index.php?action=addChallengeComment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'challenge_id=' + challengeId + '&content=' + encodeURIComponent(content)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success === false && data.redirect) {
            window.location.href = data.redirect;
            return;
        }
        if (data.success) {
            const list = document.getElementById('comments-list-' + challengeId);
            const noComment = list.querySelector('.no-comments');
            if (noComment) noComment.remove();

            const initial = data.username ? data.username.charAt(0).toUpperCase() : '?';
            const html = `
                <div class="comment-item">
                    <div class="comment-avatar">
                        <div class="avatar-initials xs">${initial}</div>
                    </div>
                    <div class="comment-bubble">
                        <strong>${data.username}</strong>
                        <p>${data.content}</p>
                        <small>À l'instant</small>
                    </div>
                </div>`;
            list.insertAdjacentHTML('beforeend', html);
            input.value = '';
            // Recharger les commentaires depuis le serveur
            loadChallengeComments(challengeId);
        }
    })
    .catch(e => console.error(e));
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>