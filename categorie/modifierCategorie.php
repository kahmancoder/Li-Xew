<?php
    session_start();
    require_once "../db.php";

    function formulaire($a, $b, $c, $d) { ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une catégorie — Le Journal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <div class="journal-header">
        <div class="journal-title">Le Journal</div>
        <div class="journal-ornament">— ◆ —</div>
    </div>

    <div class="page-wrapper">

        <div class="page-header">
            <h1 class="page-title">Modifier une catégorie</h1>
            <a href="listeCategorie.php" class="btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>

        <div class="table-card form-card">
            <p class="form-subtitle">Renseignez l'ancien et le nouveau nom de la catégorie</p>

            <form method="post" action="modifierCategorie.php">

                <div class="form-group">
                    <label for="nom">Ancien nom de la catégorie</label>
                    <input type="text" id="nom" name="nom"
                        placeholder="Nom de la catégorie à modifier"
                        value="<?php echo htmlspecialchars($a); ?>" required>
                    <?php if ($c): ?>
                        <div class="alert-danger">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?php echo $c; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="newNom">Nouveau nom de la catégorie</label>
                    <input type="text" id="newNom" name="newNom"
                        placeholder="Nouveau nom de la catégorie"
                        value="<?php echo htmlspecialchars($b); ?>" required>
                    <?php if ($d): ?>
                        <div class="alert-danger">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?php echo $d; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-pen-to-square"></i> Modifier
                </button>

            </form>
        </div>

        <div class="journal-footer">
            &copy; <?php echo date('Y'); ?> Le Journal — Administration
        </div>

    </div>

</body>
</html>
    <?php }

    if(!isset($_SESSION['id'])){
        header("location: ../authentification/connexion.php");
        exit;
    }

    $sql = "SELECT role FROM utilisateur where id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['id']]);

    $role = $stmt->fetchColumn();

    if ($role !== "admin"){
        die("Seul l'admin peut modifier");
    }

    if (!isset($_POST['nom'])){
        formulaire($_GET['nom'], "", "", "");
    }else{
        $nom = $_POST['nom'];
        $newNom = $_POST['newNom'];

        if(!$nom){
            die("Champs nom vide");
        }

        $sql = "SELECT id FROM categorie WHERE nom = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom]);
        $nomExiste = $stmt->rowCount();

        $sql = "SELECT id FROM categorie WHERE nom = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$newNom]);
        $newNomExiste = $stmt->rowCount();

        if ($nomExiste == 0){
            formulaire("$nom", "$newNom", "Cette categorie n'existe pas", "");
            exit;
        }else if($newNomExiste > 0){
            formulaire("$nom", "$newNom", "", "Cette categorie existe deja");
            exit;
        }else{
            $sql = "UPDATE categorie SET nom = ? WHERE nom = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$newNom, $nom]);
            header("location: listeCategorie.php");
        }
    }