<?php

    $host = "localhost";
    $dbname = "li_xew";
    $user = "root";
    $password = "";

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password
        );
    }catch(PDOException $e){
        echo ("erreur");
    }