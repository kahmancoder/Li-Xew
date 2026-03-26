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
            <h1>Inscription</h1>
        </div>

        <div class="form-wrap centered">

            <form method="post" action="inscription.php">

                <div class="form-group">
                    <label for="nom">Nom <span class="requis">*</span></label>
                    <input type="text" id="nom" name="nom"
                        placeholder="Veuillez saisir votre nom"
                        value="<?php echo htmlspecialchars($nom); ?>" required>
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom <span class="requis">*</span></label>
                    <input type="text" id="prenom" name="prenom"
                        placeholder="Veuillez saisir votre prénom"
                        value="<?php echo htmlspecialchars($prenom); ?>" required>
                </div>

                <div class="form-group">
                    <label for="login">Login <span class="requis">*</span></label>
                    <input type="text" id="login" name="login"
                        placeholder="Veuillez saisir votre login"
                        value="<?php echo htmlspecialchars($login); ?>" required>
                    <?php if($loginerror): ?>
                        <div class="flash flash-erreur" style="margin-top:8px;">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?php echo $loginerror; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe <span class="requis">*</span></label>
                    <input type="password" id="password" name="password"
                        placeholder="Veuillez saisir votre mot de passe" required>
                    <?php if($passworderror): ?>
                        <div class="flash flash-erreur" style="margin-top:8px;">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?php echo $passworderror; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirmer_mdp">Confirmer le mot de passe <span class="requis">*</span></label>
                    <input type="password" id="confirmer_mdp" name="confirmer_mdp"
                        placeholder="Confirmez votre mot de passe" required>
                    <?php if($confirmerror): ?>
                        <div class="flash flash-erreur" style="margin-top:8px;">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?php echo $confirmerror; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-editeur">
                        <i class="fa-solid fa-user-plus"></i> S'inscrire
                    </button>
                    <a href="connexion.php" class="btn-retour">
                        Déjà inscrit ? Se connecter
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
                }

                $sql = "SELECT id FROM utilisateur WHERE login = ?";
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

                $sql = "INSERT INTO utilisateur (nom,prenom, login, password) VALUES (?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $prenom, $login, $hash]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['id'] = $user['id'];
                $_SESSION['id']      = $user['id'];      // compatibilité avec les pages existantes
                $_SESSION['nom']     = $user['nom'];
                $_SESSION['prenom']  = $user['prenom'];
                $_SESSION['login']   = $user['login'];
                $_SESSION['role']    = $user['role'];
                header("location: ../acceuil/acceuil.php");
            }
            catch(PDOException $e){
                echo "Erreur : " . $e->getMessage();
            }
        }
    }