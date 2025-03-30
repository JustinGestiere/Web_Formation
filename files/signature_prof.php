<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: login.php");
    exit();
}

require_once "header_prof.php";
?>

<div class="main-content">
    <div class="container mt-4">
        <h1>Page des signatures</h1>
        <p>Contenu à venir...</p>
    </div>
</div>

<?php require_once "footer.php"; ?>
