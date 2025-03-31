<?php
session_start();
require_once "bdd.php";

// Vérification si l'utilisateur est un professeur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: login.php");
    exit();
}

// Vérification des données reçues
if (!isset($_POST['cours_id']) || !isset($_POST['eleves_present']) || !is_array($_POST['eleves_present'])) {
    $_SESSION['error'] = "Données invalides";
    header("Location: signature_prof.php");
    exit();
}

$cours_id = intval($_POST['cours_id']);
$eleves_presents = $_POST['eleves_present'];
$date_signature = date('Y-m-d H:i:s');
$professeur_id = $_SESSION['user_id'];

try {
    // Début de la transaction
    $pdo->beginTransaction();

    // Récupérer les informations du cours pour avoir la classe_id
    $stmt = $pdo->prepare("SELECT class_id FROM cours WHERE id = :cours_id");
    $stmt->execute(['cours_id' => $cours_id]);
    $cours = $stmt->fetch();
    
    if (!$cours) {
        throw new Exception("Cours non trouvé");
    }

    // Préparation de la requête d'insertion
    $stmt = $pdo->prepare("INSERT INTO sign (user_id, professeur_id, classe_id, cours_id, statut, signature, signed, date_signature) 
                          VALUES (:user_id, :professeur_id, :classe_id, :cours_id, 'En attente', '', 0, :date_signature)");

    // Insertion pour chaque élève présent
    foreach ($eleves_presents as $eleve_id) {
        $stmt->execute([
            'user_id' => $eleve_id,
            'professeur_id' => $professeur_id,
            'classe_id' => $cours['class_id'],
            'cours_id' => $cours_id,
            'date_signature' => $date_signature
        ]);
    }

    // Validation de la transaction
    $pdo->commit();
    $_SESSION['success'] = "Les signatures ont été enregistrées avec succès";
} catch (Exception $e) {
    // En cas d'erreur, annulation de la transaction
    $pdo->rollBack();
    $_SESSION['error'] = "Erreur lors de l'enregistrement des signatures : " . $e->getMessage();
}

// Redirection vers la page des signatures
header("Location: signature_prof.php");
exit();
