<?php
// utilisateurs/modifier.php — Modifier un utilisateur (admin uniquement)
$page_title = "Modifier un utilisateur";
require_once '../entete.php';
require_once '../menu.php';
require_once '../style.css';
require_once '../db.php';

// Protection : admin uniquement
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: liste.php');
    exit;
}

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id = ?");
$stmt->execute([$id]);
$utilisateur = $stmt->fetch();

if (!$utilisateur) {
    header('Location: liste.php');
    exit;
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom    = trim($_POST['nom_user']      ?? '');
    $prenom = trim($_POST['prenom']        ?? '');
    $login  = trim($_POST['login_user']    ?? '');
    $mdp    = trim($_POST['password_user'] ?? '');
    $role   = trim($_POST['role']          ?? '');

    // Validation serveur
    if (empty($nom))    $erreurs[] = "Le nom est obligatoire.";
    if (empty($prenom)) $erreurs[] = "Le prénom est obligatoire.";
    if (empty($login))  $erreurs[] = "Le login est obligatoire.";
    if (strlen($login) < 3) $erreurs[] = "Le login doit contenir au moins 3 caractères.";
    if (!empty($mdp) && strlen($mdp) < 6) $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères.";
    if (!in_array($role, ['visiteur', 'editeur', 'admin'])) $erreurs[] = "Le rôle est invalide.";

    // Vérifier login unique (sauf pour cet utilisateur)
    if (empty($erreurs)) {
        $check = $pdo->prepare("SELECT id FROM utilisateur WHERE login = ? AND id != ?");
        $check->execute([$login, $id]);
        if ($check->fetch()) {
            $erreurs[] = "Ce login est déjà utilisé par un autre utilisateur.";
        }
    }

    if (empty($erreurs)) {
        if (!empty($mdp)) {
            // Mise à jour avec nouveau mot de passe
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateur SET nom=?, prenom=?, login=?, password=?, role=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $login, $hash, $role, $id]);
        } else {
            // Mise à jour sans changer le mot de passe
            $stmt = $pdo->prepare("UPDATE utilisateur SET nom=?, prenom=?, login=?, role=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $login, $role, $id]);
        }
        header('Location: liste.php?success=modif');
        exit;
    }
}

// Pré-remplir avec les valeurs POST ou les données en BD
$vals = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [
    'nom_user'   => $utilisateur['nom'],
    'prenom'     => $utilisateur['prenom'],
    'login_user' => $utilisateur['login'],
    'role'       => $utilisateur['role'],
];
?>

<main>
    <div class="page-title">Modifier un utilisateur</div>
    <p class="page-subtitle"><a href="liste.php">← Retour à la liste</a></p>

    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $e): ?>
                <div><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form id="form-utilisateur" method="POST"
              action="modifier.php?id=<?php echo (int)$id; ?>"
              data-mode="modification">

            <div class="form-group">
                <label for="nom_user">Nom</label>
                <input type="text" id="nom_user" name="nom_user"
                       value="<?php echo htmlspecialchars($vals['nom_user'] ?? ''); ?>"
                       maxlength="50">
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom"
                       value="<?php echo htmlspecialchars($vals['prenom'] ?? ''); ?>">
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="login_user">Login</label>
                <input type="text" id="login_user" name="login_user"
                       value="<?php echo htmlspecialchars($vals['login_user'] ?? ''); ?>"
                       maxlength="255">
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="password_user">Nouveau mot de passe <small style="color:#999;">(laisser vide pour ne pas changer)</small></label>
                <input type="password" id="password_user" name="password_user">
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <option value="">-- Choisir un rôle --</option>
                    <option value="visiteur" <?php echo ($vals['role'] === 'visiteur') ? 'selected' : ''; ?>>Visiteur</option>
                    <option value="editeur"  <?php echo ($vals['role'] === 'editeur')  ? 'selected' : ''; ?>>Éditeur</option>
                    <option value="admin"    <?php echo ($vals['role'] === 'admin')    ? 'selected' : ''; ?>>Administrateur</option>
                </select>
                <span class="error-msg"></span>
            </div>

            <div style="display:flex; gap:10px; margin-top:8px;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="liste.php" class="btn btn-secondary">Annuler</a>
            </div>

        </form>
    </div>
</main>

<?php require_once '../pied.php'; ?>