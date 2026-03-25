<?php
require_once '../db.php';
require_once '../entete.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /Li-Xew/acceuil/acceuil.php');
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
    header('Location: /Li-Xew/acceuil/acceuil.php');
    exit;
}

// Articles liés (même catégorie)
$stmtLies = $pdo->prepare("
    SELECT id, titre, description_courte, date, image
    FROM article
    WHERE categorie_id = ? AND id != ?
    ORDER BY date DESC
    LIMIT 4
");
$stmtLies->execute([$article['categorie_id'], $id]);
$articlesLies = $stmtLies->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT id, nom FROM categorie ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

$page_title = $article['titre'];
?>
<link rel="stylesheet" href="voir_article.css">
<?php require_once '../menu.php'; ?>

<!-- Fil d'ariane -->
<div class="breadcrumb-bar">
    <div class="breadcrumb-inner">
        <a href="/Li-Xew/acceuil/acceuil.php">🏠 Accueil</a>
        <span class="bc-sep">›</span>
        <?php if ($article['categorie']): ?>
            <a href="/Li-Xew/acceuil/acceuil.php?categorie=<?= $article['categorie_id'] ?>">
                <?= htmlspecialchars($article['categorie']) ?>
            </a>
            <span class="bc-sep">›</span>
        <?php endif; ?>
        <span><?= htmlspecialchars(mb_strimwidth($article['titre'], 0, 60, '…')) ?></span>
    </div>
</div>

<main class="article-wrapper">

    <!-- ══ BLOC HERO : image gauche + infos droite ══════════════════ -->
    <div class="article-hero">

        <!-- Image à gauche -->
        <div class="hero-image-col">
            <?php if (!empty($article['image'])): ?>
                <img src="/Li-Xew/images/<?= htmlspecialchars($article['image']) ?>"
                     alt="<?= htmlspecialchars($article['titre']) ?>"
                     class="hero-img">
            <?php else: ?>
                <div class="hero-img-placeholder">
                    <span>📰</span>
                    <p>Aucune image</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Infos à droite -->
        <div class="hero-info-col">

            <?php if ($article['categorie']): ?>
                <a href="/Li-Xew/acceuil/acceuil.php?categorie=<?= $article['categorie_id'] ?>"
                   class="article-badge">
                    <?= htmlspecialchars($article['categorie']) ?>
                </a>
            <?php endif; ?>

            <h1 class="article-title"><?= htmlspecialchars($article['titre']) ?></h1>

            <?php if (!empty($article['description_courte'])): ?>
                <p class="article-lead"><?= htmlspecialchars($article['description_courte']) ?></p>
            <?php endif; ?>

            <div class="article-meta">
                <div class="meta-item">
                    <span class="meta-icon">✍️</span>
                    <span>Par <strong><?= htmlspecialchars($article['auteur_prenom'] . ' ' . $article['auteur_nom']) ?></strong></span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">📅</span>
                    <span><?= date('d F Y', strtotime($article['date'])) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">🗂️</span>
                    <span><?= htmlspecialchars($article['categorie'] ?? 'Non classé') ?></span>
                </div>
            </div>

            <div class="hero-divider"><span>◆</span></div>

            <!-- Catégories rapides -->
            <div class="hero-cats">
                <?php foreach ($categories as $cat): ?>
                    <a href="/Li-Xew/acceuil/acceuil.php?categorie=<?= $cat['id'] ?>"
                       class="cat-pill <?= ($article['categorie_id'] == $cat['id']) ? 'cat-pill--active' : '' ?>">
                        <?= htmlspecialchars($cat['nom']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </div><!-- /.article-hero -->

    <!-- ══ CONTENU COMPLET ══════════════════════════════════════════ -->
    <div class="article-content-wrap">

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

        <a href="/Li-Xew/acceuil/acceuil.php" class="btn-retour">← Retour aux articles</a>

    </div>

    <!-- ══ ARTICLES LIÉS ════════════════════════════════════════════ -->
    <?php if (!empty($articlesLies)): ?>
    <div class="related-section">
        <h2 class="related-title">Dans la même catégorie</h2>
        <div class="related-grid">
            <?php foreach ($articlesLies as $lie): ?>
            <a href="/Li-Xew/acceuil/voir_article.php?id=<?= $lie['id'] ?>" class="related-card">
                <?php if (!empty($lie['image'])): ?>
                    <img src="/Li-Xew/images/<?= htmlspecialchars($lie['image']) ?>"
                         alt="<?= htmlspecialchars($lie['titre']) ?>" class="related-img">
                <?php else: ?>
                    <div class="related-img-placeholder">📰</div>
                <?php endif; ?>
                <div class="related-info">
                    <strong><?= htmlspecialchars($lie['titre']) ?></strong>
                    <span><?= date('d M Y', strtotime($lie['date'])) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</main>

<?php require_once '../pied.php'; ?>
