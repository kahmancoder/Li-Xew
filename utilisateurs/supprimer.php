<?php
// utilisateurs/supprimer.php — Supprimer un utilisateur (admin uniquement)
require_once '../entete.php';
require_once '../db.php';
require_once '../style.css';








//  qn,e; : admin uniquement
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: liste.php');
    exit;
}

// Empêcher l'admin de se supprimer lui-même
if ($id === (int)$_SESSION['user_id']) {
    header('Location: liste.php?erreur=auto_suppression');
    exit;
}

// Vérifier que l'utilisateur existe
$stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    header('Location: liste.php');
    exit;
}

// Suppression
$stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
$stmt->execute([$id]);

header('Location: liste.php?success=suppression');
exit;
?>