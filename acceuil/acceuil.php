<?php
$page_title = "Accueil";
require_once "../db.php";
require_once "../entete.php";

$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$recherche    = trim($_GET['q'] ?? '');
$cats         = $pdo->query("SELECT * FROM categorie ORDER BY nom ASC")->fetchAll();

$limit  = 6;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

if (!empty($recherche)) {
    $like = "%$recherche%";
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM article WHERE titre LIKE ? OR description_courte LIKE ? OR contenu_complet LIKE ?");
    $countStmt->execute([$like, $like, $like]);
    $totalArticle = $countStmt->fetchColumn();
    $ouz = $pdo->prepare("
        SELECT article.*, categorie.nom as categorie
        FROM article LEFT JOIN categorie ON article.categorie_id = categorie.id
        WHERE article.titre LIKE :q1 OR article.description_courte LIKE :q2 OR article.contenu_complet LIKE :q3
        ORDER BY date DESC LIMIT :limit OFFSET :offset
    ");
    $ouz->bindValue(':q1', $like); $ouz->bindValue(':q2', $like); $ouz->bindValue(':q3', $like);
    $ouz->bindValue(':limit', $limit, PDO::PARAM_INT);
    $ouz->bindValue(':offset', $offset, PDO::PARAM_INT);
} else if ($categorie_id > 0) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM article WHERE categorie_id = ?");
    $countStmt->execute([$categorie_id]);
    $totalArticle = $countStmt->fetchColumn();
    $ouz = $pdo->prepare("
        SELECT article.*, categorie.nom as categorie
        FROM article LEFT JOIN categorie ON article.categorie_id = categorie.id
        WHERE article.categorie_id = :cat
        ORDER BY date DESC LIMIT :limit OFFSET :offset
    ");
    $ouz->bindValue(':cat', $categorie_id, PDO::PARAM_INT);
    $ouz->bindValue(':limit', $limit, PDO::PARAM_INT);
    $ouz->bindValue(':offset', $offset, PDO::PARAM_INT);
} else {
    $totalArticle = $pdo->query("SELECT COUNT(*) FROM article")->fetchColumn();
    $ouz = $pdo->prepare("
        SELECT article.*, categorie.nom as categorie
        FROM article LEFT JOIN categorie ON article.categorie_id = categorie.id
        ORDER BY date DESC LIMIT :limit OFFSET :offset
    ");
    $ouz->bindValue(':limit', $limit, PDO::PARAM_INT);
    $ouz->bindValue(':offset', $offset, PDO::PARAM_INT);
}

$ouz->execute();
$articles  = $ouz->fetchAll();
$totalPage = ceil($totalArticle / $limit);

function paginationUrl(int $p, int $cat, string $q): string {
    $params = ['page' => $p];
    if ($cat > 0) $params['categorie'] = $cat;
    if ($q !== '') $params['q'] = $q;
    return '?' . http_build_query($params);
}

$estConnecte = isset($_SESSION['id']) || isset($_SESSION['id']);
$role        = $_SESSION['role'] ?? 'visiteur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil — ActuSite</title>
    <link rel="stylesheet" href="acceuil.css">
</head>
<body>

<?php if ($estConnecte && in_array($role, ['editeur', 'admin'])){  ?>
<div class="editeur-banner">
    <div class="editeur-banner-inner">
        <div class="editeur-banner-info">
            <span class="editeur-icon">✍️</span>
            <div>
                <strong>Espace <?= htmlspecialchars(ucfirst($role)) ?></strong>
                <span>Bonjour, <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?> !</span>
            </div>
        </div>
        <div class="editeur-banner-actions">
            <a href="../editeur/article.php" class="btn-editeur">📄 Articles</a>
            <a href="../categorie/listeCategorie.php" class="btn-editeur">🗂️ Catégories</a>
            <?php if ($role === 'admin'){  ?>
            <a href="../utilisateurs/liste.php" class="btn-editeur">👥 Utilisateurs</a>
            <a href="../admin/dashboard.php" class="btn-editeur btn-editeur--admin">⚙️ Dashboard</a>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>

<main>

    <div class="page-heading">
        <h1>📰 Récents Articles</h1>
    </div>

    <!-- FILTRES + RECHERCHE -->
    <div class="filter-search-bar">
        <div class="filter-bar">
            <a href="acceuil.php" class="filter-btn <?= ($categorie_id === 0 && $recherche === '') ? 'active' : '' ?>">Tous</a>
            <?php foreach ($cats as $cat){  ?>
                <a href="?categorie=<?= $cat['id'] ?>"
                   class="filter-btn <?= ($categorie_id === (int)$cat['id'] && $recherche === '') ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['nom']) ?>
                </a>
            <?php } ?>
        </div>
        <form class="search-form" method="GET" action="acceuil.php">
            <?php if ($categorie_id > 0){  ?><input type="hidden" name="categorie" value="<?= $categorie_id ?>"><?php } ?>
            <div class="search-input-wrap">
                <input type="text" name="q" class="search-input"
                       placeholder="Rechercher un article…"
                       value="<?= htmlspecialchars($recherche) ?>"
                       autocomplete="off">
                <button type="submit" class="search-btn">🔍</button>
                <?php if ($recherche !== ''){ ?>
                    <a href="acceuil.php" class="search-clear" title="Effacer">✕</a>
                <?php } ?>
            </div>
        </form>
    </div>

    <?php if ($recherche !== ''){  ?>
        <div class="search-result-info">
            <?= $totalArticle ?> résultat<?= $totalArticle > 1 ? 's' : '' ?> pour <strong>«&nbsp;<?= htmlspecialchars($recherche) ?>&nbsp;»</strong>
        </div>
    <?php } ?>

    <?php if (empty($articles)){  ?>
        <div class="empty-state">
            <?php if ($recherche !== ''){  ?>
                <p>Aucun article ne correspond à «&nbsp;<?= htmlspecialchars($recherche) ?>&nbsp;».</p>
                <a href="acceuil.php" class="btn-editeur" style="margin-top:1rem;display:inline-flex;">← Voir tous les articles</a>
            <?php }else{  ?>
                <p>Aucun article dans cette catégorie pour le moment.</p>
            <?php } ?>
        </div>
    <?php }else{  ?>
        <div class="container">
            <?php foreach ($articles as $article){  ?>
                <div class="card">
                    <div class="card-img-wrap">
                        <?php if (!empty($article['image'])){  ?>
                            <img src="../images/<?= htmlspecialchars($article['image']) ?>"
                                 alt="<?= htmlspecialchars($article['titre']) ?>">
                        <?php }else{  ?>
                            <div class="card-no-img">📰</div>
                        <?php }  ?>
                    </div>
                    <div class="card-content">
                        <h2>
                            <a href="voir_article.php?id=<?= $article['id'] ?>">
                                <?= htmlspecialchars($article['titre']) ?>
                            </a>
                        </h2>
                        <p><?= htmlspecialchars($article['description_courte']) ?></p>
                        <small>
                            <span><?= htmlspecialchars($article['categorie'] ?? '—') ?></span>
                            <span>·</span>
                            <span><?= htmlspecialchars($article['date']) ?></span>
                        </small>
                    </div>
                </div>
            <?php }; ?>
        </div>

        <?php if ($totalPage > 1){  ?>
            <div class="pagination">
                <?php if ($page > 1){  ?>
                    <a href="<?= paginationUrl($page - 1, $categorie_id, $recherche) ?>">⬅️</a>
                <?php } ?>
                <span>Page <?= $page ?> / <?= $totalPage ?></span>
                <?php if ($page < $totalPage){  ?>
                    <a href="<?= paginationUrl($page + 1, $categorie_id, $recherche) ?>">➡️</a>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } ?>

</main>

<?php require_once "../pied.php"; ?>
</body>
</html>
