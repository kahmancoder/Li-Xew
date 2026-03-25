<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$host = "localhost";
$dbname = "li_xew";
$user = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user, $password
    );


}catch(PDOException $e){
    die("Erreur connexion : " . $e->getMessage());
}