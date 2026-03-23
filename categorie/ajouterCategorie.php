<?php
    session_start();
    require_once "../db.php";

    function formulaire($a, $b) { ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une catégorie — Le Journal</title>
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
            <h1 class="page-title">Ajouter une catégorie</h1>
            <a href="listeCategorie.php" class="btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>

        <div class="table-card" style="padding: 36px 42px;">
            <p class="form-subtitle">Renseignez le nom de la nouvelle catégorie</p>

            <?php if ($b): ?>
                <div class="alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo $b; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="ajouterCategorie.php">

                <div class="form-group">
                    <label for="nom">Nom de la catégorie</label>
                    <input type="text" id="nom" name="nom"
                        placeholder="Veuillez saisir le nom de la catégorie"
                        value="<?php echo htmlspecialchars($a); ?>" required>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Ajouter
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

    if ($role == "visiteur"){
        echo "Veuillez creer un compte editeur";
        header("location: connexion.php");
        exit;
    }

    if (!isset($_POST['nom'])){
        formulaire("","");
    }else{
        $nom = $_POST['nom'];
        if(!$nom){
            die("Champs nom vide");
        }
        $sql = "SELECT id FROM categorie WHERE nom = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom]);

        if ($stmt->rowCount() > 0){
            formulaire("$nom", "Cette categorie existe deja");
            exit;
        }else{
            $sql = "INSERT into categorie(nom) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom]);
            header("location: listeCategorie.php");
        }
    }