<?php

    $host = "localhost";
    $dbname = "liXew";
    $user = "root";
    $password = "";

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password
        );
    }catch(PDOException $e){
        echo ("erreur");
    }