<?php
session_start();
require_once "bdd.php"; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signature'])) {
    $user_id = $_SESSION['user_id'];
    $signature = $_POST['signature'];

    // Mettre à jour la signature de l'élève
    $query = "UPDATE sign SET signature = :signature, signed = 1, date_signature = NOW() 
              WHERE user_id = :user_id AND signed = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'signature' => $signature,
        'user_id' => $user_id
    ]);

    // Rediriger l'élève vers une page de confirmation
    header("Location: confirmation_signature_eleve.php");
    exit();
}
?>
