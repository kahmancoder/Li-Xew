<?php
    session_start();
    require_once "../db.php";
    
    if(!isset($_SESSION['id'])){
        header("location: connexion.php");
        exit;
    }
    $sql = "SELECT role FROM utilisateur where id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['id']]);

    $role = $stmt->fetchColumn();

    if ($role !== "admin"){
        die ("Seul l'admin peut supprimer une categorie");
    }
    if (!isset($_GET['nom'])){
        die ("Pas de parametre");
    }else{
        $nom = $_GET['nom'];
        if(!$nom){
            die ("Champs nom vide");
        }
        $sql = "SELECT id FROM categorie WHERE nom = ? ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom]);
     
        $categorie = $stmt->fetch();

        if (!$categorie){
            die("Categorie innexistante");
        }else{
            $sql = "DELETE from categorie where nom=? ";
            $stmt = $pdo->prepare($sql);
                    
            $stmt->execute([$nom]);
        }
        header("Location: listeCategorie.php");
        exit;
        
    }

