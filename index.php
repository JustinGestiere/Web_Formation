<?php
session_start();
require_once 'config/config.php';
require_once 'config/database/database.php';

// Page par défaut
$page = $_GET['page'] ?? 'accueil';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) && !in_array($page, ['login', 'register'])) {
    header('Location: login.php');
    exit();
}

// Charger la page demandée
$file = 'files/' . $page . '.php';
if (file_exists($file)) {
    require_once $file;
} else {
    require_once 'files/error.php';
}
