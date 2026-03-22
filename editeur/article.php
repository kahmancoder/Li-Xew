<?php
$page_title = "Mes articles";
require_once "../db.php";
require_once "../entete.php";

// ─── Accès réservé aux éditeurs ───
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editeur') {
    header("Location: ../connexion.php");
    exit;
}

$editeur_id = $_SESSION['user_id'];
$action     = $_GET['action'] ?? 'liste';
$id_edit    = (int)($_GET['id'] ?? 0);
$erreurs    = [];

// ═══════════════════════════════════════════════
// SUPPRESSION
// ═══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_supprimer'])) {
    $id_sup = (int)($_POST['id'] ?? 0);
    if ($id_sup > 0) {
        $s = $pdo->prepare("SELECT id FROM article WHERE id = ? AND utilisateur_id = ?");
        $s->execute([$id_sup, $editeur_id]);
        if ($s->fetch()) {
            $pdo->prepare("DELETE FROM article WHERE id = ? AND utilisateur_id = ?")
                ->execute([$id_sup, $editeur_id]);
            $_SESSION['flash'] = ['type' => 'succes', 'message' => 'Article supprimé avec succès.'];
        } else {
            $_SESSION['flash'] = ['type' => 'erreur', 'message' => 'Article introuvable ou accès refusé.'];
        }
    }
    header("Location: article.php");
    exit;
}

// ═══════════════════════════════════════════════
// AJOUT
// ═══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_ajouter'])) {
    $valeurs = [
        'titre'              => trim($_POST['titre']              ?? ''),
        'description_courte' => trim($_POST['description_courte'] ?? ''),
        'contenu_complet'    => trim($_POST['contenu_complet']    ?? ''),
        'categorie_id'       => (int)($_POST['categorie_id']      ?? 0),
    ];

    if (empty($valeurs['titre']))                 $erreurs['titre']              = "Le titre est obligatoire.";
    elseif (strlen($valeurs['titre']) > 50)        $erreurs['titre']              = "50 caractères maximum.";
    if (!empty($valeurs['description_courte']) && strlen($valeurs['description_courte']) > 300)
                                                   $erreurs['description_courte'] = "300 caractères maximum.";
    if (empty($valeurs['contenu_complet']))        $erreurs['contenu_complet']    = "Le contenu est obligatoire.";
    elseif (strlen($valeurs['contenu_complet']) < 20) $erreurs['contenu_complet'] = "20 caractères minimum.";
    if ($valeurs['categorie_id'] <= 0)             $erreurs['categorie_id']       = "Sélectionnez une catégorie.";

    if (empty($erreurs)) {
        $pdo->prepare("
            INSERT INTO article (titre, description_courte, contenu_complet, categorie_id, utilisateur_id, date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ")->execute([
            $valeurs['titre'],
            $valeurs['description_courte'],
            $valeurs['contenu_complet'],
            $valeurs['categorie_id'],
            $editeur_id
        ]);
        $_SESSION['flash'] = ['type' => 'succes', 'message' => 'Article publié avec succès !'];
        header("Location: article.php");
        exit;
    }
    $action = 'ajouter';
}

// ═══════════════════════════════════════════════
// MODIFICATION
// ═══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_modifier'])) {
    $id_mod  = (int)($_POST['id'] ?? 0);
    $valeurs = [
        'titre'              => trim($_POST['titre']              ?? ''),
        'description_courte' => trim($_POST['description_courte'] ?? ''),
        'contenu_complet'    => trim($_POST['contenu_complet']    ?? ''),
        'categorie_id'       => (int)($_POST['categorie_id']      ?? 0),
    ];

    if (empty($valeurs['titre']))                 $erreurs['titre']              = "Le titre est obligatoire.";
    elseif (strlen($valeurs['titre']) > 50)        $erreurs['titre']              = "50 caractères maximum.";
    if (!empty($valeurs['description_courte']) && strlen($valeurs['description_courte']) > 300)
                                                   $erreurs['description_courte'] = "300 caractères maximum.";
    if (empty($valeurs['contenu_complet']))        $erreurs['contenu_complet']    = "Le contenu est obligatoire.";
    elseif (strlen($valeurs['contenu_complet']) < 20) $erreurs['contenu_complet'] = "20 caractères minimum.";
    if ($valeurs['categorie_id'] <= 0)             $erreurs['categorie_id']       = "Sélectionnez une catégorie.";

    if (empty($erreurs)) {
        $pdo->prepare("
            UPDATE article
            SET titre = ?, description_courte = ?, contenu_complet = ?, categorie_id = ?
            WHERE id = ? AND utilisateur_id = ?
        ")->execute([
            $valeurs['titre'],
            $valeurs['description_courte'],
            $valeurs['contenu_complet'],
            $valeurs['categorie_id'],
            $id_mod,
            $editeur_id
        ]);
        $_SESSION['flash'] = ['type' => 'succes', 'message' => 'Article modifié avec succès !'];
        header("Location: article.php");
        exit;
    }
    $action  = 'modifier';
    $id_edit = $id_mod;
}

// ═══════════════════════════════════════════════
// DONNÉES POUR L'AFFICHAGE
// ═══════════════════════════════════════════════
$cats = $pdo->query("SELECT * FROM categorie ORDER BY nom ASC")->fetchAll();

// Pré-remplissage formulaire modification
$valeurs = $valeurs ?? [];
if ($action === 'modifier' && $id_edit > 0 && empty($erreurs)) {
    $s = $pdo->prepare("SELECT * FROM article WHERE id = ? AND utilisateur_id = ?");
    $s->execute([$id_edit, $editeur_id]);
    $article_edit = $s->fetch();
    if (!$article_edit) { header("Location: article.php"); exit; }
    $valeurs = $article_edit;
}

// Liste des articles de l'éditeur
$mes_articles = [];
if ($action === 'liste') {
    $s = $pdo->prepare("
        SELECT article.*, categorie.nom AS categorie
        FROM article
        LEFT JOIN categorie ON article.categorie_id = categorie.id
        WHERE article.utilisateur_id = ?
        ORDER BY date DESC
    ");
    $s->execute([$editeur_id]);
    $mes_articles = $s->fetchAll();
}

// Message flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$titres = [
    'liste'    => '✍️ Mes articles',
    'ajouter'  => '📝 Nouvel article',
    'modifier' => '✏️ Modifier l\'article',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Editeur</title>
    <link rel="stylesheet" href="../acceuil/acceuil.css">
</head>
<body>
    <main>

<!-- EN-TÊTE -->
<div class="page-heading" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <h1><?= $titres[$action] ?? 'Articles' ?></h1>
    <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
        <?php if ($action !== 'liste'){  ?>
            <a href="article.php" class="btn-retour">← Mes articles</a>
        <?php }; ?>
        <?php if ($action === 'liste'){  ?>
            <a href="article.php?action=ajouter" class="btn-editeur">+ Nouvel article</a>
        <?php }; ?>
        <a href="../acceuil/acceuil.php" class="btn-retour">🏠 Accueil</a>
    </div>
</div>

<!-- Flash -->
<?php if ($flash){  ?>
    <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php }; ?>


<!-- ══════════════════════════════════════════
     VUE : LISTE
══════════════════════════════════════════ -->
<?php if ($action === 'liste'){  ?>

    <?php if (empty($mes_articles)){ ?>
        <div class="empty-state">
            <p>Vous n'avez encore publié aucun article.</p>
            <a href="article.php?action=ajouter" class="btn-editeur" style="margin-top:1rem; display:inline-flex;">
                + Rédiger mon 1er article
            </a>
        </div>
    <?php }else{  ?>
        <div class="editeur-table-wrap">
            <table class="editeur-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mes_articles as $art): ?>
                        <tr>
                            <td>
                                <a href="../articles/voir.php?id=<?= $art['id'] ?>" class="article-link">
                                    <?= htmlspecialchars($art['titre']) ?>
                                </a>
                            </td>
                            <td><span class="badge-cat"><?= htmlspecialchars($art['categorie']) ?></span></td>
                            <td class="date-cell"><?= htmlspecialchars($art['date']) ?></td>
                            <td class="actions-cell">
                                <a href="article.php?action=modifier&id=<?= $art['id'] ?>" class="btn-action btn-edit">
                                    ✏️ Modifier
                                </a>
                                <form method="POST" action="article.php" style="display:inline;"
                                      onsubmit="return confirmerSuppression('Supprimer « <?= htmlspecialchars($art['titre'], ENT_QUOTES) ?> » ?')">
                                    <input type="hidden" name="id" value="<?= $art['id'] ?>">
                                    <input type="hidden" name="action_supprimer" value="1">
                                    <button type="submit" class="btn-action btn-delete">🗑️ Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php }
    } ?>


<!-- ══════════════════════════════════════════
     VUE : FORMULAIRE (ajouter OU modifier)
══════════════════════════════════════════ -->
<?php  if ($action === 'ajouter' || $action === 'modifier') {  ?>

    <div class="form-wrap">

        <?php if (!empty($erreurs)){  ?>
            <div class="flash flash-erreur">Veuillez remplir tous les champs !!</div>
        <?php }; ?>

        <form id="form-article" method="POST" action="article.php" novalidate>

            <?php if ($action === 'modifier'){  ?>
                <input type="hidden" name="action_modifier" value="1">
                <input type="hidden" name="id" value="<?= $id_edit ?>">
            <?php 
            }else{  ?>
                <input type="hidden" name="action_ajouter" value="1">
            <?php }; ?>

            <!-- Titre -->
            <div class="form-group">
                <label for="titre">Titre <span class="requis">*</span></label>
                <input type="text" id="titre" name="titre"
                       value="<?= htmlspecialchars($valeurs['titre'] ?? '') ?>"
                       placeholder="Titre (50 car. max)">
                <span class="error-msg"></span>
                <?php if (!empty($erreurs['titre'])){  ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showError('titre','<?= addslashes($erreurs['titre']) ?>'));</script>
                <?php }; ?>
            </div>

            <!-- Catégorie -->
            <div class="form-group">
                <label for="categorie_id">Catégorie <span class="requis">*</span></label>
                <select id="categorie_id" name="categorie_id">
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($cats as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= ($valeurs['categorie_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="error-msg"></span>
                <?php if (!empty($erreurs['categorie_id'])){  ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showError('categorie_id','<?= addslashes($erreurs['categorie_id']) ?>'));</script>
                <?php }; ?>
            </div>

            <!-- Description courte -->
            <div class="form-group">
                <label for="description_courte">
                    Description courte <span class="optionnel">(300 car. max)</span>
                </label>
                <textarea id="description_courte" name="description_courte" rows="3"
                          placeholder="Résumé affiché sur la page d'accueil..."><?= htmlspecialchars($valeurs['description_courte'] ?? '') ?></textarea>
                <span class="error-msg"></span>
                <?php if (!empty($erreurs['description_courte'])){  ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showError('description_courte','<?= addslashes($erreurs['description_courte']) ?>'));</script>
                <?php }; ?>
            </div>

            <!-- Contenu complet -->
            <div class="form-group">
                <label for="contenu_complet">Contenu complet <span class="requis">*</span></label>
                <textarea id="contenu_complet" name="contenu_complet" rows="10"
                          placeholder="Rédigez le contenu intégral..."><?= htmlspecialchars($valeurs['contenu_complet'] ?? '') ?></textarea>
                <span class="error-msg"></span>
                <?php if (!empty($erreurs['contenu_complet'])){  ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showError('contenu_complet','<?= addslashes($erreurs['contenu_complet']) ?>'));</script>
                <?php }; ?>
            </div>

            <!-- Boutons -->
            <div class="form-actions">
                <?php if ($action === 'ajouter'){  ?>
                    <button type="submit" class="btn-editeur">📤 Publier l'article</button>
                <?php }else{  ?>
                    <button type="submit" class="btn-editeur">💾 Enregistrer les modifications</button>
                <?php }; ?>
                <a href="article.php" class="btn-retour">Annuler</a>
            </div>

        </form>
    </div>

<?php }; ?>

</main>

<?php require_once "../pied.php"; ?>
</body>
</html>
