<?php
    session_start();
    require_once "../db.php";
    function formulaire($a, $b){?>
        <form method = "post" action = "ajouterCategorie.php">
                <label> Nom de la categorie</label>
                <input type="text" placeholder="veuillez saisir le nom de la categorie" name="nom" value= "<?php echo $a ?>" required>
        <br>

                
                <?php echo "<p style= 'color: red'>$b </p>"; ?>

                <button type= "submit">Ajouter </button>

        </form>       
    <?php;}
    if(!isset($_SESSION['id'])){
        header("location: connexion.php");
        exit;
    }
    $sql = "SELECT role FROM utilisateur where id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['id']]);

    $role = $stmt->fetchColumn();

    if ($role == "visiteur"){
        echo "Veuillez creer un compte editeur";
        header("location: connexion.php");
        exit;
    }
    if (!isset($_POST['nom'])){
        formulaire("","");
    }else{
        $nom = $_POST['nom'];
        if(!$nom){
            die ("Champs nom vide");
        }
        $sql = "SELECT id FROM categorie WHERE nom = ? ";
        $stmt = $pdo->prepare($sql);
                
        $stmt->execute([$nom]);
        if ($stmt->rowCount() > 0){
            formulaire("$nom","Cette categorie existe deja");
            exit;
        }else{
            $sql = "INSERT into categorie(nom) VALUES (?) ";
            $stmt = $pdo->prepare($sql);
                    
            $stmt->execute([$nom]);
        }
        
    }

