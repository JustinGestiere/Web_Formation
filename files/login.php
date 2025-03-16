<?php
session_start();

// Établir la connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=web_formation', 'root', 'AulrrpTCD7Tk2nJ55H4v');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur de connexion BDD (login): " . $e->getMessage());
    exit("Une erreur est survenue lors de la connexion à la base de données.");
}

// Vérifie si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            header("Location: ../index.php");
            exit();
        case 'prof':
            header("Location: ../index.php");
            exit();
        case 'eleve':
        case 'visiteur':
            header("Location: ../index.php");
            exit();
        default:
            header("Location: login.php");  // En cas de rôle invalide
            exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assainir les entrées
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars($_POST['password']);

    // Requête pour vérifier si l'utilisateur existe
    $sql = "SELECT * FROM users WHERE emails = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();

    // Vérifier les informations de connexion
    if ($user && password_verify($password, $user['passwords'])) {
        // Si l'utilisateur existe et le mot de passe est valide
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['roles']; // Récupère le rôle de l'utilisateur (admin, user, etc.)

        // Gérer le cookie si l'utilisateur souhaite se souvenir de lui
        if (isset($_POST['remember']) && $_POST['remember'] == '1') {
            setcookie('email', $email, time() + 3600 * 24 * 30, "/", "", true, true); // Cookie qui dure 30 jours
        }

        // Redirection vers la page d'accueil
        header("Location: ../index.php");
        exit();
    } else {
        $error_message = "Erreur d'authentification. Vérifiez votre email et votre mot de passe.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Web Formation - Votre plateforme d'apprentissage en ligne">
    <title>Connexion - Web Formation</title>
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
        <form method="post" action="login.php" class="p-4 border border-light rounded">
            <h3 class="text-center">Connexion</h3>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Adresse mail :</label>
                <input type="email" class="form-control" id="email" name="email" required 
                value="<?php echo isset($_COOKIE['email']) ? htmlspecialchars($_COOKIE['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Connexion</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='register.php';">S'enregistrer</button>
        </form>
    </main>

    <script src="../js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
