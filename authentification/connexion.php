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
            <div class="alert-danger"><?php echo $c; ?></div>
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
            formulaire("$login", "informations incorrectes ");
            exit;
        }

        session_start();

        $_SESSION['id'] = $user['id'];
        header("location: ../acceuil/acceuil.php");
    }