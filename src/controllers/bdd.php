<?php
// Charger les informations sensibles depuis un fichier .env ou des variables d'environnement
require_once 'error_handler.php';

$host = getenv('DB_HOST') ?: 'localhost';  // Utilisation d'une valeur par défaut si la variable d'environnement n'est pas définie
$db = getenv('DB_NAME') ?: 'web_formation';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lancer une exception en cas d'erreur
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mode de récupération par défaut
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactiver les requêtes émulées
    //PDO::MYSQL_ATTR_LOCAL_INFILE => true,                   // Si tu as besoin d'importer des fichiers locaux
];

try {
    // Connexion à la base de données avec gestion des erreurs
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, $options);
    // Log de la connexion réussie (si nécessaire)
    // error_log('Connexion réussie à la base de données.');
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    ErrorHandler::logError("Erreur de connexion à la base de données", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    throw new Exception("Une erreur est survenue lors de la connexion à la base de données.");
}
?>
