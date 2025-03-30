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

// Récupérer les cours du professeur
$professeur_id = $_SESSION['user_id'];
var_dump("ID du professeur : " . $professeur_id); // Debug

// Récupérer les cours du professeur
$query = "SELECT c.*, cl.name as classe_nom, m.name as matiere_nom 
          FROM cours c 
          INNER JOIN classes cl ON c.class_id = cl.id
          INNER JOIN matieres m ON c.matiere_id = m.id
          WHERE c.professeur_id = :professeur_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['professeur_id' => $professeur_id]);
$cours = $stmt->fetchAll();
var_dump("Cours disponibles :", $cours);

// Sélectionner les élèves d'un cours
if (isset($_POST['cours_id'])) {
    $cours_id = $_POST['cours_id'];
    var_dump("Cours sélectionné : " . $cours_id); // Debug
    
    $stmt = $pdo->prepare("SELECT u.* FROM users u
                          INNER JOIN cours c ON u.classe_id = c.class_id
                          WHERE c.id = :cours_id AND u.roles = 'eleve'");
    $stmt->execute(['cours_id' => $cours_id]);
    $eleves = $stmt->fetchAll();
    var_dump($eleves); // Debug
}
?>

<div class="main-content">
    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <h1>Signatures des élèves</h1>
        <form method="POST">
            <div class="form-group">
                <label for="cours_id">Sélectionner un cours:</label>
                <select name="cours_id" id="cours_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Choisir un cours</option>
                    <?php if (!empty($cours)) : ?>
                        <?php foreach ($cours as $c) : ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($_POST['cours_id']) && $_POST['cours_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['titre'] . ' - ' . $c['matiere_nom'] . ' - ' . $c['classe_nom'] . ' (' . date('d/m/Y H:i', strtotime($c['date_debut'])) . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </form>

        <?php if (isset($eleves) && !empty($eleves)) : ?>
            <form method="POST" action="signature_traitement.php" class="mt-4">
                <input type="hidden" name="cours_id" value="<?= htmlspecialchars($cours_id) ?>">
                <h2>Élèves présents</h2>
                <div class="form-group">
                    <?php foreach ($eleves as $eleve) : ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="eleves_present[]" value="<?= $eleve['id'] ?>" id="eleve_<?= $eleve['id'] ?>">
                            <label class="form-check-label" for="eleve_<?= $eleve['id'] ?>"><?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenoms']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer pour signature</button>
            </form>
        <?php elseif (isset($_POST['cours_id'])) : ?>
            <div class="alert alert-info mt-4">
                Aucun élève trouvé dans ce cours.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "footer.php"; ?>
