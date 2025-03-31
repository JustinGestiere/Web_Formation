<?php
session_start();
require_once "bdd.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'eleve') {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['signature_data']) || empty($_POST['cours_id'])) {
    $_SESSION['error'] = "Données de signature manquantes";
    header("Location: signature_eleve.php");
    exit();
}

$eleve_id = $_SESSION['user_id'];
$cours_id = $_POST['cours_id'];
$signature_data = $_POST['signature_data'];

try {
    $pdo->beginTransaction();

    // Vérifier si le cours existe et récupérer les informations nécessaires
    $stmt = $pdo->prepare("SELECT c.*, p.id as prof_id, cl.id as classe_id 
                          FROM cours c
                          INNER JOIN users p ON c.professeur_id = p.id
                          INNER JOIN classes cl ON c.class_id = cl.id
                          WHERE c.id = :cours_id");
    $stmt->execute(['cours_id' => $cours_id]);
    $cours = $stmt->fetch();

    if (!$cours) {
        throw new Exception("Cours non trouvé");
    }

    // Vérifier si l'élève n'a pas déjà signé
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sign 
                          WHERE cours_id = :cours_id 
                          AND user_id = :user_id");
    $stmt->execute([
        'cours_id' => $cours_id,
        'user_id' => $eleve_id
    ]);
    
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Vous avez déjà signé ce cours");
    }

    // Vérifier si le professeur a initié la signature
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sign 
                          WHERE cours_id = :cours_id 
                          AND professeur_id = :prof_id");
    $stmt->execute([
        'cours_id' => $cours_id,
        'prof_id' => $cours['prof_id']
    ]);
    
    if ($stmt->fetchColumn() == 0) {
        throw new Exception("Le professeur n'a pas encore initié la signature");
    }

    // Insérer la signature
    $stmt = $pdo->prepare("INSERT INTO sign (user_id, professeur_id, classe_id, cours_id, signature, signed, date_signature) 
                          VALUES (:user_id, :prof_id, :classe_id, :cours_id, :signature, 1, NOW())");
    
    $stmt->execute([
        'user_id' => $eleve_id,
        'prof_id' => $cours['prof_id'],
        'classe_id' => $cours['classe_id'],
        'cours_id' => $cours_id,
        'signature' => $signature_data
    ]);

    $pdo->commit();
    $_SESSION['success'] = "Signature enregistrée avec succès";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: signature_eleve.php");
exit();
