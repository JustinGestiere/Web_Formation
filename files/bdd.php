<?php 
/**
 * Configuration de la connexion à la base de données
 */

try {
    // Paramètres de connexion
    $host = getenv('DB_HOST') ?: '127.0.0.1';  // Récupère l'hôte depuis les variables d'environnement, sinon utilise '127.0.0.1' comme valeur par défaut.
    $dbname = getenv('DB_NAME') ?: 'web_formation';  // Récupère le nom de la base de données depuis les variables d'environnement, sinon utilise 'web_formation' par défaut.
    $username = getenv('DB_USER') ?: 'root';  // Récupère le nom d'utilisateur de la base de données depuis les variables d'environnement, sinon utilise 'root' comme valeur par défaut.
    $password = getenv('DB_PASS') ?: 'AulrrpTCD7Tk2nJ55H4v';  // Récupère le mot de passe depuis les variables d'environnement, sinon utilise 'AulrrpTCD7Tk2nJ55H4v' par défaut.
    $charset = 'utf8mb4';  // Définir le jeu de caractères utilisé pour la connexion à la base de données. utf8mb4 est le choix recommandé pour une large compatibilité avec les caractères multilingues.

    // Options PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Active le mode de gestion des erreurs, qui lève une exception en cas d'erreur.
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Définit le mode par défaut de récupération des données comme étant un tableau associatif.
        PDO::ATTR_EMULATE_PREPARES => false,  // Désactive l'émulation des requêtes préparées, ce qui permet de bénéficier des requêtes préparées natives de MySQL pour plus de sécurité et de performance.
    ];

    // Création de la connexion
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password, $options);  // Crée une nouvelle instance de PDO pour se connecter à la base de données avec les paramètres définis plus haut.

} catch (PDOException $e) {
    // Afficher l'erreur complète
    die("Erreur de connexion : " . $e->getMessage());  // Si la connexion échoue, afficher un message d'erreur et arrêter l'exécution du script.
}

?>
