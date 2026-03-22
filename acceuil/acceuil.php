<?php
$page_title = "Accueil";
require_once "../db.php";
require_once "../entete.php";

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceuil</title>
    <link rel="stylesheet" href="acceuil.css">
</head>
<body>
    <main>
    <div class="page-heading">
        <h1>📰 Récents Articles</h1>
    </div>

    <div class="container">
        <?php foreach ($articles as $article): ?>
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
        <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">⬅️</a>
        <?php endif; ?>
        <span>Page <?= $page ?> / <?= $totalPage ?></span>
        <?php if ($page < $totalPage): ?>
            <a href="?page=<?= $page + 1 ?>">➡️</a>
        <?php endif; ?>
    </div>
</main>

<?php require_once "../pied.php"; ?>
</body>
</html>
