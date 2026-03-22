<?php
// accueil.php — Page d'accueil publique
// Personne 2 s'occupe de la logique articles, mais voici la structure complète

$page_title = "Accueil";
require_once 'entete.php';
require_once 'menu.php';
require_once 'db.php';

// Pagination
$par_page = 6;
$page_courante = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page_courante - 1) * $par_page;

// Filtre par catégorie (optionnel)
$categorie_filtre = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;

// Requête articles (avec filtre catégorie si demandé)
if ($categorie_filtre > 0) {
    $stmt = $pdo->prepare("
        SELECT a.*, c.nom AS categorie_nom, u.nom AS auteur_nom, u.prenom AS auteur_prenom
        FROM article a
        JOIN categorie c ON a.categorie_id = c.id
        JOIN utilisateur u ON a.utilisateur_id = u.id
        WHERE a.categorie_id = ?
        ORDER BY a.date DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$categorie_filtre, $par_page, $offset]);
} else {
    $stmt = $pdo->prepare("
        SELECT a.*, c.nom AS categorie_nom, u.nom AS auteur_nom, u.prenom AS auteur_prenom
        FROM article a
        JOIN categorie c ON a.categorie_id = c.id
        JOIN utilisateur u ON a.utilisateur_id = u.id
        ORDER BY a.date DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$par_page, $offset]);
}
$articles = $stmt->fetchAll();

// Nombre total d'articles pour la pagination
if ($categorie_filtre > 0) {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM article WHERE categorie_id = ?");
    $stmtCount->execute([$categorie_filtre]);
} else {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM article");
    $stmtCount->execute();
}
$total = $stmtCount->fetchColumn();
$total_pages = ceil($total / $par_page);

// Catégories pour le filtre
$stmtCats = $pdo->query("SELECT * FROM categorie ORDER BY nom");
$categories = $stmtCats->fetchAll();
?>

<main>
    <div class="page-title">Dernières actualités</div>
    <p class="page-subtitle">Retrouvez toute l'actualité en temps réel.</p>

    <!-- Filtre par catégorie -->
    <div style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="/accueil.php"
           class="btn btn-sm <?php echo $categorie_filtre === 0 ? 'btn-primary' : 'btn-secondary'; ?>">
            Toutes
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="/accueil.php?categorie=<?php echo $cat['id']; ?>"
               class="btn btn-sm <?php echo $categorie_filtre === (int)$cat['id'] ? 'btn-primary' : 'btn-secondary'; ?>">
                <?php echo htmlspecialchars($cat['nom']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Grille d'articles -->
    <?php if (empty($articles)): ?>
        <div class="alert alert-info">Aucun article disponible pour le moment.</div>
    <?php else: ?>
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
                <div class="article-card">
                    <div class="article-card-body">
                        <div class="article-card-title">
                            <a href="/articles/voir.php?id=<?php echo $article['id']; ?>">
                                <?php echo htmlspecialchars($article['titre']); ?>
                            </a>
                        </div>
                        <p class="article-card-desc">
                            <?php echo htmlspecialchars($article['description_courte'] ?? ''); ?>
                        </p>
                        <div class="article-card-meta">
                            <span class="article-category-badge">
                                <?php echo htmlspecialchars($article['categorie_nom']); ?>
                            </span>
                            <span>
                                <?php echo htmlspecialchars($article['auteur_prenom'] . ' ' . $article['auteur_nom']); ?>
                                &mdash;
                                <?php echo date('d/m/Y', strtotime($article['date'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page_courante > 1): ?>
                    <a href="?page=<?php echo $page_courante - 1; ?><?php echo $categorie_filtre ? '&categorie='.$categorie_filtre : ''; ?>">
                        &laquo; Précédent
                    </a>
                <?php else: ?>
                    <span class="disabled">&laquo; Précédent</span>
                <?php endif; ?>

                <span class="current">
                    Page <?php echo $page_courante; ?> / <?php echo $total_pages; ?>
                </span>

                <?php if ($page_courante < $total_pages): ?>
                    <a href="?page=<?php echo $page_courante + 1; ?><?php echo $categorie_filtre ? '&categorie='.$categorie_filtre : ''; ?>">
                        Suivant &raquo;
                    </a>
                <?php else: ?>
                    <span class="disabled">Suivant &raquo;</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once 'pied.php'; ?>