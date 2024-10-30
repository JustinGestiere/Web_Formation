<?php
session_start(); // Démarrer la session

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Supprimer le cookie "email" s'il existe
if (isset($_COOKIE['email'])) {
    setcookie('email', '', time() - 3600, "/"); // Supprime le cookie
}

// Rediriger vers la page d'accueil ou de connexion
header("Location: login.php");
exit;
?>

