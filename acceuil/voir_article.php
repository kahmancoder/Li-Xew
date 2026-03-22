<?php
require_once '../db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: accueil.php');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT a.*, 
           c.nom AS categorie,
           u.nom AS auteur_nom, u.prenom AS auteur_prenom
    FROM article a
    LEFT JOIN categorie c ON a.categorie_id = c.id
    LEFT JOIN utilisateur u ON a.utilisateur_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header('Location: accueil.php');
    exit;
}

$stmtLies = $pdo->prepare("
    SELECT id, titre, description_courte, date
    FROM article
    WHERE categorie_id = ? AND id != ?
    ORDER BY date DESC
    LIMIT 3
");
$stmtLies->execute([$article['categorie_id'], $id]);
$articlesLies = $stmtLies->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT id, nom FROM categorie ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['titre']) ?> — Le Journal</title>
    <link rel="stylesheet" href="acceuil.css">
    <link rel="stylesheet" href="voir_article.css">
</head>
<body>

<header class="masthead">
    <div class="masthead-top">
        <span class="masthead-date"><?= date('l d F Y') ?></span>
        <span class="masthead-tagline">L'actualité au quotidien</span>
    </div>
    <div class="masthead-main">
        <a href="accueil.php"><h1>Le Journal</h1></a>
        <div class="masthead-rule"><span>◆</span></div>
    </div>
</header>

<nav class="navbar">
    <a href="accueil.php">Accueil</a>
    <?php foreach ($categories as $cat): ?>
        <a href="accueil.php?categorie=<?= $cat['id'] ?>"
           class="<?= ($article['categorie_id'] == $cat['id']) ? 'active' : '' ?>">
            <?= htmlspecialchars($cat['nom']) ?>
        </a>
    <?php endforeach; ?>
</nav>

<div class="breadcrumb">
    <div class="breadcrumb-inner">
        <a href="accueil.php">Accueil</a>
        <span class="breadcrumb-sep">›</span>
        <?php if ($article['categorie']): ?>
            <a href="accueil.php?categorie=<?= $article['categorie_id'] ?>">
                <?= htmlspecialchars($article['categorie']) ?>
            </a>
            <span class="breadcrumb-sep">›</span>
        <?php endif; ?>
        <span><?= htmlspecialchars(mb_strimwidth($article['titre'], 0, 55, '…')) ?></span>
    </div>
</div>

<div class="article-page">

    <main class="article-main">

        <header class="article-header">
            <?php if ($article['categorie']): ?>
                <a href="accueil.php?categorie=<?= $article['categorie_id'] ?>" class="article-badge">
                    <?= htmlspecialchars($article['categorie']) ?>
                </a>
            <?php endif; ?>
            <h1 class="article-title"><?= htmlspecialchars($article['titre']) ?></h1>
            <div class="article-byline">
                <span class="byline-author">
                    Par <?= htmlspecialchars($article['auteur_prenom'] . ' ' . $article['auteur_nom']) ?>
                </span>
                <span class="byline-dot"></span>
                <span class="byline-date"><?= date('d F Y', strtotime($article['date'])) ?></span>
            </div>
        </header>

        <?php if (!empty($article['image'])): ?>
        <figure class="article-figure">
            <img src="images/<?= htmlspecialchars($article['image']) ?>"
                 alt="<?= htmlspecialchars($article['titre']) ?>">
            <figcaption>
                <?= htmlspecialchars($article['categorie']) ?> — <?= date('d F Y', strtotime($article['date'])) ?>
            </figcaption>
        </figure>
        <?php endif; ?>

        <?php if (!empty($article['description_courte'])): ?>
        <p class="article-lead"><?= htmlspecialchars($article['description_courte']) ?></p>
        <?php endif; ?>

        <div class="article-rule"><span>◆</span></div>

        <div class="article-body">
            <?php
            $paragraphes = preg_split('/\n{2,}/', trim($article['contenu_complet']));
            foreach ($paragraphes as $p):
                if (!empty(trim($p))):
            ?>
                <p><?= nl2br(htmlspecialchars(trim($p))) ?></p>
            <?php
                endif;
            endforeach;
            ?>
        </div>

        <a href="accueil.php" class="btn-retour">← Retour aux articles</a>

    </main>

    <aside class="article-sidebar">

        <?php if (!empty($articlesLies)): ?>
        <div class="sidebar-block">
            <div class="sidebar-block-title">Dans la même catégorie</div>
            <?php foreach ($articlesLies as $lie): ?>
            <div class="sidebar-related">
                <h4><a href="voir.php?id=<?= $lie['id'] ?>"><?= htmlspecialchars($lie['titre']) ?></a></h4>
                <span class="sidebar-related-date"><?= date('d M Y', strtotime($lie['date'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="sidebar-block">
            <div class="sidebar-block-title">Catégories</div>
            <?php foreach ($categories as $cat): ?>
            <a href="accueil.php?categorie=<?= $cat['id'] ?>"
               class="sidebar-cat-link <?= ($article['categorie_id'] == $cat['id']) ? 'sidebar-cat-link--active' : '' ?>">
                <?= htmlspecialchars($cat['nom']) ?>
                <span>→</span>
            </a>
            <?php endforeach; ?>
        </div>

    </aside>

</div>

<footer>
    Le Journal &nbsp;·&nbsp; ESP Génie Informatique &nbsp;·&nbsp; Projet Final Backend 2026
</footer>

</body>
</html>
