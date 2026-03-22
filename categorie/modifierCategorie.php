<?php
    session_start();
    require_once "../db.php";
    function formulaire($a, $b, $c, $d){?>
        <form method = "post" action = "modifierCategorie.php">
                <label> Ancien nom de la categorie</label>
                <input type="text" placeholder="veuillez saisir le nom de la categorie que vous voulais modifier" name="nom" value= "<?php echo $a ?>" required>
        <br>
                <?php echo "<p style= 'color: red'>$c </p>"; ?>
        <br>
                <label> Nouveau nom de la categorie</label>
                <input type="text" placeholder="veuillez saisir nouveau le nom de la categorie" name="newNom" value= "<?php echo $b ?>" required>
        <br>
                
                <?php echo "<p style= 'color: red'>$d </p>"; ?>

                <button type= "submit">Modifier </button>

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
        ?><script> alert("Veuillez creer un compte editeur");</script>
        <?php header("location: connexion.php");
        exit;
    }
    if (!isset($_POST['nom'])){
        formulaire("","","", "");
    }else{
        $nom = $_POST['nom'];
        $newNom = $_POST['newNom'];
        if(!$nom){
            die ("Champs nom vide");
        }
        $sql = "SELECT id FROM categorie WHERE nom = ? ";
        $stmt = $pdo->prepare($sql);
                
        $stmt->execute([$nom]);
        $nomExiste=$stmt->rowCount();

        $sql = "SELECT id FROM categorie WHERE nom = ? ";
        $stmt = $pdo->prepare($sql);
                
        $stmt->execute([$newNom]);
        $newNomExiste=$stmt->rowCount();

        if ($nomExiste== 0){
            formulaire("$nom","$newNom","Cette categorie n'existe pas ","");
            exit;
        }else if($newNomExiste >0){
            formulaire("$nom","$newNom","","Cette categorie existe deja");
            exit;
        }else{
            $sql = "UPDATE categorie SET nom = ? WHERE nom = ?";
            $stmt = $pdo->prepare($sql);
                    
            $stmt->execute([$newNom]);
        }
        
    }

