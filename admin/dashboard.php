<?php
$page_title = "Tableau de bord";
require_once '../db.php';
require_once '../entete.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../authentification/connexion.php');
    exit;
}

// ── Statistiques ───────────────────────────────────────────────────────────
$stats['articles']     = $pdo->query("SELECT COUNT(*) FROM article")->fetchColumn();
$stats['categories']   = $pdo->query("SELECT COUNT(*) FROM categorie")->fetchColumn();
$stats['utilisateurs'] = $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
$stats['editeurs']     = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role='editeur'")->fetchColumn();
$stats['admins']       = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role='admin'")->fetchColumn();
$stats['visiteurs']    = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role='visiteur'")->fetchColumn();

// ── 5 derniers articles ────────────────────────────────────────────────────
$derniers_articles = $pdo->query(
    "SELECT a.id, a.titre, a.date, a.image, c.nom AS categorie,
            u.nom AS auteur_nom, u.prenom AS auteur_prenom
     FROM article a
     LEFT JOIN categorie c ON a.categorie_id = c.id
     LEFT JOIN utilisateur u ON a.utilisateur_id = u.id
     ORDER BY a.date DESC LIMIT 5"
)->fetchAll();

// ── Articles par catégorie ─────────────────────────────────────────────────
$articles_par_cat = $pdo->query(
    "SELECT c.nom, COUNT(a.id) AS total
     FROM categorie c
     LEFT JOIN article a ON a.categorie_id = c.id
     GROUP BY c.id, c.nom ORDER BY total DESC"
)->fetchAll();

// ── 5 derniers utilisateurs ────────────────────────────────────────────────
$derniers_users = $pdo->query(
    "SELECT id, nom, prenom, login, role FROM utilisateur ORDER BY id DESC LIMIT 5"
)->fetchAll();
?>

<?php require_once '../menu.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
<link rel="stylesheet" href="dashboard.css">

</head>
<body>
    <main class="dash-main">

    <!-- ── EN-TÊTE ──────────────────────────────────────────────── -->
    <div class="dash-top">
        <div>
            <h1 class="dash-title">Tableau de bord</h1>
            <p class="dash-sub">Bienvenue, <strong><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></strong> — Administration ActuSite</p>
        </div>
        <div class="dash-meta">
            <span class="dash-date"><?= date('d F Y') ?></span>
            <a href="../acceuil/acceuil.php" class="dash-btn-site">🌐 Voir le site</a>
        </div>
    </div>

    <!-- ── CARTES STATS ──────────────────────────────────────────── -->
    <div class="dash-stats">

        <div class="dstat-card">
            <div class="dstat-left">
                <span class="dstat-icon">📰</span>
            </div>
            <div class="dstat-right">
                <span class="dstat-num"><?= $stats['articles'] ?></span>
                <span class="dstat-label">Articles publiés</span>
                <a href="../editeur/article.php" class="dstat-link">Gérer →</a>
            </div>
        </div>

        <div class="dstat-card">
            <div class="dstat-left">
                <span class="dstat-icon">🗂️</span>
            </div>
            <div class="dstat-right">
                <span class="dstat-num"><?= $stats['categories'] ?></span>
                <span class="dstat-label">Catégories</span>
                <a href="../categorie/listeCategorie.php" class="dstat-link">Gérer →</a>
            </div>
        </div>

        <div class="dstat-card">
            <div class="dstat-left">
                <span class="dstat-icon">👥</span>
            </div>
            <div class="dstat-right">
                <span class="dstat-num"><?= $stats['utilisateurs'] ?></span>
                <span class="dstat-label">Utilisateurs</span>
                <a href="../utilisateurs/liste.php" class="dstat-link">Gérer →</a>
            </div>
        </div>

        <div class="dstat-card">
            <div class="dstat-left">
                <span class="dstat-icon">✏️</span>
            </div>
            <div class="dstat-right">
                <span class="dstat-num"><?= $stats['editeurs'] ?></span>
                <span class="dstat-label">Éditeurs</span>
                <a href="../utilisateurs/ajouter.php" class="dstat-link">Ajouter →</a>
            </div>
        </div>

    </div>

    <!-- ── CONTENU PRINCIPAL ─────────────────────────────────────── -->
    <div class="dash-grid">

        <!-- COLONNE GAUCHE -->
        <div class="dash-col">

            <!-- Derniers articles -->
            <div class="dash-card">
                <div class="dash-card-head">
                    <h2>📄 Derniers articles</h2>
                    <a href="../editeur/article.php?action=ajouter" class="dcard-btn dcard-btn--primary">+ Ajouter</a>
                </div>
                <?php if (empty($derniers_articles)): ?>
                    <p class="dash-empty">Aucun article pour l'instant.</p>
                <?php else: ?>
                <table class="dash-table">
                    <thead>
                        <tr><th>Titre</th><th>Catégorie</th><th>Auteur</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($derniers_articles as $art): ?>
                        <tr>
                            <td>
                                <a href="../acceuil/voir_article.php?id=<?= (int)$art['id'] ?>" class="dash-article-link">
                                    <?= htmlspecialchars($art['titre']) ?>
                                </a>
                            </td>
                            <td><span class="dash-badge"><?= htmlspecialchars($art['categorie'] ?? '—') ?></span></td>
                            <td><?= htmlspecialchars($art['auteur_prenom'] . ' ' . $art['auteur_nom']) ?></td>
                            <td><?= htmlspecialchars($art['date']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Articles par catégorie -->
            <div class="dash-card">
                <div class="dash-card-head">
                    <h2>📊 Articles par catégorie</h2>
                    <a href="../categorie/listeCategorie.php" class="dcard-btn">Gérer</a>
                </div>
                <?php if (empty($articles_par_cat)): ?>
                    <p class="dash-empty">Aucune catégorie.</p>
                <?php else: ?>
                <div class="dash-bars">
                    <?php
                    $max = max(array_column($articles_par_cat, 'total')) ?: 1;
                    foreach ($articles_par_cat as $cat):
                        $pct = round(($cat['total'] / $max) * 100);
                    ?>
                    <div class="dbar-row">
                        <span class="dbar-label"><?= htmlspecialchars($cat['nom']) ?></span>
                        <div class="dbar-track">
                            <div class="dbar-fill" style="width:<?= $pct ?>%"></div>
                        </div>
                        <span class="dbar-count"><?= (int)$cat['total'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- COLONNE DROITE -->
        <div class="dash-col">

            <!-- Répartition rôles -->
            <div class="dash-card">
                <div class="dash-card-head">
                    <h2>👤 Répartition des rôles</h2>
                    <a href="../utilisateurs/liste.php" class="dcard-btn">Voir tout</a>
                </div>
                <div class="dash-roles">
                    <div class="droles-list">
                        <div class="drole-item">
                            <span class="drole-dot drole-admin"></span>
                            <span class="drole-name">Administrateurs</span>
                            <span class="drole-n"><?= (int)$stats['admins'] ?></span>
                        </div>
                        <div class="drole-item">
                            <span class="drole-dot drole-editeur"></span>
                            <span class="drole-name">Éditeurs</span>
                            <span class="drole-n"><?= (int)$stats['editeurs'] ?></span>
                        </div>
                        <div class="drole-item">
                            <span class="drole-dot drole-visiteur"></span>
                            <span class="drole-name">Visiteurs</span>
                            <span class="drole-n"><?= (int)$stats['visiteurs'] ?></span>
                        </div>
                    </div>
                    <div class="droles-circle">
                        <span class="droles-total"><?= (int)$stats['utilisateurs'] ?></span>
                        <span class="droles-total-label">Total</span>
                    </div>
                </div>
            </div>

            <!-- Derniers utilisateurs -->
            <div class="dash-card">
                <div class="dash-card-head">
                    <h2>🆕 Derniers comptes</h2>
                    <a href="../utilisateurs/ajouter.php" class="dcard-btn dcard-btn--primary">+ Ajouter</a>
                </div>
                <ul class="dash-userlist">
                    <?php foreach ($derniers_users as $u): ?>
                    <li class="dash-user-item">
                        <div class="duser-avatar"><?= strtoupper(mb_substr($u['prenom'],0,1).mb_substr($u['nom'],0,1)) ?></div>
                        <div class="duser-info">
                            <strong><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?></strong>
                            <span>@<?= htmlspecialchars($u['login']) ?></span>
                        </div>
                        <span class="duser-role duser-role--<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
                        <div class="duser-actions">
                            <a href="../utilisateurs/modifier.php?id=<?= (int)$u['id'] ?>" class="daction-btn">✏️</a>
                            <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                            <a href="../utilisateurs/supprimer.php?id=<?= (int)$u['id'] ?>"
                               class="daction-btn daction-btn--del"
                               onclick="return confirm('Supprimer cet utilisateur ?')">🗑️</a>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Accès rapides -->
            <div class="dash-card">
                <div class="dash-card-head"><h2>⚡ Accès rapides</h2></div>
                <div class="dash-quick">
                    <a href="../utilisateurs/ajouter.php" class="dquick-btn">
                        <span>👤</span> Nouvel utilisateur
                    </a>
                    <a href="../categorie/ajouterCategorie.php" class="dquick-btn">
                        <span>🗂️</span> Nouvelle catégorie
                    </a>
                    <a href="../editeur/article.php?action=ajouter" class="dquick-btn">
                        <span>📰</span> Nouvel article
                    </a>
                    <a href="../utilisateurs/liste.php" class="dquick-btn dquick-btn--outline">
                        <span>👥</span> Tous les utilisateurs
                    </a>
                </div>
            </div>

        </div>
    </div>

</main>
</body>
</html>


<?php require_once '../pied.php'; ?>
