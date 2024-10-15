<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Web Formation - Votre plateforme d'apprentissage en ligne">
    <title>Web Formation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="image">
            <img src="../images/logo.jpg" alt="Logo du site WEB FORMATION" class="logo">
            <h2 class="h2_logo">WEB FORMATION</h2>
        </div>
    </header>

    <main>
    <form method="post" action="login.php" class="p-4 border border-light rounded">
        <div class="form-group">
            <label for="text">Nom :</label>
            <input type="text" placeholder="Martin" class="form-control" id="nom" name="nom" required>
        </div>

        <div class="form-group">
            <label for="text">Prenom :</label>
            <input type="text" placeholder="Jean" class="form-control" id="prenom" name="prenom" required>
        </div>

        <div class="form-group">
            <label for="email">Adresse mail :</label>
            <input type="email" placeholder="exemple@gmail.com" class="form-control" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" placeholder="Votre mot de passe" class="form-control" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="password">Confirmer le mot de passe :</label>
            <input type="password" placeholder="Confirmation de votre mot de passe" class="form-control" id="confirmpassword" name="confirmpassword" required>
        </div>

        <p id="message"></p> <!-- Paragraphe pour afficher le message -->

        <button type="submit" class="btn btn-primary">Connexion</button>
    </form>
</main>
    <!-- Scripts de Bootstrap -->
    <script src="../js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
