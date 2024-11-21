<?php
$host = 'localhost';
$db = 'web_formation';
$user = 'root';
$pass = '';  // Assure-toi que ce mot de passe est sécurisé pour la production
$charset = 'utf8mb4'; // Pour une gestion complète des caractères UTF-8

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lancer une exception en cas d'erreur
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mode de récupération par défaut
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactiver les requêtes émulées
];

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, $options);
    
    // Tester la connexion
    $pdo->query("SELECT 1");  // Simple query to test connection

    // Si la connexion est réussie, tu peux ajouter un message de débogage ici
    // echo "Connexion réussie !";
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Adresse e-mail utilisée comme expéditeur (SENDER_MAIL)
define('SENDER_MAIL', 'justin.gestiere@gmail.com'); // Adresse utilisée pour envoyer les e-mails
?>
