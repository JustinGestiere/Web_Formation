<?php
session_start();
require_once "bdd.php"; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eleves_present'])) {
    $professeur_id = $_SESSION['user_id'];
    $eleves_present = $_POST['eleves_present'];

    // Parcourir chaque élève sélectionné pour créer une entrée dans la table sign
    foreach ($eleves_present as $eleve_id) {
        $query = "INSERT INTO sign (user_id, professeur_id, classe_id, statut, signature, signed, date_signature)
                  VALUES (:user_id, :professeur_id, :classe_id, 'En attente', '', 0, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'user_id' => $eleve_id,
            'professeur_id' => $professeur_id,
            'classe_id' => $_POST['classe_id']
        ]);
    }

    // Rediriger l'utilisateur vers une page de confirmation
    header("Location: confirmation_signature.php");
    exit();
}
?>
