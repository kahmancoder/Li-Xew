<?php
// entete.php — Inclus en haut de toutes les pages
// Démarre la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<script src="/Li-Xew/validation.js" defer></script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' — ActuSite' : 'ActuSite'; ?></title>
    <link rel="stylesheet" href="/Li-Xew/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-container">

        <!-- Logo / Nom du site -->
        <div class="header-logo">
<<<<<<< HEAD
            <a href="/accueil.php">
                <h1><span class="logo-text">Li Xew</span></h1>
=======
            <a href="/Li-Xew/acceuil/acceuil.php">
                <span class="logo-text">ActuSite</span>
>>>>>>> fceb2be812824645a39ffab9723919fbcd5b5e0f
            </a>
        </div>

        <!-- Slogan -->
        <div class="header-slogan">
            L'actualité en temps réel
        </div>

        <!-- Infos utilisateur connecté -->
        <div class="header-user">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-badge role-<?php echo htmlspecialchars($_SESSION['role']); ?>">
                    <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?>
                    — <?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?>
                </span>
                <a href="/Li-Xew/authentification/deconnexion.php" class="btn btn-logout">Déconnexion</a>
            <?php else: ?>
                <a href="/Li-Xew/authentification/connexion.php" class="btn btn-login">Connexion</a>
            <?php endif; ?>
        </div>

    </div>
</header>