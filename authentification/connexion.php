<?php
    require_once "../db.php";

    function formulaire($a, $c){ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Le Journal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <header class="site-header">
        <div class="header-container">
            <div class="header-logo">
                <a href="../index.php">
                    <span class="logo-icon">📰</span>
                    <span class="logo-text">Le Journal</span>
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

            <?php if($c): ?>
                <div class="flash flash-erreur">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo $c; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="connexion.php">

                <div class="form-group">
                    <label for="login">Login <span class="requis">*</span></label>
                    <input type="text" id="login" name="login"
                        placeholder="Veuillez saisir votre login"
                        value="<?php echo htmlspecialchars($a); ?>" required>
                </div>

                <div class="form-group">
                    <label for="mdp">Mot de passe <span class="requis">*</span></label>
                    <input type="password" id="mdp" name="mdp"
                        placeholder="Veuillez saisir votre mot de passe" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-editeur">
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
        &copy; <?php echo date('Y'); ?> Le Journal — <a href="../acceuil/acceuil.php">Accueil</a>
    </footer>

</body>
</html>
    <?php }

    if (!isset($_POST["login"])){
        formulaire("", "");
    }else{

        $login = $_POST['login'];
        $mot_de_passe = $_POST['mdp'];

        if(!$login){
            exit("Le champs login est requis");
        }
        if(!$mot_de_passe){
            exit("Le champs mot de passe est requis");
        }

        $sql = "SELECT * FROM utilisateur WHERE login = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user || !password_verify($mot_de_passe, $user['password'])){
            formulaire("$login", "Identifiants incorrects, veuillez réessayer.");
            exit;
        }

        session_start();
        $_SESSION['id'] = $user['id'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['role'] = $user['role'];
        header("location: ../acceuil/acceuil.php");
    }