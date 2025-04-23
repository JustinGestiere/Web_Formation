<?php
// Affichage des erreurs pour le debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
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
    <title>Web Formation - Espace Professeur</title>
    <style>
        /* Styles du bouton toggle */
        .navbar-toggler {
            position: relative;
            width: 45px;
            height: 40px;
            border: 2px solid #333;
            background: transparent;
            border-radius: 4px;
            cursor: pointer;
            padding: 8px;
            margin-right: 15px;
        }

        .navbar-toggler span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: #333;
            margin: 4px 0;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .navbar-toggler:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .navbar-toggler.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .navbar-toggler.active span:nth-child(2) {
            opacity: 0;
        }

        .navbar-toggler.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        /* Styles du header et sidebar */
        .container_header_admin {
            background: #f8f9fa;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 100%;
            z-index: 1001;
        }

        .logo_header_admin {
            height: 40px;
            margin-right: 15px;
        }

        #sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            background-color: #f8f9fa;
            transition: 0.3s;
            z-index: 1002;
            padding-top: 60px;
            border-right: 1px solid #dee2e6;
        }

        #sidebar.active {
            left: 0;
        }

        .sidebar-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .sidebar-header h3 {
            color: #333;
            margin: 0;
            font-size: 1.4em;
        }

        .close-sidebar {
            color: #333;
            position: absolute;
            right: 10px;
            top: 10px;
            background: none;
            border: none;
            font-size: 25px;
            cursor: pointer;
        }

        #sidebar .nav-link {
            color: #333;
            padding: 10px 20px;
            transition: 0.3s;
        }

        #sidebar .nav-link:hover {
            background-color: #e9ecef;
        }

        #overlay {
            display: none;
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 1001;
        }

        #overlay.active {
            display: block;
        }

        /* Ajustement du contenu principal */
        body {
            margin: 0;
            padding: 0;
        }

        .main-content {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<header>
    <div class="container_header_admin">
        <div class="d-flex align-items-center">
            <button class="navbar-toggler" type="button" onclick="toggleSidebar(this)">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <img src="../images/logo.jpg" alt="Logo de Web Formation" class="logo_header_admin">
            <h2 class="h3 mb-0">Web Formation</h2>
        </div>
    </div>
</header>

<nav id="sidebar">
    <div class="sidebar-header">
        <h3>Menu</h3>
        <button class="close-sidebar" onclick="toggleSidebar(document.querySelector('.navbar-toggler'))">×</button>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="professeur.php">Accueil</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="emploi_du_temps.php">Emploi du temps</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="signature_prof.php">Signature</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="absences.php">Absences/Présences</a>
        </li>
        <li class="nav-item">
            <form method="post" action="logout.php" class="d-inline">
                <button type="submit" class="btn btn-danger nav-link w-100">Déconnexion</button>
            </form>
        </li>
    </ul>
</nav>

<div id="overlay" onclick="toggleSidebar(document.querySelector('.navbar-toggler'))"></div>

<script>
function toggleSidebar(button) {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
    button.classList.toggle('active');
}
</script>

<!-- Scripts de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
