<?php
// Inclure le fichier de connexion à la base de données
require 'bdd.php';

$error = ''; // Variable pour afficher les erreurs

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération des données du formulaire et nettoyage
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['username']);
    $age = trim($_POST['age']);
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];

    // Validation des entrées
    if (empty($nom) || empty($prenom) || empty($email) || empty($age) || empty($password) || empty($confirmpassword)) {
        $error = "Tous les champs doivent être remplis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse e-mail n'est pas valide.";
    } elseif (!is_numeric($age) || $age < 16) {
        $error = "L'âge doit être un nombre valide et supérieur ou égal à 16.";
    } elseif ($password !== $confirmpassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email existe déjà dans la base de données
        $sql = "SELECT * FROM users WHERE emails = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = "Cette adresse e-mail est déjà utilisée.";
        } else {
            // Hachage du mot de passe
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Préparation de la requête d'insertion
            $sql = "INSERT INTO users (nom, prenoms, emails, ages, passwords) VALUES (:nom, :prenom, :email, :age, :mot_de_passe)";
            $stmt = $pdo->prepare($sql);

            // Liaison des paramètres
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':mot_de_passe', $hashed_password);

            // Exécution de la requête
            if ($stmt->execute()) {
                // Redirection après l'inscription réussie
                header("Location: login.php");
                exit;
            } else {
                $error = "Erreur lors de l'inscription. Veuillez réessayer.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Web Formation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="../css/authentification.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="image">
            <img src="../images/logo.jpg" alt="Logo du site WEB FORMATION" class="logo">
            <h2 class="h2_logo">WEB FORMATION</h2>
        </div>
    </header>

    <main>
        <form method="post" action="register.php" class="p-4 border border-light rounded">
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" placeholder="Martin" class="form-control" id="nom" name="nom" required>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" placeholder="Jean" class="form-control" id="prenom" name="prenom" required>
            </div>

            <div class="form-group">
                <label for="username">Adresse mail :</label>
                <input type="email" placeholder="exemple@gmail.com" class="form-control" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="age">Âge :</label>
                <input type="number" placeholder="Votre âge" class="form-control" id="age" name="age" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" placeholder="Votre mot de passe" class="form-control" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirmpassword">Confirmer le mot de passe :</label>
                <input type="password" placeholder="Confirmation de votre mot de passe" class="form-control" id="confirmpassword" name="confirmpassword" required>
            </div>

            <!-- Afficher les erreurs ici -->
            <?php if ($error): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Inscription</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='login.php';">Accueil</button>
        </form>
    </main>

    <!-- Scripts de Bootstrap -->
    <script src="../js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
