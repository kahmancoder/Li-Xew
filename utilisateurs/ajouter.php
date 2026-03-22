<?php
// utilisateurs/ajouter.php — Ajouter un utilisateur (admin uniquement)
$page_title = "Ajouter un utilisateur";
require_once '../entete.php';
require_once '../menu.php';
require_once '../db.php';
// require_once '../style.css';
// Protection : admin uniquement
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../authentification/connexion.php');
    exit;
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom    = trim($_POST['nom_user']    ?? '');
    $prenom = trim($_POST['prenom']      ?? '');
    $login  = trim($_POST['login_user']  ?? '');
    $mdp    = trim($_POST['password_user'] ?? '');
    $role   = trim($_POST['role']        ?? '');

    // Validation serveur
    if (empty($nom))    $erreurs[] = "Le nom est obligatoire.";
    if (empty($prenom)) $erreurs[] = "Le prénom est obligatoire.";
    if (empty($login))  $erreurs[] = "Le login est obligatoire.";
    if (strlen($login) < 3) $erreurs[] = "Le login doit contenir au moins 3 caractères.";
    if (empty($mdp))    $erreurs[] = "Le mot de passe est obligatoire.";
    if (strlen($mdp) < 6)   $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères.";
    if (!in_array($role, ['visiteur', 'editeur', 'admin'])) $erreurs[] = "Le rôle est invalide.";

    // Vérifier login unique
    if (empty($erreurs)) {
        $check = $pdo->prepare("SELECT id FROM utilisateur WHERE login = ?");
        $check->execute([$login]);
        if ($check->fetch()) {
            $erreurs[] = "Ce login est déjà utilisé.";
        }
    }

    if (empty($erreurs)) {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, login, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $login, $hash, $role]);
        header('Location: liste.php?success=ajout');
        exit;
    }
}
?>

<main>
    <div class="page-title">Ajouter un utilisateur</div>
    <p class="page-subtitle"><a href="liste.php">← Retour à la liste</a></p>

    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $e): ?>
                <div><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form id="form-utilisateur" method="POST" action="ajouter.php">

            <div class="form-group">
                <label for="nom_user">Nom</label>
                <input type="text" id="nom_user" name="nom_user"
                       value="<?php echo htmlspecialchars($_POST['nom_user'] ?? ''); ?>"
                       maxlength="50">
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom"
                       value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="login_user">Login</label>
                <input type="text" id="login_user" name="login_user"
                       value="<?php echo htmlspecialchars($_POST['login_user'] ?? ''); ?>"
                       maxlength="255">
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="password_user">Mot de passe</label>
                <input type="password" id="password_user" name="password_user">
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <option value="">-- Choisir un rôle --</option>
                    <option value="visiteur" <?php echo (($_POST['role'] ?? '') === 'visiteur') ? 'selected' : ''; ?>>Visiteur</option>
                    <option value="editeur"  <?php echo (($_POST['role'] ?? '') === 'editeur')  ? 'selected' : ''; ?>>Éditeur</option>
                    <option value="admin"    <?php echo (($_POST['role'] ?? '') === 'admin')    ? 'selected' : ''; ?>>Administrateur</option>
                </select>
                <span class="error-msg"></span>
            </div>

            <div style="display:flex; gap:10px; margin-top:8px;">
                <button type="submit" class="btn btn-primary">Ajouter</button>
                <a href="liste.php" class="btn btn-secondary">Annuler</a>
            </div>

        </form>
    </div>
</main>

<?php require_once '../pied.php'; ?>