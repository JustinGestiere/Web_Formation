<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Web Formation - Votre plateforme d'apprentissage en ligne">
    <title>Web Formation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="../css/authentification.css" rel="stylesheet">
</head>
    <body>
        <?php
            session_start();
            // Inclure le fichier de connexion
            require 'bdd.php';

            if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Vérifie la requête HTTP reçue est de type POST, c'est à dire que le formulaire a été soumis
                $email = $_POST['email']; 
                $password = $_POST['password'];

                // Verifie avec la bdd si le user existe
                $sql = "SELECT * FROM users WHERE emails = :email"; // verifie si l'email existe dans la bdd
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user = $stmt->fetch();

                //
                if ($user && password_verify($password, $user['passwords'])) { // Utiliser 'passwords' pour le champ de mot de passe
                    // Connexion réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['roles'];
                    // header("Location: index.php"); // Redirige vers la page d'accueil
                    header("Location: " . $_SESSION['user_role'] . ".php"); // Redirige vers la page d'accueil
                    exit;
                } else {
                    echo "Identifiants incorrects";
                }
            }
        ?>

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
                    <input type="email" class="form-control" id="email" name="email" required 
                    value="<?php echo isset($_COOKIE['email']) ? htmlspecialchars($_COOKIE['email']) : ''; ?>"> <!-- Préremplir avec le cookie -->
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
                <!-- <div class="form-group">
                    <a href="forgot_password.php" class="text-secondary">Mot de passe oublié ?</a>
                </div> -->
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
