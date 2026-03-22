<?php
    require_once "db.php";

    function formulaire($a, $c){ ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Document</title>
                <link rel="stylesheet" href="style.css">
            </head>
            <body>
                
            <form method = "post" action = "connexion.php">
                <label > login</label>
                <input type="text" placeholder="veuillez saisir votre login" name="login" value= "<?php echo $a ?>" required>
        <br>
                <label > Mot de passe</label>
                <input type="password" placeholder="veuillez saisir votre mot de passe" name="mdp" required>
                
                <?php echo "<p style= 'color: red'>$c </p>"; ?>

                <button type= "submit">Se connecter </button>

            </form>
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
        echo "connexion reussie";
    }
