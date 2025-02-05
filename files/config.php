<?php
/**
 * Configuration principale de l'application Web Formation
 * 
 * Ce fichier contient les paramètres de base de données et les constantes globales
 * utilisées dans toute l'application.
 * 
 * @version 1.0
 * @author Justin GESTIERE
 */

// Paramètres de connexion à la base de données
$host = 'localhost';      // Hôte de la base de données
$db = 'web_formation';    // Nom de la base de données
$user = 'root';          // Nom d'utilisateur MySQL
$pass = '';              // Mot de passe MySQL (à sécuriser en production)
$charset = 'utf8mb4';    // Support complet des caractères UTF-8

// Options PDO pour la sécurité et la gestion des erreurs
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Création de la connexion PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, $options);
    
    // Test simple de la connexion
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    // Log l'erreur et affiche un message utilisateur
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die("Une erreur est survenue lors de la connexion à la base de données.");
}

// Configuration des emails
define('SENDER_MAIL', 'justin.gestiere@gmail.com');

?>
