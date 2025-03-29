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

        // Récupérer les données du formulaire
        $id = $_POST['id'];
        $name = $_POST['name'];

        // Préparer et exécuter la requête de mise à jour
        $stmt = $pdo->prepare("UPDATE classes SET name = :name WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':id' => $id
        ]);

        // Redirection
        header("Location: /files/classes.php");
        exit;
    }
?>