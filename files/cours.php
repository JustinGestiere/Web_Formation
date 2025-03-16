<?php
// Connexion à la base de données
include('bdd.php');

// Vérification des actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_course'])) {
        $matiere_id = $_POST['matiere_id'];
        $professeur_id = $_POST['professeur_id'];
        $start_datetime = $_POST['start_datetime'];
        $end_datetime = $_POST['end_datetime'];
        $class_id = $_POST['class_id'];

        // Insertion d'un nouveau cours
        $sql = "
        INSERT INTO emploi_du_temps (matiere_id, professeur_id, start_datetime, end_datetime, class_id)
        VALUES (:matiere_id, :professeur_id, :start_datetime, :end_datetime, :class_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':matiere_id' => $matiere_id,
            ':professeur_id' => $professeur_id,
            ':start_datetime' => $start_datetime,
            ':end_datetime' => $end_datetime,
            ':class_id' => $class_id,
        ]);
    }
}

// Récupération des matières, professeurs et classes
$sql = "SELECT id, name FROM matiere";
$matiere_stmt = $pdo->query($sql);
$matieres = $matiere_stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT id, nom FROM users WHERE roles LIKE '%professeur%'";
$professeur_stmt = $pdo->query($sql);
$professeurs = $professeur_stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT id, name FROM classes";
$class_stmt = $pdo->query($sql);
$classes = $class_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Cours</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gestion des Cours</h1>

    <!-- Formulaire pour ajouter un cours -->
    <h2>Ajouter un cours</h2>
    <form method="post">
        <label for="matiere_id">Matière :</label>
        <select name="matiere_id" id="matiere_id" required>
            <option value="">Sélectionner une matière</option>
            <?php foreach ($matieres as $matiere) : ?>
                <option value="<?php echo $matiere['id']; ?>"><?php echo htmlspecialchars($matiere['name']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="professeur_id">Professeur :</label>
        <select name="professeur_id" id="professeur_id" required>
            <option value="">Sélectionner un professeur</option>
            <?php foreach ($professeurs as $professeur) : ?>
                <option value="<?php echo $professeur['id']; ?>"><?php echo htmlspecialchars($professeur['nom']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="start_datetime">Date et Heure de Début :</label>
        <input type="datetime-local" name="start_datetime" id="start_datetime" required>

        <label for="end_datetime">Date et Heure de Fin :</label>
        <input type="datetime-local" name="end_datetime" id="end_datetime" required>

        <label for="class_id">Classe :</label>
        <select name="class_id" id="class_id" required>
            <option value="">Sélectionner une classe</option>
            <?php foreach ($classes as $class) : ?>
                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="add_course">Ajouter le cours</button>
    </form>

    <!-- Affichage de l'emploi du temps -->
    <h2>Emploi du Temps par Matière</h2>
    <?php
    $sql = "
    SELECT e.id, e.start_datetime, e.end_datetime, m.name AS matiere, p.nom AS professeur, c.name AS classe
    FROM emploi_du_temps e
    JOIN matiere m ON e.matiere_id = m.id
    JOIN users p ON e.professeur_id = p.id
    JOIN classes c ON e.class_id = c.id
    ORDER BY e.start_datetime";
    $stmt = $pdo->query($sql);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($courses) > 0) {
        echo '<table>';
        echo '<tr><th>Matière</th><th>Professeur</th><th>Classe</th><th>Heure de Début</th><th>Heure de Fin</th></tr>';
        foreach ($courses as $course) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($course['matiere']) . '</td>';
            echo '<td>' . htmlspecialchars($course['professeur']) . '</td>';
            echo '<td>' . htmlspecialchars($course['classe']) . '</td>';
            echo '<td>' . (new DateTime($course['start_datetime']))->format('d/m/Y H:i') . '</td>';
            echo '<td>' . (new DateTime($course['end_datetime']))->format('d/m/Y H:i') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Aucun cours trouvé.</p>';
    }
    ?>
</body>
</html>
