<?php
/**
 * Configuration de la connexion à la base de données
 */

try {
    // Paramètres de connexion
    $host = getenv('DB_HOST') ?: '127.0.0.1'; // Correction de l'IP par défaut
    $dbname = getenv('DB_NAME') ?: 'web_formation';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: 'AulrrpTCD7Tk2nJ55H4v';
    $charset = 'utf8mb4';

    // Options PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Création de la connexion
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password, $options);

} catch (PDOException $e) {
    // Log l'erreur et arrête le script
    error_log("Erreur de connexion à la base de données : " . $e->getMessage(), 3, "/var/log/mon_application.log");
    die("Une erreur est survenue lors de la connexion à la base de données.");
}
?>
