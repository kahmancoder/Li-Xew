<?php 
    session_start();
    function formulaire($nom, $prenom, $login, $loginerror, $passworderror, $confirmerror){ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — Le Journal</title>
    <link rel="stylesheet" href="style.css">
 
</head>
<body>

    <div style="text-align:center; margin-bottom: 36px;">
        <div style="
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(2.4rem, 6vw, 3.6rem);
            font-weight: 900;
            background: linear-gradient(135deg, #e8d9a0, #c9a84c 40%, #f5e6b0 60%, #b8892a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
        ">Le Journal</div>
        <div style="color:#c9a84c; font-size:13px; letter-spacing:8px; margin-top:8px; opacity:0.8;">— ◆ —</div>
    </div>

    <div class="form-card">
        <p class="form-title">Inscription</p>
        <p class="form-subtitle">Créez votre compte pour accéder à l'espace rédaction</p>

        <form method="post" action="inscription.php">

            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom"
                    placeholder="Veuillez saisir votre nom"
                    value="<?php echo htmlspecialchars($nom); ?>" required>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom"
                    placeholder="Veuillez saisir votre prénom"
                    value="<?php echo htmlspecialchars($prenom); ?>" required>
            </div>

            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" id="login" name="login"
                    placeholder="Veuillez saisir votre login"
                    value="<?php echo htmlspecialchars($login); ?>" required>
                <?php if($loginerror): ?>
                    <div class="alert-danger" style="margin-top:8px; margin-bottom:0;"><?php echo $loginerror; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                    placeholder="Veuillez saisir votre mot de passe" required>
                <?php if($passworderror): ?>
                    <div class="alert-danger" style="margin-top:8px; margin-bottom:0;"><?php echo $passworderror; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirmer_mdp">Confirmer le mot de passe</label>
                <input type="password" id="confirmer_mdp" name="confirmer_mdp"
                    placeholder="Confirmez votre mot de passe" required>
                <?php if($confirmerror): ?>
                    <div class="alert-danger" style="margin-top:8px; margin-bottom:0;"><?php echo $confirmerror; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <option value="visiteur">Visiteur</option>
                    <option value="editeur">Éditeur</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">S'inscrire</button>

        </form>
    </div>

    <div class="journal-footer">
        Déjà inscrit ? <a href="connexion.php">Se connecter</a>
    </div>

</body>
</html>
    <?php }
    if (!isset($_POST['nom'])){
        formulaire("","","", "", "", "");
    }else{

        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            try{
                require_once "../db.php";

                $nom = $_POST['nom'];
                $prenom = $_POST['prenom'];
                $login = $_POST['login'];
                $mot_de_passe = $_POST['password'];
                $confirmer_mdp = $_POST['confirmer_mdp'];
                $role = $_POST['role'];

                if (!$nom){
                    exit("Champs Nom manquant");
                }elseif(!$prenom){
                    exit("Champs Prenom manquant");
                }elseif(!$login){
                    exit("Champs login manquant");
                }elseif(!$mot_de_passe){
                    exit("Champs mot de passe manquant");
                }elseif(!$confirmer_mdp){
                    exit("Champs confirmer mot de passe manquant");
                }elseif(!$role){
                    exit("Champs role manquant");
                }


                $sql = "SELECT id FROM utilisateur WHERE login = ? ";
                $stmt = $pdo->prepare($sql);
                
                $stmt->execute([$login]);

                if ($stmt->rowCount() > 0){
                        formulaire("$nom","$prenom", "$login", "Ce login est deja utilise","","");
                        exit;
                }

                if(strlen($mot_de_passe) < 6){
                    formulaire("$nom","$prenom", "$login", "","Le mot de passe doit contenir plus de 6 caracteres", "");
                    exit;
                }

                if ($mot_de_passe !== $confirmer_mdp){
                    formulaire("$nom","$prenom", "$login", "","", "Les mots de passe ne correspondent pas");
                    exit;
                }

                $hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

                $sql = "INSERT INTO utilisateur (nom,prenom, login, password, role) VALUES (?,?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $prenom, $login, $hash, $role]);

                echo("inscription reussie");
            }
            catch(PDOException $e){
                echo "Erreur : " . $e->getMessage();
            }
        }
    }