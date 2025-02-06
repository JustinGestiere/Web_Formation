<?php
// Inclure les fichiers nécessaires
require('C:/xampp/htdocs/bts_sio/Web_Formation/libs/PHPMailer.php'); // Chemin vers PhpMailer
require 'C:/xampp/htdocs/bts_sio/Web_Formation/libs/SMTP.php';
require 'C:/xampp/htdocs/bts_sio/Web_Formation/libs/Exception.php';
require 'C:/xampp/htdocs/bts_sio/Web_Formation/files/config.php'; // Fichier contenant les infos de connexion BDD et SENDER_MAIL

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=web_formation;charset=utf8mb4', 'root', '');

// Vérification de l'envoi du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Vérifier si l'email existe dans la table utilisateurs
    $stmt = $pdo->prepare('SELECT id FROM users WHERE emails = :email'); // Correction de la colonne "emails"
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Insérer le token dans la table reset_tokens
        $stmt = $pdo->prepare('INSERT INTO reset_tokens (users_id, token, expires_at) VALUES (:users_id, :token, :expires_at)');
        $stmt->execute([
            ':users_id' => $user['id'],  // Paramètre :users_id
            ':token' => $token,          // Paramètre :token
            ':expires_at' => $expiresAt // Paramètre :expires_at
        ]);

        // Envoi de l'email
        $mail = new PHPMailer(true);
        try {
            // Configuration du SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = SENDER_MAIL; // Adresse email du sender
            $mail->Password = 'rlhn lvlr bkwj sdyx'; // Mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Contenu de l'email
            $mail->setFrom(SENDER_MAIL, 'WEB FORMATION');
            $mail->addAddress($email);
            $mail->Subject = 'Reinitialisation de mot de passe';
            
            // URL dynamique avec le token généré
            $resetLink = 'http://localhost/bts_sio/Web_Formation/files/reset_mdp.php?token=' . $token;
            $mail->Body = 'Cliquez sur le lien suivant pour réinitialiser votre mot de passe : '
                        . $resetLink;

            $mail->send();
            echo 'Un email a été envoyé pour réinitialiser votre mot de passe.';
        } catch (Exception $e) {
            echo "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
        }
    } else {
        echo 'Aucun utilisateur trouvé avec cet email.';
    }
}
?>

<form method="POST" action="">
    <label for="email">Entrez votre email :</label>
    <input type="email" name="email" id="email" required>
    <button type="submit">Envoyer</button>
</form>
