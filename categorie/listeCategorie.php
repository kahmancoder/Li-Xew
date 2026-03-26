<?php   
    session_start();
    require_once "../db.php";
    $sql = "SELECT nom FROM categorie";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([]);

    $categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories — Le Journal</title>
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
            <h1 class="page-title">Liste des catégories</h1>
            <a href="ajouterCategorie.php" class="btn-primary">+ Ajouter</a>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $categorie): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($categorie['nom']); ?></td>
                            <td>
                                <a class="action-modifier"
                                   href="modifierCategorie.php?nom=<?php echo urlencode($categorie['nom']); ?>">
<i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <a class="action-supprimer"
                                   href="supprimerCategorie.php?nom=<?php echo urlencode($categorie['nom']); ?>">
                                   <i class="fa fa-trash" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="journal-footer">
            &copy; <?php echo date('Y'); ?> Le Journal — Administration
        </div>

    </div>

</body>
</html>