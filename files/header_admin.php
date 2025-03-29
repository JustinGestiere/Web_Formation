<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=web_formation', 'root', 'AulrrpTCD7Tk2nJ55H4v');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur BDD (admin): " . $e->getMessage());
    exit("Erreur de connexion à la base de données.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="../css/header_admin.css" rel="stylesheet">
    <title>Web Formation - Gestion de Planning</title>
    <style>
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background-color: #fff;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        #sidebar.active {
            transform: translateX(0);
        }
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
        }
        #overlay.active {
            display: block;
        }
    </style>
</head>
<body>

<header>
    <div class="container_header_admin">
        <div class="d-flex align-items-center">
            <button class="navbar-toggler" type="button" onclick="toggleSidebar()">
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
            </button>
            <img src="../images/logo.jpg" alt="Logo de Web Formation" class="logo_header_admin">
            <h2 class="h3 mb-0">Web Formation</h2>
        </div>
    </div>
</header>

<nav id="sidebar">
    <div class="sidebar-header">
        <h3>Menu</h3>
        <button class="close-sidebar" onclick="toggleSidebar()">×</button>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="../index.php">Accueil</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="matieres.php">Matières</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="classes.php">Classes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="cours.php">Cours</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="utilisateurs.php">Utilisateurs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="emploi_du_temps.php">Emploi du temps</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="statistique.php">Statistiques</a>
        </li>
        <li class="nav-item">
            <form method="post" action="logout.php" class="d-inline">
                <button type="submit" class="btn btn-danger nav-link w-100">Déconnexion</button>
            </form>
        </li>
    </ul>
</nav>

<div id="overlay" onclick="toggleSidebar()"></div>

<main>
    <!-- Scripts de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</main>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
}
</script>

</body>
</html>
