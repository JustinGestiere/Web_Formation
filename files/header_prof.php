<?php
/**
 * En-tête professeur - Gestion des accès et de la navigation
 */

// Démarrage de la session
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
    error_log("Erreur BDD (prof): " . $e->getMessage());
    exit("Erreur de connexion à la base de données.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Formation - Gestion de Planning</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="../css/header_admin.css" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <style>
        body {
            overflow-x: hidden;
        }
        
        #sidebar {
            position: fixed;
            width: 250px;
            height: 100%;
            left: -250px;
            background: #f8f9fa;
            transition: all 0.3s;
            overflow-y: auto;
            z-index: 1000;
            padding: 20px;
            box-shadow: 3px 0 5px rgba(0,0,0,0.1);
        }
        
        #sidebar.active {
            left: 0;
        }
        
        #sidebar .list-unstyled {
            margin-top: 20px;
        }
        
        #sidebar .list-unstyled li {
            margin-bottom: 10px;
        }
        
        #sidebar .list-unstyled li a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        #sidebar .list-unstyled li a:hover {
            background-color: #e9ecef;
            color: #007bff;
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
            z-index: 1001;
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
            background-color: rgba(0,0,0,0.05);
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

        .container_header_admin {
            padding: 15px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            z-index: 999;
        }

        .logo_header_admin {
            height: 40px;
            margin-right: 15px;
        }

        .content-wrapper {
            margin-left: 0;
            transition: margin-left 0.3s;
            padding: 20px;
        }

        @media (min-width: 768px) {
            body.sidebar-active .content-wrapper {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="container_header_admin">
        <div class="d-flex align-items-center">
            <button class="navbar-toggler" type="button" onclick="toggleSidebar()">
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
        <button class="close-sidebar" onclick="toggleSidebar()">×</button>
    </div>
    <ul class="list-unstyled">
        <li><a href="professeur.php" class="nav-link">Accueil</a></li>
        <li><a href="emploi_du_temps.php" class="nav-link">Emploi du temps</a></li>
        <li><a href="signature_prof.php" class="nav-link">Signature</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><form method="post" action="logout.php" class="d-inline"><button type="submit" class="btn btn-danger nav-link w-100">Déconnexion</button></form></li>
        <?php else: ?>
            <li><a href="login.php" class="nav-link">Se connecter</a></li>
        <?php endif; ?>
    </ul>
</nav>

<div id="overlay" onclick="toggleSidebar()"></div>

<div class="content-wrapper">

<script>
function toggleSidebar() {
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const button = document.querySelector('.navbar-toggler');
    
    body.classList.toggle('sidebar-active');
    sidebar.classList.toggle('active');
    button.classList.toggle('active');
}

// Fermer le menu si on clique en dehors
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const button = document.querySelector('.navbar-toggler');
    
    if (!event.target.closest('#sidebar') && 
        !event.target.closest('.navbar-toggler') && 
        sidebar.classList.contains('active')) {
        toggleSidebar();
    }
});
</script>

</div>

</body>
</html>
