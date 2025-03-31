<?php
session_start();
require_once "bdd.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'eleve') {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: signature_eleve.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée";
    header("Location: signature_eleve.php");
    exit();
}

if (!isset($_POST['cours_id']) || !isset($_POST['signature_data'])) {
    $_SESSION['error'] = "Données manquantes";
    header("Location: signature_eleve.php");
    exit();
}

$cours_id = $_POST['cours_id'];
$signature_data = $_POST['signature_data'];
$eleve_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Vérifier si le cours existe et si l'élève peut le signer
    $stmt = $pdo->prepare("SELECT c.*, s.id as sign_id, s.signed 
                          FROM cours c 
                          LEFT JOIN sign s ON s.cours_id = c.id 
                          AND s.user_id = :eleve_id
                          WHERE c.id = :cours_id");
    $stmt->execute([
        'cours_id' => $cours_id,
        'eleve_id' => $eleve_id
    ]);
    $result = $stmt->fetch();

    if (!$result) {
        throw new Exception("Ce cours n'existe pas");
    }

    if ($result['signed'] == 1) {
        throw new Exception("Vous avez déjà signé ce cours");
    }

    // Vérifier si le professeur a initié la signature
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sign 
                          WHERE cours_id = :cours_id 
                          AND professeur_id = :prof_id");
    $stmt->execute([
        'cours_id' => $cours_id,
        'prof_id' => $result['professeur_id']
    ]);
    
    if ($stmt->fetchColumn() == 0) {
        throw new Exception("Le professeur n'a pas encore initié la signature pour ce cours");
    }

    if ($result['sign_id']) {
        // Mettre à jour la signature existante
        $stmt = $pdo->prepare("UPDATE sign 
                              SET signature = :signature,
                                  signed = 1,
                                  date_signature = NOW() 
                              WHERE id = :sign_id");
        $stmt->execute([
            'signature' => $signature_data,
            'sign_id' => $result['sign_id']
        ]);
    } else {
        // Créer une nouvelle signature
        $stmt = $pdo->prepare("INSERT INTO sign (user_id, professeur_id, classe_id, cours_id, signature, signed, date_signature) 
                              VALUES (:user_id, :prof_id, :classe_id, :cours_id, :signature, 1, NOW())");
        $stmt->execute([
            'user_id' => $eleve_id,
            'prof_id' => $result['professeur_id'],
            'classe_id' => $result['class_id'],
            'cours_id' => $cours_id,
            'signature' => $signature_data
        ]);
    }

    $pdo->commit();
    $_SESSION['success'] = "Signature enregistrée avec succès";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: signature_eleve.php");
exit();
?>
