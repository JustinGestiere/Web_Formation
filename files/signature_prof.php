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

$query = "SELECT DISTINCT c.* FROM classes c
          INNER JOIN emploi_du_temps edt ON c.id = edt.class_id
          WHERE edt.professeur_id = :professeur_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['professeur_id' => $professeur_id]);
$classes = $stmt->fetchAll();
var_dump($classes); // Debug

// Sélectionner les élèves pour chaque classe
if (isset($_POST['classe_id'])) {
    $classe_id = $_POST['classe_id'];
    var_dump("Classe sélectionnée : " . $classe_id); // Debug
    
    $stmt = $pdo->prepare("SELECT DISTINCT u.* FROM users u
                          INNER JOIN emploi_du_temps edt ON u.class_id = edt.class_id
                          WHERE edt.class_id = :classe_id 
                          AND u.role = 'eleve'");
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
