<?php
$title = "Recherche d'utilisateurs - ChallengeHub";
$active_page = "search";

ob_start();
?>

<div class="search-page">
    <div class="search-header">
        <h1>Rechercher des profils</h1>
        
        <form action="index.php" method="GET" class="search-form">
            <input type="hidden" name="action" value="search">
            <div class="search-box">
                <input type="text" 
                       name="q" 
                       placeholder="Nom d'utilisateur..." 
                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                       autofocus>
                <button type="submit">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </div>
        </form>
    </div>
    
    <?php if(isset($_GET['q']) && !empty($_GET['q'])): ?>
        <div class="search-results">
            <h2>Résultats pour "<?= htmlspecialchars($_GET['q']) ?>"</h2>
            
            <?php if(empty($users)): ?>
                <div class="no-results">
                    <i class="fas fa-users fa-4x"></i>
                    <p>Aucun utilisateur trouvé</p>
                </div>
            <?php else: ?>
                <div class="users-grid">
                    <?php foreach($users as $user): ?>
                        <div class="user-card">
                            <div class="user-avatar">
                                <?php if(!empty($user['avatar'])): ?>
                                    <img src="public/<?= htmlspecialchars($user['avatar']) ?>" 
                                         alt="<?= htmlspecialchars($user['username']) ?>">
                                <?php else: ?>
                                    <div class="default-avatar">
                                        <i class="fas fa-user-astronaut"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="user-info">
                                <h3><?= htmlspecialchars($user['username']) ?></h3>
                                <?php if(!empty($user['bio'])): ?>
                                    <p><?= htmlspecialchars(substr($user['bio'], 0, 100)) ?>...</p>
                                <?php endif; ?>
                                <small>Membre depuis <?= date('d/m/Y', strtotime($user['created_at'])) ?></small>
                            </div>
                            
                            <a href="index.php?action=viewProfile&id=<?= $user['id'] ?>" class="btn-view">
                                Voir le profil
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.search-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.search-header {
    text-align: center;
    margin-bottom: 40px;
}

.search-header h1 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 20px;
}

.search-form {
    max-width: 500px;
    margin: 0 auto;
}

.search-box {
    display: flex;
    gap: 10px;
}

.search-box input {
    flex: 1;
    padding: 15px;
    border: 2px solid #e1e1e1;
    border-radius: 10px;
    font-size: 1rem;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
}

.search-box button {
    padding: 15px 30px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.search-box button:hover {
    background: #5a6fd8;
    transform: translateY(-2px);
}

.search-results h2 {
    color: #333;
    margin-bottom: 30px;
}

.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.user-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s;
}

.user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
}

.user-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 15px;
    border: 3px solid #667eea;
}

.user-avatar img,
.user-avatar .default-avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar .default-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
}

.user-info {
    margin-bottom: 15px;
}

.user-info h3 {
    color: #333;
    margin-bottom: 5px;
}

.user-info p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.user-info small {
    color: #999;
    font-size: 0.8rem;
}

.btn-view {
    padding: 10px 20px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
    width: 100%;
}

.btn-view:hover {
    background: #5a6fd8;
}

.no-results {
    text-align: center;
    padding: 60px;
    background: #f8f9fa;
    border-radius: 15px;
}

.no-results i {
    color: #ccc;
    margin-bottom: 20px;
}

.no-results p {
    color: #666;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>