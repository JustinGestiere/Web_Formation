<?php
include 'config.php'; // Inclure la configuration de la base de données

// Traitement de la demande de réinitialisation de mot de passe
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Vérifier si l'e-mail existe dans la base de données
    $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Générer un token unique pour la réinitialisation
        $token = bin2hex(random_bytes(50)); // Token sécurisé
        $expires_at = date("Y-m-d H:i:s", time() + 3600); // 1 heure de validité

        // Supprimer les anciens tokens existants avant d'en ajouter un nouveau
        $stmt = $pdo->prepare('DELETE FROM reset_tokens WHERE users_id = ?');
        $stmt->execute([$user['id']]);

        // Insérer le nouveau token
        $stmt = $pdo->prepare('INSERT INTO reset_tokens (users_id, token, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$user['id'], $token, $expires_at]);

        // Envoi du lien de réinitialisation par e-mail
        $resetLink = "http://yourdomain.com/reset_mdp.php?token=$token"; // Remplacer par l'URL de ton site

        $subject = "Réinitialisation de votre mot de passe";
        $message = "Cliquez sur le lien suivant pour réinitialiser votre mot de passe : $resetLink";
        $headers = "From: " . SENDER_MAIL;

        if (mail($email, $subject, $message, $headers)) {
            echo "Un e-mail avec les instructions de réinitialisation a été envoyé.";
        } else {
            echo "Erreur lors de l'envoi de l'e-mail.";
        }
    } else {
        echo "Cet e-mail n'est pas associé à un compte.";
    }
}

// Traitement du lien de réinitialisation de mot de passe
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifier si le token est valide et non expiré
    $stmt = $pdo->prepare('SELECT users_id, expires_at FROM reset_tokens WHERE token = ?');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if ($reset && strtotime($reset['expires_at']) > time()) {
        // Token valide, afficher le formulaire de réinitialisation
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Sécuriser le mot de passe

            // Mettre à jour le mot de passe de l'utilisateur
            $stmt = $pdo->prepare('UPDATE users SET passwords = ? WHERE id = ?');
            $stmt->execute([$password, $reset['users_id']]);

            // Supprimer le token de réinitialisation après utilisation
            $stmt = $pdo->prepare('DELETE FROM reset_tokens WHERE token = ?');
            $stmt->execute([$token]);

            echo "Votre mot de passe a été réinitialisé avec succès.";
            // Après la réinitialisation réussie
            header('Location: login.php');
            exit();
        }
    } else {
        echo "Ce lien de réinitialisation est invalide ou a expiré.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe</title>
</head>
<body>
    <!-- Formulaire pour demander un lien de réinitialisation -->
    <?php if (!isset($_GET['token'])): ?>
        <h1>Réinitialisation de votre mot de passe</h1>
        <form action="reset_mdp.php" method="post">
            <label for="email">Entrez votre adresse e-mail :</label>
            <input type="email" name="email" id="email" required>
            <button type="submit">Envoyer le lien de réinitialisation</button>
        </form>
    <?php endif; ?>

    <!-- Formulaire pour saisir un nouveau mot de passe -->
    <?php if (isset($_GET['token']) && !isset($_POST['password'])): ?>
        <h1>Réinitialiser votre mot de passe</h1>
        <form action="reset_mdp.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
            <label for="password">Nouveau mot de passe :</label>
            <input type="password" name="password" id="password" required>
            <button type="submit">Réinitialiser le mot de passe</button>
        </form>
    <?php endif; ?>
</body>
</html>
