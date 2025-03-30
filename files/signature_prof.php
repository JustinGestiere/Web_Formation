<?php
session_start();

// Vérification de la connexion et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: login.php");
    exit();
}

// Inclusion du header
require_once "header_prof.php";
?>

<div class="container mt-4">
    <h1>Page des signatures</h1>
    <p>Contenu à venir...</p>
</div>

</div> <!-- Fermeture de content-wrapper -->

<?php require_once "footer.php"; ?>

</body>
</html>
