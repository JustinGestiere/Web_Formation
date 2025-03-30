<?php
session_start();
require_once "header_prof.php"; // Inclusion du header pour le professeur

// Vérification si l'utilisateur est un professeur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: login.php");
    exit();
}

require_once "bdd.php"; // Connexion à la base de données

// Récupérer les classes du professeur
$professeur_id = $_SESSION['user_id'];
$query = "SELECT c.id, c.nom FROM classes c
          JOIN professeur_classes pc ON c.id = pc.classe_id
          WHERE pc.professeur_id = :professeur_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['professeur_id' => $professeur_id]);
$classes = $stmt->fetchAll();

// Sélectionner les élèves pour chaque classe
if (isset($_POST['classe_id'])) {
    $classe_id = $_POST['classe_id'];
    $stmt = $pdo->prepare("SELECT u.id, u.nom FROM users u
                           JOIN class_users cu ON u.id = cu.user_id
                           WHERE cu.classe_id = :classe_id AND u.role = 'eleve'");
    $stmt->execute(['classe_id' => $classe_id]);
    $eleves = $stmt->fetchAll();
}
?>

<div class="main-content">
    <div class="container mt-4">
        <h1>Signatures des élèves</h1>
        <form method="POST">
            <label for="classe_id">Sélectionner une classe:</label>
            <select name="classe_id" id="classe_id" onchange="this.form.submit()">
                <option value="">Choisir une classe</option>
                <?php foreach ($classes as $classe) : ?>
                    <option value="<?= $classe['id'] ?>" <?= (isset($classe_id) && $classe_id == $classe['id']) ? 'selected' : '' ?>>
                        <?= $classe['nom'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (isset($eleves)) : ?>
            <form method="POST" action="signature_traitement.php">
                <h2>Élèves présents</h2>
                <?php foreach ($eleves as $eleve) : ?>
                    <input type="checkbox" name="eleves_present[]" value="<?= $eleve['id'] ?>"> <?= $eleve['nom'] ?><br>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary">Envoyer pour signature</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once "footer.php"; ?>
