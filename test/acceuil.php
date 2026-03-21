<?php
require_once 'db.php'; 

$articleParPage = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $articleParPage;

$categorieId = isset($_GET['categorie']) && is_numeric($_GET['categorie']) ? (int)$_GET['categorie'] : null;

if ($categorieId) {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM article WHERE categorie_id = ?");
    $stmtCount->execute([$categorieId]);
} else {
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM article");
}
$totalArticle = $stmtCount->fetchColumn();
$totalPages = ceil($totalArticle / $articleParPage);

if ($categorieId) {
    $stmt = $pdo->prepare("
        SELECT a.id, a.titre, a.description_courte, a.date_publication,
               c.nom AS categorie, u.prenom, u.nom AS auteur_nom
        FROM article a
        LEFT JOIN categories c ON a.categorie_id = c.id
        LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id
        WHERE a.categorie_id = ?
        ORDER BY a.date_publication DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$categorieId, $articleParPage, $offset]);
} else {
    $stmt = $pdo->prepare("
        SELECT a.id, a.titre, a.description_courte, a.date_publication, a.image,
               c.nom AS categorie, u.prenom, u.nom AS auteur_nom
        FROM article a
        LEFT JOIN categories c ON a.categorie_id = c.id
        LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id
        ORDER BY a.date_publication DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$articleParPage, $offset]);
}
$article = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

$categorieNom = '';
if ($categorieId) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categorieId) { $categorieNom = $cat['nom']; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Journal — Actualités</title>
    <link rel="stylesheet" href="acceuil.css">
   
 
</head>
<body>

<header class="masthead">
    <p class="masthead-date">
        <?php echo mb_strtoupper(strftime('%A %d %B %Y')); ?> &nbsp;·&nbsp; ESP Génie Informatique
    </p>
    <h1 class="masthead-title">Le Journal</h1>
    <div class="masthead-ornement">◆</div>
    <p class="masthead-tagline">L'actualité au quotidien</p>

    <nav class="nav-bar">
        <div class="nav-inner">
            <a href="accueil.php" class="nav-link <?php echo !$categorieId ? 'active' : ''; ?>">Accueil</a>
            <?php foreach ($categories as $cat): ?>
                <a href="accueil.php?categorie=<?php echo $cat['id']; ?>"
                   class="nav-link <?php echo ($categorieId == $cat['id']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['nom']); ?>
                </a>
            <?php endforeach; ?>
            <?php if (isset($_SESSION['utilisateur'])): ?>
                <?php include 'menu.php'; ?>
            <?php else: ?>
                <a href="connexion.php" class="nav-link">Connexion</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<div class="breadcrumb">
    <a href="accueil.php">Accueil</a>
    <?php if ($categorieNom): ?>
        <span>›</span>
        <span><?php echo htmlspecialchars($categorieNom); ?></span>
    <?php endif; ?>
</div>

<?php if (empty($article)): ?>
    <div class="no-article">Aucun article disponible pour le moment.</div>
<?php else: ?>

    <?php if ($page === 1 && !$categorieId): $hero = array_shift($article); ?>
    <section class="hero-section">
        <div class="section-heading"><h2>À la une</h2></div>
        <article class="hero-card">
            <div class="hero-image">
                <?php if (!empty($hero['image'])): ?>
                    <img src="<?php echo htmlspecialchars($hero['image']); ?>"
                         alt="<?php echo htmlspecialchars($hero['titre']); ?>">
                <?php else: ?>
                    <div class="hero-image-placeholder">◆</div>
                <?php endif; ?>
            </div>
            <div class="hero-content">
                <?php if (!empty($hero['categorie'])): ?>
                    <span class="article-cat">
                        <?php echo htmlspecialchars($hero['categorie']); ?>
                    </span>
                <?php endif; ?>
                <h3>
                    <a href="article/voir.php?id=<?php echo $hero['id']; ?>">
                        <?php echo htmlspecialchars($hero['titre']); ?>
                    </a>
                </h3>
                <p class="hero-desc"><?php echo htmlspecialchars($hero['description_courte']); ?></p>
                <div class="article-meta">
                    <?php echo date('d M Y', strtotime($hero['date_publication'])); ?>
                    <span class="dot">·</span>
                    Par <?php echo htmlspecialchars($hero['prenom'] . ' ' . $hero['auteur_nom']); ?>
                </div>
                <a href="article/voir.php?id=<?php echo $hero['id']; ?>" class="lire-plus">
                    Lire l'article
                </a>
            </div>
        </article>
    </section>
    <?php endif; ?>

    <section class="article-section">
        <?php if ($categorieId): ?>
            <div class="section-heading">
                <h2>Catégorie : <?php echo htmlspecialchars($categorieNom); ?></h2>
            </div>
        <?php elseif ($page > 1): ?>
            <div class="section-heading"><h2>Tous les article</h2></div>
        <?php else: ?>
            <div class="section-heading"><h2>Dernières nouvelles</h2></div>
        <?php endif; ?>

        <?php if (!empty($article)): ?>
        <div class="article-grid">
            <?php foreach ($article as $article): ?>
            <article class="article-card">
                <div class="card-image">
                    <?php if (!empty($article['image'])): ?>
                        <img src="<?php echo htmlspecialchars($article['image']); ?>"
                             alt="<?php echo htmlspecialchars($article['titre']); ?>">
                    <?php else: ?>
                        <div class="card-image-placeholder">◆</div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($article['categorie'])): ?>
                        <a href="accueil.php?categorie=<?php echo $article['categorie_id'] ?? ''; ?>">
                            <span class="article-cat"><?php echo htmlspecialchars($article['categorie']); ?></span>
                        </a>
                    <?php endif; ?>
                    <h3>
                        <a href="article/voir.php?id=<?php echo $article['id']; ?>">
                            <?php echo htmlspecialchars($article['titre']); ?>
                        </a>
                    </h3>
                    <p class="card-desc"><?php echo htmlspecialchars($article['description_courte']); ?></p>
                    <div class="card-meta">
                        <?php echo date('d M Y', strtotime($article['date_publication'])); ?>
                        &nbsp;·&nbsp;
                        Par <?php echo htmlspecialchars($article['prenom'] . ' ' . $article['auteur_nom']); ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <nav class="pagination">
            <?php
            $urlBase = 'accueil.php?' . ($categorieId ? "categorie=$categorieId&" : '');
            ?>
            <a href="<?php echo $urlBase; ?>page=<?php echo max(1, $page-1); ?>"
               class="btn-page" <?php if ($page <= 1) echo 'style="pointer-events:none;opacity:.3"'; ?>>
                ← Précédent
            </a>
            <span class="page-info">Page <?php echo $page; ?> / <?php echo max(1,$totalPages); ?></span>
            <a href="<?php echo $urlBase; ?>page=<?php echo min($totalPages, $page+1); ?>"
               class="btn-page" <?php if ($page >= $totalPages) echo 'style="pointer-events:none;opacity:.3"'; ?>>
                Suivant →
            </a>
        </nav>
    </section>

<?php endif; ?>

<footer>
    <div class="footer-logo">Le Journal</div>
    ESP — Département Génie Informatique &nbsp;·&nbsp; Projet Final Backend 2026
</footer>

</body>
</html>