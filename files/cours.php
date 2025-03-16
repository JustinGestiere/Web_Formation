<?php
session_start();
require 'bdd.php';

// Récupérer les matières
$stmt = $pdo->query("SELECT id, nom FROM matieres");
$matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Création d'un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    if (isset($_POST['titre'], $_POST['description'], $_POST['date_debut'], $_POST['date_fin'], $_POST['professeur_id'], $_POST['class_id'], $_POST['matiere_id'])) {
        $stmt = $pdo->prepare("INSERT INTO cours (titre, description, date_debut, date_fin, professeur_id, class_id, matiere_id) VALUES (:titre, :description, :date_debut, :date_fin, :professeur_id, :class_id, :matiere_id)");
        $stmt->execute([
            ':titre' => $_POST['titre'],
            ':description' => $_POST['description'],
            ':date_debut' => $_POST['date_debut'],
            ':date_fin' => $_POST['date_fin'],
            ':professeur_id' => $_POST['professeur_id'],
            ':class_id' => $_POST['class_id'],
            ':matiere_id' => $_POST['matiere_id']
        ]);
        echo "Le cours a été créé avec succès.";
    }
}

// Récupérer les détails d'un cours pour modification
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM cours WHERE id = :id");
    $stmt->execute([':id' => $_GET['edit']]);
    $cours = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Modification d'un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    if (isset($_POST['id'], $_POST['titre'], $_POST['description'], $_POST['date_debut'], $_POST['date_fin'], $_POST['professeur_id'], $_POST['class_id'], $_POST['matiere_id'])) {
        $stmt = $pdo->prepare("UPDATE cours SET titre = :titre, description = :description, date_debut = :date_debut, date_fin = :date_fin, professeur_id = :professeur_id, class_id = :class_id, matiere_id = :matiere_id WHERE id = :id");
        $stmt->execute([
            ':id' => $_POST['id'],
            ':titre' => $_POST['titre'],
            ':description' => $_POST['description'],
            ':date_debut' => $_POST['date_debut'],
            ':date_fin' => $_POST['date_fin'],
            ':professeur_id' => $_POST['professeur_id'],
            ':class_id' => $_POST['class_id'],
            ':matiere_id' => $_POST['matiere_id']
        ]);
        echo "Le cours a été mis à jour avec succès.";
    }
}
?>
