<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
$host     = "localhost";
$dbname   = "li_xew";
$user     = "root";
=======
$host = "localhost";
$dbname = "liXew";
$user = "root";
>>>>>>> dc5b61e1337044d118c66bac2ed3a5c1e6692fc1
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
<<<<<<< HEAD
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
=======
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}catch(PDOException $e){
    die("Erreur connexion : " . $e->getMessage());
>>>>>>> dc5b61e1337044d118c66bac2ed3a5c1e6692fc1
}