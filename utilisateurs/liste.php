<?php
// utilisateurs/liste.php — Liste de tous les utilisateurs (admin uniquement)
$page_title = "Gestion des utilisateurs";
require_once '../entete.php';
require_once '../menu.php';
require_once '../style.css';
require_once '../db.php';

// Protection : admin uniquement
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../authentification/connexion.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM utilisateur ORDER BY nom, prenom");
$utilisateurs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href=""
</head>
<body>
    

<main>
    <div class="page-title">Gestion des utilisateurs</div>
    <p class="page-subtitle">Liste de tous les comptes enregistrés.</p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php
            $msgs = [
                'ajout'      => 'Utilisateur ajouté avec succès.',
                'modif'      => 'Utilisateur modifié avec succès.',
                'suppression'=> 'Utilisateur supprimé avec succès.',
            ];
            echo htmlspecialchars($msgs[$_GET['success']] ?? 'Opération réussie.');
            ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 16px;">
        <a href="ajouter.php" class="btn btn-primary">+ Ajouter un utilisateur</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Login</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($utilisateurs)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#999;">Aucun utilisateur trouvé.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($utilisateurs as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['id']); ?></td>
                            <td><?php echo htmlspecialchars($u['nom']); ?></td>
                            <td><?php echo htmlspecialchars($u['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($u['login']); ?></td>
                            <td>
                                <span class="nav-role-badge role-<?php echo htmlspecialchars($u['role']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($u['role'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="modifier.php?id=<?php echo (int)$u['id']; ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                    <a href="supprimer.php?id=<?php echo (int)$u['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirmerSuppression('Supprimer cet utilisateur ?')">
                                        Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once '../pied.php'; ?>
</body>
</html>