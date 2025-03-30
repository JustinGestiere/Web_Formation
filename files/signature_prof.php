<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
var_dump("ID du professeur : " . $professeur_id); // Debug

// D'abord, affichons les tables disponibles
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
var_dump("Tables disponibles :", $tables);

// Affichons la structure de la table users
$stmt = $pdo->query("DESCRIBE users");
$users_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
var_dump("Structure de la table users :", $users_columns);

// Affichons la structure de la table classes
$stmt = $pdo->query("DESCRIBE classes");
$classes_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
var_dump("Structure de la table classes :", $classes_columns);

// Pour l'instant, récupérons toutes les classes
$query = "SELECT * FROM classes";
$stmt = $pdo->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll();
var_dump("Classes disponibles :", $classes);

// Sélectionner les élèves d'une classe
if (isset($_POST['classe_id'])) {
    $classe_id = $_POST['classe_id'];
    var_dump("Classe sélectionnée : " . $classe_id); // Debug
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'eleve' AND class_id = :classe_id");
    $stmt->execute(['classe_id' => $classe_id]);
    $eleves = $stmt->fetchAll();
    var_dump($eleves); // Debug
}
?>

<div class="main-content">
    <div class="container mt-4">
        <h1>Signatures des élèves</h1>
        <form method="POST">
            <div class="form-group">
                <label for="classe_id">Sélectionner une classe:</label>
                <select name="classe_id" id="classe_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Choisir une classe</option>
                    <?php if (!empty($classes)) : ?>
                        <?php foreach ($classes as $classe) : ?>
                            <option value="<?= $classe['id'] ?>" <?= (isset($_POST['classe_id']) && $_POST['classe_id'] == $classe['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($classe['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </form>

        <?php if (isset($eleves) && !empty($eleves)) : ?>
            <form method="POST" action="signature_traitement.php" class="mt-4">
                <h2>Élèves présents</h2>
                <div class="form-group">
                    <?php foreach ($eleves as $eleve) : ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="eleves_present[]" value="<?= $eleve['id'] ?>" id="eleve_<?= $eleve['id'] ?>">
                            <label class="form-check-label" for="eleve_<?= $eleve['id'] ?>"><?= htmlspecialchars($eleve['nom']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer pour signature</button>
            </form>
        <?php elseif (isset($_POST['classe_id'])) : ?>
            <div class="alert alert-info mt-4">
                Aucun élève trouvé dans cette classe.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "footer.php"; ?>
