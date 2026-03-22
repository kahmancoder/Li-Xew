<?php
    session_start();
    require_once "../db.php";
    function formulaire($a, $b){?>
        <form method = "post" action = "supprimerCategorie.php">
                <label> Nom de la categorie</label>
                <input type="text" placeholder="veuillez saisir le nom de la categorie" name="nom" value= "<?php echo $a ?>" required>
        <br>

                
                <?php echo "<p style= 'color: red'>$b </p>"; ?>

                <button type= "submit">Supprimer</button>

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
                
        $categorie = $stmt->fetch();

        if (!$categorie){
            formulaire("$nom","Cette categorie n'existe pas");
            exit;
        }else{
            $sql = "DELETE from categorie where nom=? ";
            $stmt = $pdo->prepare($sql);
                    
            $stmt->execute([$nom]);
        }
        header("Location: listerCategorie.php");
        exit;
        
    }

