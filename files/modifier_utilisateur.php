<?php
session_start();
require_once 'bdd.php'; // Inclure la connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $emails = $_POST['emails'];
    $ages = $_POST['ages'];
    $roles = $_POST['roles'];
    $classe_id = $_POST['classe_id'];

    try {
        // Mettre à jour les informations de l'utilisateur
        $stmt = $pdo->prepare("
            UPDATE users 
            SET nom = :nom, prenoms = :prenoms, emails = :emails, ages = :ages, roles = :roles, classe_id = :classe_id
            WHERE id = :id
        ");
        $stmt->execute([
            ':nom' => $nom,
            ':prenoms' => $prenoms,
            ':emails' => $emails,
            ':ages' => $ages,
            ':roles' => $roles,
            ':classe_id' => $classe_id,
            ':id' => $id,
        ]);

        $_SESSION['message'] = "Utilisateur mis à jour avec succès.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
    }

    // Rediriger vers la page de gestion des utilisateurs
    header("Location: /files/utilisateurs.php");
    exit();
}
?>
