<?php
require_once '../entete.php';
require_once '../menu.php';
require_once '../db.php';

$action     = $_GET['action'] ?? 'liste';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /Li-Xew/authentification/connexion.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM article ORDER BY date");
$articles = $stmt->fetchAll();
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
            <a href="article.php" class="btn-retour">← les articles</a>
        <?php } ?>
        <?php if ($action === 'liste'){ ?>
            <a href="article.php?action=ajouter" class="btn-editeur">+ Nouvel article</a>
        <?php } ?>
        <a href="../acceuil/acceuil.php" class="btn-retour">🏠 Accueil</a>
    </div>
</div>



<!-- ══════════════════════════════════════════
     VUE : LISTE
══════════════════════════════════════════ -->
<?php if ($action === 'liste'){ ?>
    <?php if (empty($articles)){ ?>
        <div class="empty-state">
            <p>Il y a aucun article.</p>
            <a href="article.php?action=ajouter" class="btn-editeur" style="margin-top:1rem; display:inline-flex;">
                + Rédiger le premier article
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
                    <?php foreach ($articles as $art): ?>
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
                            <td>
                                <span class="badge-cat">
                                    <?php
                                    $stmt = $pdo->prepare("SELECT nom FROM categorie WHERE id = ?");
                                    $stmt->execute([$art['categorie_id']]);
                                    $nomCategorie = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo htmlspecialchars($nomCategorie['nom']);
                                    ?>
                                </span>
                            </td>
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