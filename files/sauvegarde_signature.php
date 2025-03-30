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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sign_id = $_POST['sign_id'];
    $signature = $_POST['signature'];

    // Décoder l'image base64
    $signature = str_replace('data:image/png;base64,', '', $signature);
    $signature = base64_decode($signature);

    // Sauvegarder l'image
    $file_name = "signatures/signature_$sign_id.png";
    file_put_contents($file_name, $signature);

    // Mettre à jour la BDD
    $sql_update = "UPDATE sign SET file_name = ?, statut = 'Signé' WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$file_name, $sign_id]);

    echo "Signature enregistrée avec succès !";
}

include ("footer.php");
?>