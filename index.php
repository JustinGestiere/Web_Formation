<?php
session_start(); // Démarre la session si ce n'est pas déjà fait

// Inclure le header approprié en fonction du rôle
if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            include "files/header_admin.php"; // Si rôle admin
            break;
        case 'prof':
            include "files/header_prof.php"; // Si rôle prof
            break;
        default:
            include "files/header.php"; // Sinon le header par défaut
            break;
    }
} else {
    // Si l'utilisateur n'est pas connecté, on peut rediriger vers login
    header("Location: files/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/emploi_du_temps.css" rel="stylesheet">
    <link href="css/accueil.css" rel="stylesheet" />
    <title>Accueil</title>
</head>
<body>
    <div class="statistiques">
        <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les classes
                        $sql = "SELECT name FROM class ORDER BY name";
                        $stmt = $pdo->query($sql);
                        $names = $stmt->fetchAll();
                        $namesCount = count($names);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des classes : " . $e->getMessage());
                        $names = [];
                        $namesCount = 0;
                    }
                ?>
                <h4>Classes ( <?php echo $namesCount; ?> )</h4>
            </summary>
            <div class="liste_statistiques">
                <ul>
                    <?php
                    if ($namesCount > 0) {
                        foreach ($names as $name) {
                            echo "<li>" . htmlspecialchars($name["name"]) . "</li>";
                        }
                    } else {
                        echo "<p>Aucune classe trouvée.</p>";
                    }
                    ?>
                </ul>
            </div>
        </details>

        <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les élèves
                        $sql = "SELECT * FROM users WHERE roles = 'eleve' ORDER BY emails";
                        $stmt = $pdo->query($sql);
                        $eleves = $stmt->fetchAll();
                        $elevesCount = count($eleves);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des élèves : " . $e->getMessage());
                        $eleves = [];
                        $elevesCount = 0;
                    }
                ?>
                <h4>Élèves ( <?php echo $elevesCount; ?> )</h4>
            </summary>
            <div class="liste_statistiques">
                <ul>
                    <?php
                    if ($elevesCount > 0) {
                        foreach ($eleves as $eleve) {
                            echo "<li>" . htmlspecialchars($eleve["nom"]) . " " . htmlspecialchars($eleve["prenoms"]) . " (" . htmlspecialchars($eleve["emails"]) . ")</li>";
                        }
                    } else {
                        echo "<p>Aucun élève trouvé.</p>";
                    }
                    ?>
                </ul>
            </div>
        </details>

        <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les professeurs
                        $sql = "SELECT * FROM users WHERE roles = 'prof' ORDER BY emails";
                        $stmt = $pdo->query($sql);
                        $professeurs = $stmt->fetchAll();
                        $professeursCount = count($professeurs);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des professeurs : " . $e->getMessage());
                        $professeurs = [];
                        $professeursCount = 0;
                    }
                ?>
                <h4>Professeurs ( <?php echo $professeursCount; ?> )</h4>
            </summary>
            <div class="liste_statistiques">
                <ul>
                    <?php
                    if ($professeursCount > 0) {
                        foreach ($professeurs as $prof) {
                            echo "<li>" . htmlspecialchars($prof["nom"]) . " " . htmlspecialchars($prof["prenoms"]) . " (" . htmlspecialchars($prof["emails"]) . ")</li>";
                        }
                    } else {
                        echo "<p>Aucun professeur trouvé.</p>";
                    }
                    ?>
                </ul>
            </div>
        </details>
    </div>

    <!--  Emploi du temps -->
    <?php
    // Variables principales
    $week_offset = isset($_GET['week_offset']) ? (int)$_GET['week_offset'] : 0;
    $selected_class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

    // Définir le premier lundi de 2025 comme point de départ
    $start_date = new DateTime('2025-01-06');
    $start_date->modify("{$week_offset} week");

    // Calcul des jours de la semaine (du lundi au vendredi)
    $days = [];
    $week_start = clone $start_date;
    for ($i = 0; $i < 5; $i++) {
        $days[] = clone $week_start;
        $week_start->modify('+1 day');
    }

    // Récupération des classes disponibles
    $classes = $pdo->query("SELECT id, name FROM class")->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des emplois du temps pour la semaine
    $sql = "
    SELECT e.id, e.start_datetime, e.end_datetime, m.name as matiere, p.nom as professeur
    FROM emploi_du_temps e
    JOIN matiere m ON e.matiere_id = m.id
    JOIN users p ON e.professeur_id = p.id
    WHERE e.class_id = :class_id AND e.start_datetime BETWEEN :start_date AND :end_date
    ORDER BY e.start_datetime";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':class_id' => $selected_class_id,
        ':start_date' => $days[0]->format('Y-m-d'),
        ':end_date' => end($days)->format('Y-m-d'),
    ]);

    $classes_schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Sélection de la classe -->
    <div class="class_selector">
        <form method="get">
            <select name="class_id" onchange="this.form.submit()">
                <option value="0">Sélectionner une classe</option>
                <?php foreach ($classes as $class) : ?>
                    <option value="<?php echo $class['id']; ?>" <?php echo ($class['id'] == $selected_class_id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Affichage du planning -->
    <div class="calendar">
        <div class="week">
            <?php foreach ($days as $day) : ?>
                <div class="day">
                    <h5><?php echo $day->format('l'); ?></h5>
                    <p><?php echo $day->format('d/m'); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="schedules">
            <?php foreach ($classes_schedule as $schedule) : ?>
                <div class="schedule">
                    <h6><?php echo htmlspecialchars($schedule['matiere']); ?></h6>
                    <p><?php echo htmlspecialchars($schedule['professeur']); ?></p>
                    <p><?php echo (new DateTime($schedule['start_datetime']))->format('H:i') . ' - ' . (new DateTime($schedule['end_datetime']))->format('H:i'); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>
