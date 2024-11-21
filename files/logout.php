<?php
session_start(); // Démarrer la session

// Vérifier si la session existe avant de tenter de la détruire
if (isset($_SESSION['user_id'])) {
    // Détruire toutes les variables de session
    $_SESSION = array();

    // Détruire la session
    session_destroy();
    
    // Supprimer le cookie "email" s'il existe
    if (isset($_COOKIE['email'])) {
        // Supprimer le cookie avec les options de sécurité
        setcookie('email', '', time() - 3600, "/", "", isset($_SERVER["HTTPS"]), true); // HttpOnly et sécurisé
    }
    
    // Rediriger vers la page de connexion ou la page d'accueil
    header("Location: login.php");
    exit;
} else {
    // Si la session n'existe pas (utilisateur non authentifié)
    header("Location: login.php");
    exit;
}
?>
