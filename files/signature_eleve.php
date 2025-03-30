<?php
session_start();
require_once "header_eleve.php"; // Inclusion du header pour l'élève

// Vérification si l'utilisateur est un élève
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'eleve') {
    header("Location: login.php");
    exit();
}

require_once "bdd.php"; // Connexion à la base de données

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM sign WHERE user_id = :user_id AND signed = 0";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$signature = $stmt->fetch();

// Si la signature est déjà réalisée, afficher un message
if (!$signature) {
    echo "<p>Vous n'avez pas de signature en attente.</p>";
    exit();
}

?>

<div class="main-content">
    <div class="container mt-4">
        <h1>Signature du professeur</h1>
        <form method="POST" action="enregistrer_signature.php">
            <p><strong>Professeur:</strong> <?= $signature['professeur_id'] ?></p>
            <p><strong>Classe:</strong> <?= $signature['classe_id'] ?></p>

            <label for="signature">Votre signature:</label>
            <textarea name="signature" id="signature" required></textarea>

            <button type="submit" class="btn btn-primary">Signer</button>
        </form>
    </div>
</div>

<?php require_once "footer.php"; ?>
