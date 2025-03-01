<?php
/**
 * Configuration de la connexion à la base de données
 */

try {
    // Paramètres de connexion
    $host = getenv('DB_HOST') ?: 'DB_HOST=180.149.196.125';
    $dbname = getenv('DB_NAME') ?: 'web_formation';
    $username = getenv('DB_USER') ?: 'web_formation';
    $password = getenv('DB_PASS') ?: 'l2aIopUl3GqDBw5zYddF';
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
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die("Une erreur est survenue lors de la connexion à la base de données.");
}
?>
