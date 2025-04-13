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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="../css/header_admin.css" rel="stylesheet">
    <title>Web Formation - Gestion de Planning</title>
    <style>
        /* Styles pour la barre de navigation */
        #sidebar {
            position: fixed;
            width: 250px;
            height: 100%;
            left: -250px;
            top: 0;
            background-color: #f8f9fa;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 3px 0 5px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        #sidebar.active {
            left: 0;
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
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        #overlay.active {
            display: block;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: #007bff;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
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
            display: flex;
            flex-direction: column;
            justify-content: space-around;
        }

        .navbar-toggler span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: #333;
            border-radius: 2px;
            transition: all 0.3s ease;
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

        .close-sidebar {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 0 10px;
            color: white;
        }
        
        /* Styles pour les liens de navigation */
        .nav-link {
            padding: 10px 20px;
            color: #333;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>

<header>
    <div class="container_header_admin">
        <div class="d-flex align-items-center">
            <button class="navbar-toggler" type="button" onclick="toggleSidebar(this)">
                <div style="width: 25px; height: 3px; background-color: #333; margin: 4px 0; display: block !important;"></div>
                <div style="width: 25px; height: 3px; background-color: #333; margin: 4px 0; display: block !important;"></div>
                <div style="width: 25px; height: 3px; background-color: #333; margin: 4px 0; display: block !important;"></div>
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
            <a class="nav-link" href="eleve.php">Accueil</a>
        </li>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="absences.php">Absences/Présences</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="signature_eleve.php">Signatures</a>
            </li>
            <li class="nav-item">
                <form method="post" action="/files/logout.php" class="d-inline">
                    <button type="submit" class="btn btn-danger nav-link w-100">Déconnexion</button>
                </form>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="/files/login.php">Se connecter</a>
            </li>
        <?php endif; ?>
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
<!-- jQuery d'abord, puis Popper.js, puis Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>