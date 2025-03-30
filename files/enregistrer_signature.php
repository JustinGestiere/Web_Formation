<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "bdd.php";

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'eleve') {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée";
    header("Location: signature_eleve.php");
    exit();
}

// Récupération des données
$cours_id = isset($_POST['cours_id']) ? intval($_POST['cours_id']) : 0;
$signature_data = isset($_POST['signature_data']) ? $_POST['signature_data'] : '';
$eleve_id = $_SESSION['user_id'];

if (!$cours_id || !$signature_data) {
    $_SESSION['error'] = "Données manquantes";
    header("Location: signature_eleve.php");
    exit();
}

try {
    // Début de la transaction
    $pdo->beginTransaction();

    // Récupérer les informations du cours
    $stmt = $pdo->prepare("SELECT c.*, u.classe_id 
                          FROM cours c 
                          INNER JOIN users u ON u.id = :eleve_id 
                          WHERE c.id = :cours_id");
    $stmt->execute([
        'eleve_id' => $eleve_id,
        'cours_id' => $cours_id
    ]);
    $cours = $stmt->fetch();

    if (!$cours) {
        throw new Exception("Cours non trouvé");
    }

    // Vérifier si une signature existe déjà pour ce cours et cet élève
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sign 
                          WHERE cours_id = :cours_id 
                          AND user_id = :eleve_id 
                          AND DATE(date_signature) = DATE(:date_cours)");
    $stmt->execute([
        'cours_id' => $cours_id,
        'eleve_id' => $eleve_id,
        'date_cours' => $cours['date_debut']
    ]);
    
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Vous avez déjà signé ce cours");
    }

    // Vérifier si le professeur a initié la signature
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sign 
                          WHERE cours_id = :cours_id 
                          AND professeur_id = :prof_id 
                          AND DATE(date_signature) = DATE(:date_cours)");
    $stmt->execute([
        'cours_id' => $cours_id,
        'prof_id' => $cours['professeur_id'],
        'date_cours' => $cours['date_debut']
    ]);

    if ($stmt->fetchColumn() == 0) {
        throw new Exception("La signature n'est pas encore disponible pour ce cours");
    }

    // Enregistrer la signature
    $stmt = $pdo->prepare("INSERT INTO sign (
        cours_id,
        user_id,
        professeur_id,
        classe_id,
        signature,
        signed,
        date_signature
    ) VALUES (
        :cours_id,
        :user_id,
        :professeur_id,
        :classe_id,
        :signature,
        1,
        :date_signature
    )");

    // Utiliser NOW() pour la date actuelle et ajuster pour le fuseau horaire Europe/Paris
    $date = new DateTime('now', new DateTimeZone('Europe/Paris'));
    
    $stmt->execute([
        'cours_id' => $cours_id,
        'user_id' => $eleve_id,
        'professeur_id' => $cours['professeur_id'],
        'classe_id' => $cours['classe_id'],
        'signature' => $signature_data,
        'date_signature' => $date->format('Y-m-d H:i:s')
    ]);

    // Valider la transaction
    $pdo->commit();
    
    $_SESSION['success'] = "Signature enregistrée avec succès";
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: signature_eleve.php");
exit();
