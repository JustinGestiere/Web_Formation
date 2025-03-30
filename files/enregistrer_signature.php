<?php
session_start();
require_once "bdd.php";

// Vérification si l'utilisateur est un élève
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'eleve') {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['cours_id']) || !isset($_POST['signature_data'])) {
    $_SESSION['error'] = "Données manquantes";
    header("Location: signature_eleve.php");
    exit();
}

$eleve_id = $_SESSION['user_id'];
$cours_id = $_POST['cours_id'];
$signature_data = $_POST['signature_data'];

try {
    // Vérifier si le cours existe et si l'élève est dans la bonne classe
    $stmt = $pdo->prepare("SELECT c.*, s.id as signature_id 
                          FROM cours c 
                          LEFT JOIN sign s ON s.classe_id = c.class_id 
                          AND s.user_id = :eleve_id 
                          AND DATE(s.date_signature) = DATE(c.date_debut)
                          WHERE c.id = :cours_id");
    $stmt->execute([
        'cours_id' => $cours_id,
        'eleve_id' => $eleve_id
    ]);
    $cours = $stmt->fetch();

    if (!$cours) {
        throw new Exception("Cours non trouvé");
    }

    if ($cours['signature_id']) {
        throw new Exception("Vous avez déjà signé ce cours");
    }

    // Vérifier si le professeur a déjà initié la signature
    $stmt = $pdo->prepare("SELECT id FROM sign 
                          WHERE classe_id = :classe_id 
                          AND DATE(date_signature) = DATE(:date_cours)
                          LIMIT 1");
    $stmt->execute([
        'classe_id' => $cours['class_id'],
        'date_cours' => $cours['date_debut']
    ]);
    
    if (!$stmt->fetch()) {
        throw new Exception("La signature n'est pas encore disponible pour ce cours");
    }

    // Enregistrer la signature
    $stmt = $pdo->prepare("INSERT INTO sign (user_id, professeur_id, classe_id, signature, signed, date_signature) 
                          VALUES (:user_id, :professeur_id, :classe_id, :signature, 1, NOW())");
    
    $stmt->execute([
        'user_id' => $eleve_id,
        'professeur_id' => $cours['professeur_id'],
        'classe_id' => $cours['class_id'],
        'signature' => $signature_data
    ]);

    $_SESSION['success'] = "Votre signature a été enregistrée avec succès";

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: signature_eleve.php");
exit();
?>
