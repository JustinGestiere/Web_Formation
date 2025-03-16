<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Vérifie si l'utilisateur est connecté et récupère son rôle
if (isset($_SESSION['user_role'])) {
    $user_role = $_SESSION['user_role'];
} else {
    // Si l'utilisateur n'est pas connecté, redirige vers login.php
    header("Location: files/login.php");
    exit();
}

// En fonction du rôle, inclure le bon header
switch ($user_role) {
    case 'admin':
        include('files/header_admin.php');
        break;
    case 'prof':
        include('files/header_prof.php');
        break;
    case 'eleve':
    case 'visiteur':
        include('files/header_eleve.php'); // Header générique pour élèves et visiteurs
        break;
    default:
        // Si aucun rôle valide, rediriger vers login.php
        header("Location: files/login.php");
        exit();
}
?>

