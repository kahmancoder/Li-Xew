<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
</body>
<?php 
    session_start();
    function formulaire($nom, $prenom, $login, $loginerror, $passworderror, $confirmerror){ ?>
            <form method = "post" action = "inscription.php">
                <label > Nom</label>
                <input type="text" placeholder="veuillez saisir votre nom" name="nom" value= "<?php echo $nom ?>" required>
        <br>
                <label > Prenom</label>
                <input type="text" placeholder="veuillez saisir votre prenom" name="prenom" value= "<?php echo $prenom ?>" required>
        <br>
                <label > login</label>
                <input type="text" placeholder="veuillez saisir votre login" name="login" value= "<?php echo $login ?>" required>
                <?php echo "<p style= 'color: red'>$loginerror </p>"; ?>
        <br>
                <label > Mot de passe</label>
                <input type="password" placeholder="veuillez saisir votre mot de passe" name="password" name="mdp" required>
                <?php echo "<p style= 'color: red'>$passworderror </p>"; ?>
        <br>
                <label > Confirmer mot de passe</label>
                <input type="password" placeholder="veuillez saisir votre mot de passe" name="confirmer_mdp" name="mdp" required>
                <?php echo "<p style= 'color: red'>$confirmerror </p>"; ?>
        <br>
                <label > Role</label>
                <select name="role" >
                    <option value="visiteur"> Visiteur</option>
                    <option value="editeur"> Editeur</option>
                </select>              
        <br>
                <button type= submit>S'inscrire </button>

            </form>
    <?php }
    if (!isset($_POST['nom'])){
        formulaire("","","", "", "", "");
    }else{

        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            try{
                require_once "db.php";

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
    ?></html>