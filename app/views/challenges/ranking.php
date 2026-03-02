<?php
// app/views/challenges/ranking.php
// Page de classement des participations

$title = "Classement - ChallengeHub";
$active_page = "ranking";

ob_start();
?>

<div class="ranking-page">
    <div class="ranking-header">
        <h1 class="page-title">
            <span class="glitch-text" data-text="CLASSEMENT">CLASSEMENT</span>
        </h1>
        <p class="page-subtitle">Les participations les mieux notées de la communauté</p>
        
        <!-- Filtre par catégorie -->
        <div class="ranking-filters">
            <form action="index.php" method="GET" class="filter-form">
                <input type="hidden" name="action" value="ranking">
                
                <div class="filter-group">
                    <label for="category">Catégorie :</label>
                    <select name="category" id="category" onchange="this.form.submit()">
                        <option value="all" <?= ($_GET['category'] ?? 'all') == 'all' ? 'selected' : '' ?>>
                            Toutes les catégories
                        </option>
                        <?php if(isset($categories) && !empty($categories)): ?>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>" 
                                    <?= ($_GET['category'] ?? '') == $cat['category'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="limit">Nombre :</label>
                    <select name="limit" id="limit" onchange="this.form.submit()">
                        <option value="10" <?= ($_GET['limit'] ?? 20) == 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= ($_GET['limit'] ?? 20) == 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= ($_GET['limit'] ?? 20) == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($_GET['limit'] ?? 20) == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Classement -->
    <div class="ranking-list">
        <?php if(isset($ranking) && !empty($ranking)): ?>
            <?php foreach($ranking as $index => $item): ?>
                <div class="ranking-item <?= $index < 3 ? 'top-' . ($index+1) : '' ?>">
                    <div class="ranking-position">
                        <?php if($index == 0): ?>
                            <div class="crown">👑</div>
                        <?php endif; ?>
                        <span class="position-number">#<?= $index + 1 ?></span>
                    </div>
                    
                    <div class="ranking-content">
                        <div class="ranking-user">
                            <img src="public/<?= htmlspecialchars($item['avatar'] ?? 'images/default-avatar.png') ?>" 
                                 alt="<?= htmlspecialchars($item['username']) ?>" 
                                 class="avatar-small">
                            <span class="username"><?= htmlspecialchars($item['username']) ?></span>
                        </div>
                        
                        <div class="ranking-details">
                            <h3 class="ranking-title">
                                <a href="index.php?action=showSubmission&id=<?= $item['id'] ?>">
                                    Participation à "<?= htmlspecialchars($item['challenge_title']) ?>"
                                </a>
                            </h3>
                            
                            <p class="ranking-description">
                                <?= htmlspecialchars(substr($item['description'], 0, 100)) ?>...
                            </p>
                        </div>
                        
                        <div class="ranking-votes">
                            <div class="votes-count">
                                <i class="fas fa-thumbs-up"></i>
                                <span class="count"><?= $item['votes_count'] ?></span>
                                <span class="label">vote<?= $item['votes_count'] > 1 ? 's' : '' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-ranking">
                <i class="fas fa-trophy fa-4x"></i>
                <h3>Aucune participation pour le moment</h3>
                <p>Les meilleures participations apparaîtront ici</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.ranking-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.ranking-filters {
    margin-bottom: 30px;
    display: flex;
    justify-content: center;
}

.filter-form {
    display: flex;
    gap: 20px;
    background: rgba(0,255,127,0.05);
    padding: 20px;
    border-radius: 8px;
    border: 1px solid rgba(0,255,127,0.1);
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-group label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.filter-group select {
    padding: 8px 12px;
    background: rgba(0,255,127,0.03);
    border: 1px solid rgba(0,255,127,0.1);
    border-radius: 4px;
    color: var(--text-primary);
    font-family: var(--font-primary);
    cursor: pointer;
}

.filter-group select:hover {
    border-color: var(--green-primary);
}

.ranking-item {
    background: rgba(0,255,127,0.02);
    border: 1px solid rgba(0,255,127,0.1);
    border-radius: 8px;
    margin-bottom: 15px;
    padding: 20px;
    display: flex;
    gap: 20px;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.ranking-item:hover {
    border-color: var(--green-primary);
    transform: translateX(5px);
    box-shadow: 0 0 30px rgba(0,255,127,0.1);
}

.ranking-item.top-1 {
    background: linear-gradient(135deg, rgba(255,215,0,0.1), rgba(0,255,127,0.05));
    border-color: gold;
}

.ranking-item.top-2 {
    background: linear-gradient(135deg, rgba(192,192,192,0.1), rgba(0,255,127,0.05));
    border-color: silver;
}

.ranking-item.top-3 {
    background: linear-gradient(135deg, rgba(205,127,50,0.1), rgba(0,255,127,0.05));
    border-color: #cd7f32;
}

.ranking-position {
    min-width: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 900;
    font-family: var(--font-secondary);
    position: relative;
}

.crown {
    font-size: 2rem;
    line-height: 1;
    margin-bottom: 5px;
    animation: crownFloat 2s ease-in-out infinite;
}

@keyframes crownFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.position-number {
    color: var(--green-primary);
    text-shadow: 0 0 10px var(--glow-color);
}

.ranking-content {
    flex: 1;
    display: flex;
    gap: 20px;
    align-items: center;
}

.ranking-user {
    min-width: 120px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}

.avatar-small {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid var(--green-primary);
    object-fit: cover;
}

.ranking-user .username {
    font-size: 0.9rem;
    color: var(--text-primary);
}

.ranking-details {
    flex: 1;
}

.ranking-title {
    margin-bottom: 5px;
}

.ranking-title a {
    color: var(--green-primary);
    text-decoration: none;
    font-size: 1.1rem;
}

.ranking-title a:hover {
    text-shadow: 0 0 10px var(--glow-color);
}

.ranking-description {
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.4;
}

.ranking-votes {
    min-width: 100px;
    text-align: center;
}

.votes-count {
    background: rgba(0,255,127,0.1);
    border: 1px solid var(--green-primary);
    border-radius: 20px;
    padding: 8px 15px;
}

.votes-count i {
    color: #ff4444;
    margin-right: 5px;
}

.votes-count .count {
    font-size: 1.2rem;
    font-weight: 900;
    color: var(--green-primary);
    margin-right: 5px;
}

.votes-count .label {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.empty-ranking {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-secondary);
    border: 2px dashed rgba(0,255,127,0.2);
    border-radius: 8px;
}

.empty-ranking i {
    color: rgba(0,255,127,0.2);
    margin-bottom: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
        gap: 10px;
    }
    
    .ranking-content {
        flex-direction: column;
        text-align: center;
    }
    
    .ranking-item {
        flex-direction: column;
        text-align: center;
    }
    
    .ranking-position {
        margin-bottom: 10px;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>