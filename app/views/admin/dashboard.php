<?php
$title = "Dashboard Admin - ChallengeHub";
$active_page = "admin";
ob_start();
?>

<div class="admin-wrap">

    <!-- ===== HEADER ADMIN ===== -->
    <div class="admin-header">
        <div class="admin-header-left">
            <div class="admin-icon"><i class="bi bi-shield-fill-check"></i></div>
            <div>
                <h1>Tableau de bord</h1>
                <p>Bienvenue, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> — Espace administrateur</p>
            </div>
        </div>
        <a href="index.php?action=home" class="btn-back">
            <i class="bi bi-arrow-left"></i> Retour au site
        </a>
    </div>

    <!-- ===== KPI CARDS ===== -->
    <div class="kpi-grid">
        <div class="kpi-card blue">
            <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-info">
                <span class="kpi-val"><?= $stats['total_users'] ?></span>
                <span class="kpi-lbl">Utilisateurs</span>
                <span class="kpi-sub">+<?= $stats['new_users_week'] ?> cette semaine</span>
            </div>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-icon"><i class="bi bi-trophy-fill"></i></div>
            <div class="kpi-info">
                <span class="kpi-val"><?= $stats['total_challenges'] ?></span>
                <span class="kpi-lbl">Défis</span>
                <span class="kpi-sub">+<?= $stats['new_challenges_week'] ?> cette semaine</span>
            </div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-icon"><i class="bi bi-send-fill"></i></div>
            <div class="kpi-info">
                <span class="kpi-val"><?= $stats['total_submissions'] ?></span>
                <span class="kpi-lbl">Participations</span>
            </div>
        </div>
        <div class="kpi-card orange">
            <div class="kpi-icon"><i class="bi bi-hand-thumbs-up-fill"></i></div>
            <div class="kpi-info">
                <span class="kpi-val"><?= $stats['total_votes'] ?></span>
                <span class="kpi-lbl">Votes</span>
            </div>
        </div>
        <div class="kpi-card pink">
            <div class="kpi-icon"><i class="bi bi-chat-fill"></i></div>
            <div class="kpi-info">
                <span class="kpi-val"><?= $stats['total_comments'] ?></span>
                <span class="kpi-lbl">Commentaires</span>
            </div>
        </div>
    </div>

    <!-- ===== GRAPHIQUES ===== -->
    <div class="charts-grid">

        <!-- Camembert catégories -->
        <div class="chart-card">
            <h3 class="chart-title"><i class="bi bi-pie-chart-fill"></i> Défis par catégorie</h3>
            <div class="chart-wrap">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Courbe activité -->
        <div class="chart-card chart-wide">
            <h3 class="chart-title"><i class="bi bi-graph-up-arrow"></i> Activité (30 derniers jours)</h3>
            <div class="chart-wrap">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

    </div>

    <!-- ===== ONGLETS GESTION ===== -->
    <div class="admin-tabs">
        <button class="atab-btn active" onclick="switchAdminTab('users', this)">
            <i class="bi bi-people-fill"></i> Utilisateurs (<?= count($allUsers) ?>)
        </button>
        <button class="atab-btn" onclick="switchAdminTab('challenges', this)">
            <i class="bi bi-trophy-fill"></i> Défis (<?= count($allChallenges) ?>)
        </button>
    </div>

    <!-- TAB : Utilisateurs -->
    <div id="atab-users" class="atab-pane active">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Défis</th>
                        <th>Participations</th>
                        <th>Inscrit le</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($allUsers as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td>
                            <div class="table-user">
                                <div class="table-avatar"><?= strtoupper(substr($u['username'], 0, 1)) ?></div>
                                <strong><?= htmlspecialchars($u['username']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge-num"><?= $u['nb_challenges'] ?></span></td>
                        <td><span class="badge-num"><?= $u['nb_submissions'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if($u['is_admin']): ?>
                                <span class="role-badge admin"><i class="bi bi-shield-fill-check"></i> Admin</span>
                            <?php else: ?>
                                <span class="role-badge user"><i class="bi bi-person-fill"></i> Membre</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <!-- Toggle admin -->
                                <form action="index.php?action=adminToggleAdmin" method="POST" style="display:inline">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="tbtn gray" title="<?= $u['is_admin'] ? 'Retirer admin' : 'Rendre admin' ?>">
                                        <i class="bi bi-shield-<?= $u['is_admin'] ? 'slash' : 'fill-check' ?>"></i>
                                    </button>
                                </form>
                                <!-- Supprimer -->
                                <?php if(!$u['is_admin']): ?>
                                <form action="index.php?action=adminDeleteUser" method="POST" style="display:inline"
                                      onsubmit="return confirm('Supprimer <?= htmlspecialchars($u['username']) ?> ?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="tbtn red"><i class="bi bi-trash3-fill"></i></button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB : Défis -->
    <div id="atab-challenges" class="atab-pane">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Créateur</th>
                        <th>Votes</th>
                        <th>Participations</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($allChallenges as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td>
                            <a href="index.php?action=showChallenge&id=<?= $c['id'] ?>" class="table-link">
                                <?= htmlspecialchars(substr($c['title'], 0, 40)) ?><?= strlen($c['title']) > 40 ? '...' : '' ?>
                            </a>
                        </td>
                        <td><span class="cat-badge"><?= htmlspecialchars($c['category']) ?></span></td>
                        <td><?= htmlspecialchars($c['creator']) ?></td>
                        <td><span class="badge-num"><?= $c['nb_likes'] ?></span></td>
                        <td><span class="badge-num"><?= $c['nb_submissions'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="index.php?action=showChallenge&id=<?= $c['id'] ?>" class="tbtn blue"><i class="bi bi-eye-fill"></i></a>
                                <form action="index.php?action=adminDeleteChallenge" method="POST" style="display:inline"
                                      onsubmit="return confirm('Supprimer ce défi ?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="challenge_id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="tbtn red"><i class="bi bi-trash3-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ===== STYLES ===== -->
<style>
.admin-wrap { max-width: 1200px; margin: 0 auto; }

/* Header */
.admin-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:16px; }
.admin-header-left { display:flex; align-items:center; gap:16px; }
.admin-icon { width:56px; height:56px; background:linear-gradient(135deg,#667eea,#764ba2); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:white; }
.admin-header h1 { font-size:1.6rem; font-weight:800; color:#222; margin:0 0 4px; }
.admin-header p { color:#777; font-size:0.9rem; margin:0; }
.btn-back { display:inline-flex; align-items:center; gap:8px; padding:10px 20px; background:#f0f0f0; color:#555; border-radius:10px; text-decoration:none; font-weight:600; transition:all 0.2s; }
.btn-back:hover { background:#e0e0e0; color:#333; }

/* KPI */
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:16px; margin-bottom:28px; }
.kpi-card { display:flex; align-items:center; gap:16px; padding:20px; border-radius:14px; color:white; box-shadow:0 4px 20px rgba(0,0,0,0.12); }
.kpi-card.blue   { background:linear-gradient(135deg,#667eea,#764ba2); }
.kpi-card.purple { background:linear-gradient(135deg,#a855f7,#7c3aed); }
.kpi-card.green  { background:linear-gradient(135deg,#10b981,#059669); }
.kpi-card.orange { background:linear-gradient(135deg,#f59e0b,#d97706); }
.kpi-card.pink   { background:linear-gradient(135deg,#ec4899,#db2777); }
.kpi-icon { font-size:1.8rem; opacity:0.85; }
.kpi-info { display:flex; flex-direction:column; }
.kpi-val { font-size:1.8rem; font-weight:800; line-height:1; }
.kpi-lbl { font-size:0.85rem; opacity:0.9; margin-top:2px; }
.kpi-sub { font-size:0.75rem; opacity:0.7; margin-top:4px; }

/* Charts */
.charts-grid { display:grid; grid-template-columns:1fr 2fr; gap:20px; margin-bottom:28px; }
.chart-card { background:white; border-radius:14px; padding:22px; box-shadow:0 4px 16px rgba(0,0,0,0.06); }
.chart-title { font-size:1rem; font-weight:700; color:#333; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.chart-title i { color:#667eea; }
.chart-wrap { position:relative; height:260px; }

/* Tabs */
.admin-tabs { display:flex; gap:8px; margin-bottom:20px; border-bottom:2px solid #e8e8e8; }
.atab-btn { padding:12px 22px; background:none; border:none; border-bottom:3px solid transparent; margin-bottom:-2px; color:#777; font-size:0.95rem; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px; border-radius:8px 8px 0 0; }
.atab-btn:hover { color:#667eea; background:#f5f5ff; }
.atab-btn.active { color:#667eea; border-bottom-color:#667eea; background:#f0eeff; }
.atab-pane { display:none; }
.atab-pane.active { display:block; }

/* Table */
.admin-table-wrap { background:white; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,0.06); overflow:hidden; }
.admin-table { width:100%; border-collapse:collapse; font-size:0.88rem; }
.admin-table thead { background:linear-gradient(135deg,#667eea,#764ba2); color:white; }
.admin-table th { padding:14px 16px; text-align:left; font-weight:600; white-space:nowrap; }
.admin-table td { padding:12px 16px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.admin-table tbody tr:hover { background:#fafbff; }
.admin-table tbody tr:last-child td { border-bottom:none; }

.table-user { display:flex; align-items:center; gap:10px; }
.table-avatar { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem; flex-shrink:0; }
.table-link { color:#667eea; text-decoration:none; font-weight:600; }
.table-link:hover { text-decoration:underline; }

.badge-num { background:#f0f2ff; color:#667eea; padding:3px 10px; border-radius:20px; font-weight:700; font-size:0.82rem; }
.role-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:0.78rem; font-weight:700; }
.role-badge.admin { background:#fef3c7; color:#d97706; }
.role-badge.user  { background:#f0f2ff; color:#667eea; }
.cat-badge { background:#f0f9ff; color:#0284c7; padding:3px 10px; border-radius:20px; font-size:0.78rem; font-weight:600; }

.table-actions { display:flex; gap:6px; }
.tbtn { width:32px; height:32px; border-radius:8px; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem; transition:all 0.2s; text-decoration:none; }
.tbtn.blue { background:#e8f0ff; color:#667eea; }
.tbtn.blue:hover { background:#667eea; color:white; }
.tbtn.gray { background:#f0f0f0; color:#555; }
.tbtn.gray:hover { background:#667eea; color:white; }
.tbtn.red  { background:#fee2e2; color:#ef4444; }
.tbtn.red:hover  { background:#ef4444; color:white; }

@media(max-width:900px) {
    .charts-grid { grid-template-columns:1fr; }
    .kpi-grid { grid-template-columns:repeat(2,1fr); }
    .admin-table-wrap { overflow-x:auto; }
}
@media(max-width:600px) {
    .kpi-grid { grid-template-columns:1fr; }
}
</style>

<!-- ===== JAVASCRIPT + CHART.JS ===== -->
<script src="public/js/chart.umd.min.js"></script>
<script>
function switchAdminTab(name, btn) {
    document.querySelectorAll('.atab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.atab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('atab-' + name).classList.add('active');
    btn.classList.add('active');
}

// ── Camembert : défis par catégorie ──────────────────────────
const categoryData = <?= json_encode($categoryData) ?>;
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: categoryData.map(d => d.category),
        datasets: [{
            data: categoryData.map(d => d.total),
            backgroundColor: [
                '#667eea','#764ba2','#10b981','#f59e0b',
                '#ec4899','#3b82f6','#ef4444','#8b5cf6'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 12 } } }
        }
    }
});

// ── Courbe : activité 30 jours ────────────────────────────────
const usersPerDay      = <?= json_encode($usersPerDay) ?>;
const challengesPerDay = <?= json_encode($challengesPerDay) ?>;

// Fusionner les dates
const allDays = [...new Set([
    ...usersPerDay.map(d => d.day),
    ...challengesPerDay.map(d => d.day)
])].sort();

const usersMap     = Object.fromEntries(usersPerDay.map(d => [d.day, parseInt(d.total)]));
const challengeMap = Object.fromEntries(challengesPerDay.map(d => [d.day, parseInt(d.total)]));

new Chart(document.getElementById('activityChart'), {
    type: 'line',
    data: {
        labels: allDays.map(d => {
            const dt = new Date(d);
            return dt.toLocaleDateString('fr-FR', { day:'2-digit', month:'short' });
        }),
        datasets: [
            {
                label: 'Inscriptions',
                data: allDays.map(d => usersMap[d] || 0),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102,126,234,0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#667eea'
            },
            {
                label: 'Défis créés',
                data: allDays.map(d => challengeMap[d] || 0),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#10b981'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>