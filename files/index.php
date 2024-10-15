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
            <label for="email">Adresse mail :</label>
            <input type="email" class="form-control" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" name="remember" value="1" id="remember">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>

        <button type="submit" class="btn btn-primary">Connexion</button>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='register.php';">S'enregistrer</button>
    </form>
</main>
    <!-- Scripts de Bootstrap -->
    <script src="../js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
