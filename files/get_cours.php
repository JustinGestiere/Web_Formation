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
    
    $sql = "SELECT id, title, DATE_FORMAT(start_datetime, '%H:%i') as start_time 
            FROM emploi_du_temps 
            WHERE class_id = ? AND professeur_id = ?
            AND DATE(start_datetime) = CURRENT_DATE
            ORDER BY start_datetime";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['classe_id'], $_SESSION['user_id']]);
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
