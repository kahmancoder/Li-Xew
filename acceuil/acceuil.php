<?php
$page_title = "Accueil";
require_once "../db.php";
require_once "../entete.php";

// ─── Filtre catégorie ───
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;

// Récupérer toutes les catégories pour les boutons filtre
$cats = $pdo->query("SELECT * FROM categorie ORDER BY nom ASC")->fetchAll();

// ─── Pagination ───
$limit  = 6;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ─── Requête articles (avec ou sans filtre catégorie) ───
if ($categorie_id > 0) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM article WHERE categorie_id = ?");
    $countStmt->execute([$categorie_id]);
    $totalArticle = $countStmt->fetchColumn();

    $ouz = $pdo->prepare("
        SELECT article.*, categorie.nom as categorie
        FROM article
        LEFT JOIN categorie ON article.categorie_id = categorie.id
        WHERE article.categorie_id = :cat
        ORDER BY date DESC
        LIMIT :limit OFFSET :offset
    ");
    $ouz->bindValue(':cat',    $categorie_id, PDO::PARAM_INT);
    $ouz->bindValue(':limit',  $limit,        PDO::PARAM_INT);
    $ouz->bindValue(':offset', $offset,       PDO::PARAM_INT);
} else {
    $totalArticle = $pdo->query("SELECT COUNT(*) FROM article")->fetchColumn();

    $ouz = $pdo->prepare("
        SELECT article.*, categorie.nom as categorie
        FROM article
        LEFT JOIN categorie ON article.categorie_id = categorie.id
        ORDER BY date DESC
        LIMIT :limit OFFSET :offset
    ");
    $ouz->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $ouz->bindValue(':offset', $offset, PDO::PARAM_INT);
}

$ouz->execute();
$articles  = $ouz->fetchAll();
$totalPage = ceil($totalArticle / $limit);

// Helper pagination (garde le filtre dans l'URL)
function paginationUrl(int $p, int $cat): string {
    $params = ['page' => $p];
    if ($cat > 0) $params['categorie'] = $cat;
    return '?' . http_build_query($params);
}

// ─── Rôle de l'utilisateur connecté ───
$estConnecte = isset($_SESSION['id']);
$role        = $estConnecte ? $_SESSION['role'] : 'visiteur';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceuil</title>
    <link rel="stylesheet" href="acceuil.css">
</head>
<body>
    
<main>

    <!-- ══════════════════════════════════════════
         BANDEAU ÉDITEUR  (visible seulement si connecté)
    ══════════════════════════════════════════ -->
    <?php if ($estConnecte && (($role === 'editeur') || ($role === 'admin'))): ?>
        <div class="editeur-banner">
            <div class="editeur-banner-inner">
                <div class="editeur-banner-info">
                    <span class="editeur-icon">✍️</span>
                    <div>
                        <strong>Espace éditeur</strong>
                        <span>Bonjour, <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?> !</span>
                    </div>
                </div>
                <a href="../editeur/article.php" class="btn-editeur">
                    📄 Voir mes articles
                </a>
                <a href="../categorie/listeCategorie.php" class="btn-editeur">
                    📄 Categorie
                </a>
                <a href="../utilisateurs/liste.php" class="btn-editeur">
                    📄 Utilisateur
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════
         TITRE + FILTRE CATÉGORIES
    ══════════════════════════════════════════ -->
    <div class="page-heading">
        <h1>📰 Récents Articles</h1>
    </div>

    <div class="filter-bar">
        <a href="acceuil.php"
           class="filter-btn <?= $categorie_id === 0 ? 'active' : '' ?>">
            Tous
        </a>
        <?php foreach ($cats as $cat): ?>
            <a href="?categorie=<?= $cat['id'] ?>"
               class="filter-btn <?= $categorie_id === (int)$cat['id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['nom']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ══════════════════════════════════════════
         LISTE DES ARTICLES
    ══════════════════════════════════════════ -->
    <?php if (empty($articles)): ?>
        <div class="empty-state">
            <p>Aucun article dans cette catégorie pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="container">
            <?php foreach ($articles as $article): ?>
                <div class="card">
                    <img src="../images/<?= htmlspecialchars($article['image']) ?>" alt="image">
                    <div class="card-content">
                        <h2>
                            <a href="articles/voir.php?id=<?= $article['id'] ?>">
                                <?= htmlspecialchars($article['titre']) ?>
                            </a>
                        </h2>
                        <p><?= htmlspecialchars($article['description_courte']) ?></p>
                        <small>
                            <?= htmlspecialchars($article['categorie']) ?> |
                            <?= $article['date'] ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPage > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= paginationUrl($page - 1, $categorie_id) ?>">⬅️</a>
                <?php endif; ?>
                <span>Page <?= $page ?> / <?= $totalPage ?></span>
                <?php if ($page < $totalPage): ?>
                    <a href="<?= paginationUrl($page + 1, $categorie_id) ?>">➡️</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</main>

<?php require_once "../pied.php"; ?>
</body>
</html>