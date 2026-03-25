<?php
// utilisateurs/supprimer.php — Supprimer un utilisateur (admin uniquement)
require_once '../entete.php';
require_once '../db.php';

// Protection : admin uniquement
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /Li-Xew/authentification/connexion.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: liste.php');
    exit;
}

// Empêcher l'admin de se supprimer lui-même
if ($id === (int)$_SESSION['id']) {
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
$stmt = $pdo->prepare("UPDATE article set utilisateur_id = 1 where utilisateur_id = ?");
$stmt->execute([$id]);
// Suppression
$stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
$stmt->execute([$id]);

header('Location: liste.php?success=suppression');
exit;
?>