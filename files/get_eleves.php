<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

if (!isset($_GET['classe_id'])) {
    echo json_encode(['error' => 'ID de classe manquant']);
    exit();
}

try {
    require_once "bdd.php";
    
    $sql = "SELECT u.id, u.nom, u.prenoms
            FROM users u
            INNER JOIN eleves_classes ec ON u.id = ec.eleve_id
            WHERE ec.classe_id = ? AND u.roles = 'eleve'
            ORDER BY u.nom, u.prenoms";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['classe_id']]);
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
