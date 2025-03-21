<?php
/**
 * En-tête élève - Gestion des accès et de la navigation
 */

// Démarrage de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'eleve' && $_SESSION['user_role'] !== 'visiteur')) {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=web_formation', 'root', 'AulrrpTCD7Tk2nJ55H4v');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur BDD (eleve): " . $e->getMessage());
    exit("Erreur de connexion à la base de données.");
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="../css/header.css" rel="stylesheet">
    <title>Web Formation - Gestion de Planning</title>
</head>
<body>

<header class="bg-light">
    <div class="container_header">
        <div class="d-flex align-items-center py-3">
            <div class="d-flex align-items-center mr-3">
                <img src="../images/logo.jpg" alt="Logo de Web Formation" class="logo mr-2">
                <h2 class="h3 mb-0">Web Formation</h2>
            </div>
            <nav class="ml-auto">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/files/matieres.php">Matières</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/files/emploi_du_temps.php">Emploi du temps</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/files/signature.php">Signature</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="contact.php">Compte</a>
                    </li> -->

                    <!-- Afficher le bouton Déconnexion seulement si l'utilisateur est connecté -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <form method="post" action="/files/logout.php" class="d-inline">
                                <button type="submit" class="btn btn-danger nav-link">Déconnexion</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/files/login.php">Se connecter</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>

<!-- Scripts de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>