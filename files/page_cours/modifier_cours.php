<?php
    session_start(); // Démarre la session si ce n'est pas déjà fait

    // Inclure le header approprié en fonction du rôle
    if (isset($_SESSION['user_role'])) {
        switch ($_SESSION['user_role']) {
            case 'admin':
                include "header_admin.php"; // Si rôle admin
                break;
            case 'prof':
                include "header_prof.php"; // Si rôle prof
                break;
            default:
                include "header.php"; // Sinon le header par défaut
                break;
        }
    } else {
        // Si l'utilisateur n'est pas connecté, on peut rediriger vers login
        header("Location: login.php");
        exit();
    }
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $professeur_id = $_POST['professeur_id'];
    $class_id = $_POST['class_id'];

    // Mise à jour du cours dans la base de données
    $stmt = $pdo->prepare("UPDATE cours SET titre = :titre, description = :description, date_debut = :date_debut, date_fin = :date_fin, professeur_id = :professeur_id, class_id = :class_id WHERE id = :id");
    $stmt->execute([
        ':titre' => $titre,
        ':description' => $description,
        ':date_debut' => $date_debut,
        ':date_fin' => $date_fin,
        ':professeur_id' => $professeur_id,
        ':class_id' => $class_id,
        ':id' => $id
    ]);

    $message = "Le cours a été mis à jour avec succès.";

    // Redirection
    header("Location: cours.php");
    exit;
}
?>
