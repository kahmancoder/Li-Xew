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

// ─── Fonction upload image ───
function uploadImage(): ?string {
    if (empty($_FILES['image']['name'])) return null;

    $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $exts_ok = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $exts_ok))              return 'FORMAT';
    if ($_FILES['image']['size'] > 5*1024*1024) return 'TAILLE';

    $dossier = '../images/';
    if (!is_dir($dossier)) mkdir($dossier, 0755, true);

    $nom = uniqid('img_') . '.' . $ext;
    return move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $nom) ? $nom : null;
}

// ═══════════════════════════════════════════════
// SUPPRESSION
// ═══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_supprimer'])) {
    $id_sup = (int)($_POST['id'] ?? 0);
    if ($id_sup > 0) {
        $s = $pdo->prepare("SELECT id, image FROM article WHERE id = ? AND utilisateur_id = ?");
        $s->execute([$id_sup, $editeur_id]);
        $art = $s->fetch();
        if ($art) {
            if ($art['image'] && file_exists('../images/' . $art['image'])) {
                unlink('../images/' . $art['image']);
            }
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
        'image'              => null,
    ];

    if (empty($valeurs['titre']))                                     $erreurs['titre']              = "Le titre est obligatoire.";
    elseif (strlen($valeurs['titre']) > 50)                           $erreurs['titre']              = "50 caractères maximum.";
    if (!empty($valeurs['description_courte']) && strlen($valeurs['description_courte']) > 300)
                                                                      $erreurs['description_courte'] = "300 caractères maximum.";
    if (empty($valeurs['contenu_complet']))                           $erreurs['contenu_complet']    = "Le contenu est obligatoire.";
    elseif (strlen($valeurs['contenu_complet']) < 20)                 $erreurs['contenu_complet']    = "20 caractères minimum.";
    if ($valeurs['categorie_id'] <= 0)                                $erreurs['categorie_id']       = "Sélectionnez une catégorie.";

    // ─── Gestion photo ───
    if (!empty($_FILES['image']['name'])) {
        $resultat = uploadImage();
        if ($resultat === 'FORMAT')     $erreurs['image'] = "Format non autorisé (jpg, png, gif, webp).";
        elseif ($resultat === 'TAILLE') $erreurs['image'] = "L'image ne doit pas dépasser 5 Mo.";
        elseif ($resultat === null)     $erreurs['image'] = "Erreur lors de l'upload.";
        else                            $valeurs['image'] = $resultat;
    }

    if (empty($erreurs)) {
        $pdo->prepare("
            INSERT INTO article (titre, description_courte, contenu_complet, image, categorie_id, utilisateur_id, date)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ")->execute([
            $valeurs['titre'],
            $valeurs['description_courte'],
            $valeurs['contenu_complet'],
            $valeurs['image'],
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
        'image'              => $_POST['image_actuelle'] ?? null,
    ];

    if (empty($valeurs['titre']))                                     $erreurs['titre']              = "Le titre est obligatoire.";
    elseif (strlen($valeurs['titre']) > 50)                           $erreurs['titre']              = "50 caractères maximum.";
    if (!empty($valeurs['description_courte']) && strlen($valeurs['description_courte']) > 300)
                                                                      $erreurs['description_courte'] = "300 caractères maximum.";
    if (empty($valeurs['contenu_complet']))                           $erreurs['contenu_complet']    = "Le contenu est obligatoire.";
    elseif (strlen($valeurs['contenu_complet']) < 20)                 $erreurs['contenu_complet']    = "20 caractères minimum.";
    if ($valeurs['categorie_id'] <= 0)                                $erreurs['categorie_id']       = "Sélectionnez une catégorie.";

    // ─── Gestion photo (nouvelle image optionnelle) ───
    if (!empty($_FILES['image']['name'])) {
        $resultat = uploadImage();
        if ($resultat === 'FORMAT')     $erreurs['image'] = "Format non autorisé (jpg, png, gif, webp).";
        elseif ($resultat === 'TAILLE') $erreurs['image'] = "L'image ne doit pas dépasser 5 Mo.";
        elseif ($resultat === null)     $erreurs['image'] = "Erreur lors de l'upload.";
        else {
            // Supprimer l'ancienne image du disque
            if ($valeurs['image'] && file_exists('../images/' . $valeurs['image'])) {
                unlink('../images/' . $valeurs['image']);
            }
            $valeurs['image'] = $resultat;
        }
    }

    if (empty($erreurs)) {
        $pdo->prepare("
            UPDATE article
            SET titre = ?, description_courte = ?, contenu_complet = ?, image = ?, categorie_id = ?
            WHERE id = ? AND utilisateur_id = ?
        ")->execute([
            $valeurs['titre'],
            $valeurs['description_courte'],
            $valeurs['contenu_complet'],
            $valeurs['image'],
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

$valeurs = $valeurs ?? [];
if ($action === 'modifier' && $id_edit > 0 && empty($erreurs)) {
    $s = $pdo->prepare("SELECT * FROM article WHERE id = ? AND utilisateur_id = ?");
    $s->execute([$id_edit, $editeur_id]);
    $article_edit = $s->fetch();
    if (!$article_edit) { header("Location: article.php"); exit; }
    $valeurs = $article_edit;
}

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

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$titres = [
    'liste'    => '✍️ Mes articles',
    'ajouter'  => '📝 Nouvel article',
    'modifier' => '✏️ Modifier l\'article',
];
?>
<!DOCTYPE html>
<html lang="fr">
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
        <?php if ($action !== 'liste'){ ?>
            <a href="article.php" class="btn-retour">← Mes articles</a>
        <?php } ?>
        <?php if ($action === 'liste'){ ?>
            <a href="article.php?action=ajouter" class="btn-editeur">+ Nouvel article</a>
        <?php } ?>
        <a href="../acceuil/acceuil.php" class="btn-retour">🏠 Accueil</a>
    </div>
</div>

<!-- Flash -->
<?php if ($flash){ ?>
    <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php } ?>

<!-- ══════════════════════════════════════════
     VUE : LISTE
══════════════════════════════════════════ -->
<?php if ($action === 'liste'){ ?>
    <?php if (empty($mes_articles)){ ?>
        <div class="empty-state">
            <p>Vous n'avez encore publié aucun article.</p>
            <a href="article.php?action=ajouter" class="btn-editeur" style="margin-top:1rem; display:inline-flex;">
                + Rédiger mon 1er article
            </a>
        </div>
    <?php } else { ?>
        <div class="editeur-table-wrap">
            <table class="editeur-table">
                <thead>
                    <tr>
                        <th>Photo</th>
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
                                <?php if ($art['image']): ?>
                                    <img src="../images/<?= htmlspecialchars($art['image']) ?>"
                                         alt="" class="table-thumb">
                                <?php else: ?>
                                    <span class="no-photo">—</span>
                                <?php endif; ?>
                            </td>
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
    <?php } ?>

<!-- ══════════════════════════════════════════
     VUE : FORMULAIRE (ajouter OU modifier)
══════════════════════════════════════════ -->
<?php } elseif ($action === 'ajouter' || $action === 'modifier'){ ?>

    <div class="form-wrap">

        <?php if (!empty($erreurs)){ ?>
            <div class="flash flash-erreur">Veuillez remplir tous les champs</div>
        <?php } ?>

        <form id="form-article" method="POST" action="article.php"
              enctype="multipart/form-data" novalidate>

            <?php if ($action === 'modifier'){ ?>
                <input type="hidden" name="action_modifier" value="1">
                <input type="hidden" name="id" value="<?= $id_edit ?>">
                <input type="hidden" name="image_actuelle" value="<?= htmlspecialchars($valeurs['image'] ?? '') ?>">
            <?php } else { ?>
                <input type="hidden" name="action_ajouter" value="1">
            <?php } ?>

            <!-- Titre -->
            <div class="form-group">
                <label for="titre">Titre <span class="requis">*</span></label>
                <input type="text" id="titre" name="titre"
                       value="<?= htmlspecialchars($valeurs['titre'] ?? '') ?>"
                       placeholder="Titre (50 car. max)">
                <span class="error-msg"></span>
                <?php if (!empty($erreurs['titre'])){ ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showError('titre','<?= addslashes($erreurs['titre']) ?>'));</script>
                <?php } ?>
            </div>

            <!-- Photo -->
            <div class="form-group">
                <label for="image">Photo <span class="optionnel"></span></label>

                <?php if (!empty($valeurs['image'])): ?>
                    <div class="img-actuelle">
                        <img src="../images/<?= htmlspecialchars($valeurs['image']) ?>"
                             alt="Image actuelle" class="img-preview">
                        <small class="hint">Image actuelle — choisissez un fichier pour la remplacer.</small>
                    </div>
                <?php endif; ?>

                <input type="file" id="image" name="image" accept="image/*">
                <small class="hint">jpg, png, gif, webp — 5 Mo max</small>
                <img id="preview-img" src="" alt="Aperçu" class="img-preview" style="display:none;">
                <span class="error-msg"></span>
                <?php if (!empty($erreurs['image'])){ ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showError('image','<?= addslashes($erreurs['image']) ?>'));</script>
                <?php } ?>
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
                <?php if (!empty($erreurs['categorie_id'])){ ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showError('categorie_id','<?= addslashes($erreurs['categorie_id']) ?>'));</script>
                <?php } ?>
            </div>

            <!-- Description courte -->
            <div class="form-group">
                <label for="description_courte">
                    Description courte <span class="optionnel">(300 car. max)</span>
                </label>
                <textarea id="description_courte" name="description_courte" rows="3"
                          placeholder="Résumé affiché sur la page d'accueil..."><?= htmlspecialchars($valeurs['description_courte'] ?? '') ?></textarea>
                <span class="error-msg"></span>
                <?php if (!empty($erreurs['description_courte'])){ ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showError('description_courte','<?= addslashes($erreurs['description_courte']) ?>'));</script>
                <?php } ?>
            </div>

            <!-- Contenu complet -->
            <div class="form-group">
                <label for="contenu_complet">Contenu complet <span class="requis">*</span></label>
                <textarea id="contenu_complet" name="contenu_complet" rows="10"
                          placeholder="Rédigez le contenu intégral..."><?= htmlspecialchars($valeurs['contenu_complet'] ?? '') ?></textarea>
                <span class="error-msg"></span>
                <?php if (!empty($erreurs['contenu_complet'])){ ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showError('contenu_complet','<?= addslashes($erreurs['contenu_complet']) ?>'));</script>
                <?php } ?>
            </div>

            <!-- Boutons -->
            <div class="form-actions">
                <?php if ($action === 'ajouter'){ ?>
                    <button type="submit" class="btn-editeur">📤 Publier l'article</button>
                <?php } else { ?>
                    <button type="submit" class="btn-editeur">💾 Enregistrer les modifications</button>
                <?php } ?>
                <a href="article.php" class="btn-retour">Annuler</a>
            </div>

        </form>
    </div>

<?php } ?>

</main>

<script>
document.getElementById('image')?.addEventListener('change', function () {
    const preview = document.getElementById('preview-img');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php require_once "../pied.php"; ?>
</body>
</html>