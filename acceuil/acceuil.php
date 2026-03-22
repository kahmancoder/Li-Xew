<?php
require_once "db.php";

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;


if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

$ouz = $pdo->prepare("
    SELECT article.*, categorie.nom as categorie
    FROM article
    LEFT JOIN categorie ON article.categorie_id = categorie.id
    ORDER BY date DESC
    LIMIT :limit OFFSET :offset
");

$ouz->bindValue(':limit', $limit, PDO::PARAM_INT);
$ouz->bindValue(':offset', $offset, PDO::PARAM_INT);
$ouz->execute();

$articles = $ouz->fetchAll();

$totalouz = $pdo->query("SELECT COUNT(*) FROM article");
$totalArticle = $totalouz->fetchColumn();
$totalPage = ceil($totalArticle / $limit);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Accueil</title>
    <link rel="stylesheet" href="acceuil.css">
</head>

<body>

<!-- BARRE DE NAVIGATION -->
<div class="navbar">
    <h1>📰 Récents Articles</h1>
    <div class="nav-links">
        <?php if (isset($_SESSION['utilisateur'])): ?>
            <span>Bonjour, <?= htmlspecialchars($_SESSION['utilisateur']['prenom']) ?></span>
            <a href="deconnexion.php" class="btn-deconnexion">Déconnexion</a>
        <?php else: ?>
            <a href="connexion.php" class="btn-connexion">Se connecter</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
<?php foreach ($articles as $article){ ?>

    <div class="card">

        <img src="images/<?= htmlspecialchars($article['image']) ?>" alt="image">
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

<?php } ?>

</div>

<!-- PAGINATION -->
<div class="pagination">
    <?php if ($page > 1){ ?>
        <a href="?page=<?= $page - 1 ?>">⬅️</a>
    <?php } ?>
    <span>Page <?= $page ?> / <?= $totalPage ?></span>
    <?php if ($page < $totalPage){ ?>
        <a href="?page=<?= $page + 1 ?>">➡️</a>
    <?php } ?>

</div>

</body>
</html>