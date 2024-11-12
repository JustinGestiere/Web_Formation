<?php
    session_start();
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
        .logo {
            height: 50px; /* Ajustez la taille du logo si nécessaire */
        }
    </style>
</head>
<body>

<header class="bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <img src="../images/logo.jpg" alt="Logo de Web Formation" class="logo mr-2">
                <h1 class="h3 mb-0">Web Formation</h1>
            </div>
            <nav>
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">Matières</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">Élèves</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule.php">Emploi du temps</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">Signature</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Compte</a>
                    </li>
                    <li class="nav-item">
                        <form method="post" action="logout.php" class="d-inline">
                            <button type="submit" class="btn btn-danger nav-link">Déconnexion</button>
                        </form>
                    </li>
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