<?php
// connexion.php — Authentification des utilisateurs
// La session est démarrée par db.php (via session_start protégé)
require_once "../db.php";

function formulaire($login_val = '', $erreur = '') { ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — ActuSite</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="../validation.js" defer></script>
</head>
<body>

    <header class="site-header">
        <div class="header-container">
            <div class="header-logo">
                <a href="../acceuil/acceuil.php">
                    <span class="logo-icon">📰</span>
                    <span class="logo-text">ActuSite</span>
                </a>
            </div>
            <p class="header-slogan">L'actualité en temps réel</p>
        </div>
    </header>

    <main>

        <div class="page-heading">
            <h1>Connexion</h1>
        </div>

        <div class="form-wrap centered">

            <?php if ($erreur): ?>
                <div class="flash flash-erreur">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo htmlspecialchars($erreur); ?>
                </div>
            <?php endif; ?>

            <form id="form-connexion" method="post" action="connexion.php">

                <div class="form-group">
                    <label for="login">Login <span class="requis">*</span></label>
                    <input type="text" id="login" name="login"
                        placeholder="Veuillez saisir votre login"
                        value="<?php echo htmlspecialchars($login_val); ?>">
                    <span class="error-msg"></span>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe <span class="requis">*</span></label>
                    <input type="password" id="password" name="mdp"
                        placeholder="Veuillez saisir votre mot de passe">
                    <span class="error-msg"></span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-editeur" onclick="return validerConnexion()">
                        <i class="fa-solid fa-right-to-bracket"></i> Se connecter
                    </button>
                    <a href="inscription.php" class="btn-retour">
                        Pas encore de compte ? S'inscrire
                    </a>
                </div>

            </form>
        </div>

    </main>

    <footer class="site-footer">
        &copy; <?php echo date('Y'); ?> ActuSite — <a href="../acceuil/acceuil.php">Accueil</a>
    </footer>

</body>
</html>
<?php }

// ── Traitement du formulaire ────────────────────────────────────────────────
if (!isset($_POST['login'])) {
    formulaire();
    exit;
}

$login       = trim($_POST['login']      ?? '');
$mot_de_passe = trim($_POST['mdp']       ?? '');

// Validation serveur basique
if (empty($login)) {
    formulaire($login, "Le champ login est requis.");
    exit;
}
if (empty($mot_de_passe)) {
    formulaire($login, "Le champ mot de passe est requis.");
    exit;
}

// Requête préparée — protection injection SQL
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE login = ?");
$stmt->execute([$login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($mot_de_passe, $user['password'])) {
    formulaire($login, "Identifiants incorrects, veuillez réessayer.");
    exit;
}

// ── Création de la session (déjà démarrée par db.php) ──────────────────────
$_SESSION['user_id'] = $user['id'];
$_SESSION['id']      = $user['id'];      // compatibilité avec les pages existantes
$_SESSION['nom']     = $user['nom'];
$_SESSION['prenom']  = $user['prenom'];
$_SESSION['login']   = $user['login'];
$_SESSION['role']    = $user['role'];

// Redirection selon le rôle
if ($user['role'] === 'admin') {
    header('Location:  ../admin/dashboard.php');
} else {
    header('Location: ../acceuil/acceuil.php');
}
exit;
