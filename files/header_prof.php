<?php
session_start();

// Vérification de la connexion
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
require_once "bdd.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Formation - Professeur</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="css/header_admin.css" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <style>
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

        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            background-color: #343a40;
            padding-top: 60px;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 1.1em;
            color: #fff;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            color: #7386D5;
            background: #fff;
        }

        .sidebar .closebtn {
            position: absolute;
            top: 0;
            right: 10px;
            font-size: 36px;
            margin-left: 50px;
            color: #fff;
            text-decoration: none;
        }

        .openbtn {
            font-size: 20px;
            cursor: pointer;
            padding: 10px 15px;
            border: none;
            background: none;
        }

        .openbtn:hover {
            background-color: #444;
        }

        #main {
            transition: margin-left .3s;
            padding: 16px;
        }

        .container_header_admin {
            background: #fff;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1001;
        }

        .logo_header_admin {
            height: 40px;
            margin-right: 15px;
        }

        @media screen and (max-height: 450px) {
            .sidebar {padding-top: 15px;}
            .sidebar a {font-size: 18px;}
        }

        .main-content {
            margin-top: 70px;
            margin-left: 0;
            transition: margin-left .3s;
        }

        .main-content.active {
            margin-left: 250px;
        }

        .overlay {
            display: none;
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
            cursor: pointer;
        }

        .overlay.active {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container_header_admin">
        <div class="d-flex align-items-center">
            <button class="navbar-toggler" type="button" onclick="toggleSidebar(this)">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <img src="images/logo.jpg" alt="Logo" class="logo_header_admin">
            <h2 class="mb-0">Web Formation - Professeur</h2>
        </div>
    </div>

    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="toggleSidebar(document.querySelector('.navbar-toggler'))">×</a>
        <a href="professeur.php"><i class="fas fa-home"></i> Accueil</a>
        <a href="signature_prof.php"><i class="fas fa-signature"></i> Signatures</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>

    <div id="overlay" class="overlay" onclick="toggleSidebar(document.querySelector('.navbar-toggler'))"></div>

    <div class="main-content">

<script>
function toggleSidebar(button) {
    const sidebar = document.getElementById("mySidebar");
    const main = document.querySelector(".main-content");
    sidebar.classList.toggle("active");
    button.classList.toggle("active");
    main.classList.toggle("active");
    document.getElementById("overlay").classList.toggle("active");
}

// Fermer le menu si on clique en dehors
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById("mySidebar");
    const button = document.querySelector(".navbar-toggler");
    
    if (sidebar.classList.contains("active") && 
        !sidebar.contains(event.target) && 
        !button.contains(event.target) && 
        !document.querySelector(".container_header_admin").contains(event.target)) {
        toggleSidebar(button);
    }
});
</script>
