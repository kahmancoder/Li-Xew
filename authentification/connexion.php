<?php
require_once "../db.php"; // session_start() est déjà dans db.php

// Si déjà connecté → rediriger directement
if (isset($_SESSION['user_id'])) {
    header("Location: ../acceuil/acceuil.php");
    exit;
}

function formulaire($a, $c){ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Le Journal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="journal-header">
        <div class="journal-title">Le Journal</div>
        <div class="journal-ornament">— ◆ —</div>
    </div>
    <div class="form-card">
        <p class="form-title">Connexion</p>
        <p class="form-subtitle">Accédez à votre espace personnel</p>
        <?php if($c): ?>
            <div class="alert-danger"><?php echo htmlspecialchars($c); ?></div>
        <?php endif; ?>
        <form method="post" action="connexion.php">
            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" id="login" name="login"
                    placeholder="Veuillez saisir votre login"
                    value="<?php echo htmlspecialchars($a); ?>" required>
            </div>
            <div class="form-group">
                <label for="mdp">Mot de passe</label>
                <input type="password" id="mdp" name="mdp"
                    placeholder="Veuillez saisir votre mot de passe" required>
            </div>
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>
    </div>
    <div class="journal-footer">
        Pas encore de compte ? <a href="inscription.php">S'inscrire</a>
    </div>
</body>
</html>
<?php }

// ── Traitement du formulaire ──
if (!isset($_POST['login'])) {
    // Pas de soumission → afficher le formulaire (rôle visiteur par défaut)
    formulaire("", "");

} else {
    $login        = trim($_POST['login']  ?? '');
    $mot_de_passe = trim($_POST['mdp']    ?? '');

    if (!$login) {
        formulaire("", "Le champ login est requis.");
        exit;
    }
    if (!$mot_de_passe) {
        formulaire($login, "Le champ mot de passe est requis.");
        exit;
    }

    // Vérifier l'utilisateur en base
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($mot_de_passe, $user['password'])) {
        formulaire($login, "Login ou mot de passe incorrect.");
        exit;
    }

// ── Connexion réussie → remplir la session ──
session_start(); // Sécurité

$_SESSION['id'] = $user['id'];
$_SESSION['prenom']= $user['prenom'];
$_SESSION['nom']= $user['nom'];
$_SESSION['role']    = $user['role'];

// Redirection
header("Location: ../acceuil/acceuil.php");
exit;
}