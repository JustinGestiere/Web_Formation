<?php
// Partie PHP inchangée...
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="../css/header_admin.css" rel="stylesheet">
    <title>Web Formation - Gestion de Planning</title>
</head>
<body>

<header class="bg-light">
    <div class="container_header_admin">
        <div class="d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <img src="../images/logo.jpg" alt="Logo de Web Formation" class="logo_header_admin mr-2">
                <h2 class="h3 mb-0">Web Formation</h2>
            </div>
            <!-- Bouton hamburger pour mobile -->
            <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
            </button>
            <nav>
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/files/matieres.php">Matières</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/files/classes.php">Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/files/cours.php">Cours</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/files/utilisateurs.php">Utilisateurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/files/emploi_du_temps.php">Emploi du temps</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/files/statistique.php">Statistiques</a>
                    </li>
                    <li class="nav-item">
                        <form method="post" action="/files/logout.php" class="d-inline">
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

<!-- Script pour le menu hamburger -->
<script>
// Fonction pour basculer l'affichage du menu
function toggleNavbar() {
  const navMenu = document.querySelector('.nav');
  navMenu.classList.toggle('show');
}

// Ajout de l'événement au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
  const navbarToggler = document.querySelector('.navbar-toggler');
  if (navbarToggler) {
    navbarToggler.addEventListener('click', toggleNavbar);
  }
});
</script>

</body>
</html>