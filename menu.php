<?php
$role = $_SESSION['role'] ?? 'visiteur'; // visiteur par défaut si non connecté
?>

<nav class="site-nav">
    <div class="nav-container">
        <ul class="nav-links">

            <!-- ── ACCESSIBLE À TOUS (visiteurs inclus) ── -->
            <li>
                <a href="/accueil.php" class="nav-link">Accueil</a>
            </li>
            <li>
                <a href="/categories/liste.php" class="nav-link">Catégories</a>
            </li>

            <!-- ── ÉDITEUR ET ADMIN UNIQUEMENT ── -->
            <?php if ($role === 'editeur' || $role === 'admin'): ?>
            <li class="nav-dropdown">
                <span class="nav-link nav-dropdown-toggle">Articles ▾</span>
                <ul class="dropdown-menu">
                    <li><a href="/articles/liste.php">Tous les articles</a></li>
                    <li><a href="/articles/ajouter.php">Ajouter un article</a></li>
                </ul>
            </li>
            <li class="nav-dropdown">
                <span class="nav-link nav-dropdown-toggle">Catégories ▾</span>
                <ul class="dropdown-menu">
                    <li><a href="/categories/liste.php">Voir les catégories</a></li>
                    <li><a href="/categories/ajouter.php">Ajouter une catégorie</a></li>
                </ul>
            </li>
            <?php endif; ?>

            <!-- ── ADMIN UNIQUEMENT ── -->
            <?php if ($role === 'admin'): ?>
            <li class="nav-dropdown">
                <span class="nav-link nav-dropdown-toggle">Utilisateurs ▾</span>
                <ul class="dropdown-menu">
                    <li><a href="/utilisateurs/liste.php">Tous les utilisateurs</a></li>
                    <li><a href="/utilisateurs/ajouter.php">Ajouter un utilisateur</a></li>
                </ul>
            </li>
            <?php endif; ?>

        </ul>

        <!-- Badge rôle affiché dans le menu -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="nav-role-badge role-<?php echo htmlspecialchars($role); ?>">
            <?php
            $labels = [
                'visiteur' => '👁 Visiteur',
                'editeur'  => '✏️ Éditeur',
                'admin'    => '⚙️ Admin',
            ];
            echo $labels[$role] ?? ucfirst(htmlspecialchars($role));
            ?>
        </div>
        <?php endif; ?>

    </div>
</nav>