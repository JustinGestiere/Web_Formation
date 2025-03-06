<?php
session_start(); // Démarre la session si ce n'est pas déjà fait

// Inclure le header approprié en fonction du rôle
if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            include "header_admin.php"; // Si rôle admin
            break;
        case 'prof':
            include "header_prof.php"; // Si rôle prof
            break;
        default:
            include "header.php"; // Sinon le header par défaut
            break;
    }
} else {
    // Si l'utilisateur n'est pas connecté, on peut rediriger vers login
    header("Location: login.php");
    exit();
}
?>

<head>
    <link href="css/accueil.css?v=<?php echo time(); ?>" rel="stylesheet" />
</head>
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
            <p>
                <h4>Classes ( <?php echo $namesCount; ?> )</h4>
            </p>
        </summary>
        <div class="liste_statistiques">
            <ul>
                <?php
                if ($namesCount > 0) {
                    foreach ($names as $name) {
                        echo "<li>" . htmlspecialchars($name["name"]) . "</li>";
                    }
                } else {
                    echo "<p>Aucune classe trouvé.</p>";
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
            <p>
                <h4>Élèves ( <?php echo $elevesCount; ?> )</h4>
            </p>
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
            <p>
                <h4>Professeurs ( <?php echo $professeursCount; ?> )</h4>
            </p>
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

// Récupération des cours pour la classe sélectionnée (ou tous les cours si aucune classe n'est sélectionnée)
$cours = [];
$query = "
    SELECT titre, date_debut, date_fin, class_id
    FROM cours
    WHERE DATE(date_debut) BETWEEN :start_date AND :end_date
";
$params = [
    ':start_date' => $start_date->format('Y-m-d'),
    ':end_date' => $week_start->modify('-1 day')->format('Y-m-d') // Vendredi
];

if ($selected_class_id > 0) {
    $query .= " AND class_id = :class_id";
    $params[':class_id'] = $selected_class_id;
}
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organisation des cours par jour
$cours_par_jour = [];
foreach ($days as $day) {
    $cours_par_jour[$day->format('Y-m-d')] = array_filter($cours, function ($cours_item) use ($day) {
        return date('Y-m-d', strtotime($cours_item['date_debut'])) === $day->format('Y-m-d');
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/emploi_du_temps.css" rel="stylesheet">
    <title>Calendrier des cours</title>
</head>
<body>
    <h1>Calendrier des cours</h1>

    <!-- Formulaire de sélection de classe -->
    <form method="GET" action="">
        <input type="hidden" name="week_offset" value="<?php echo $week_offset; ?>">
        <label for="class_id">Sélectionnez une classe :</label>
        <select id="class_id" name="class_id" onchange="this.form.submit()">
            <option value="">-- Toutes les classes --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?php echo $class['id']; ?>" <?php echo ($class['id'] == $selected_class_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($class['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Navigation entre les semaines -->
    <div class="navigation-semaine">
        <form method="GET" action="" style="display: inline;">
            <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
            <input type="hidden" name="week_offset" value="<?php echo $week_offset - 1; ?>">
            <button type="submit">← Semaine précédente</button>
        </form>
        <span>
            Semaine du <?php echo $start_date->format('d/m/Y'); ?> au <?php echo $week_start->modify('-1 day')->format('d/m/Y'); ?>
        </span>
        <form method="GET" action="" style="display: inline;">
            <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
            <input type="hidden" name="week_offset" value="<?php echo $week_offset + 1; ?>">
            <button type="submit">Semaine suivante →</button>
        </form>
    </div>

    <!-- Calendrier -->
    <div class="calendrier">
        <table border="1">
            <thead>
                <tr>
                    <th>Lundi</th>
                    <th>Mardi</th>
                    <th>Mercredi</th>
                    <th>Jeudi</th>
                    <th>Vendredi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach ($days as $day): ?>
                        <td>
                            <strong><?php echo $day->format('d/m/Y'); ?></strong><br>
                            <?php if (!empty($cours_par_jour[$day->format('Y-m-d')])): ?>
                                <?php foreach ($cours_par_jour[$day->format('Y-m-d')] as $cours_item): ?>
                                    <div class="cours">
                                        <strong><?php echo htmlspecialchars($cours_item['titre']); ?></strong><br>
                                        <?php echo date('H:i', strtotime($cours_item['date_debut'])); ?> - <?php echo date('H:i', strtotime($cours_item['date_fin'])); ?>
                                    </div><br>
                                <?php endforeach; ?>
                            <?php else: ?>
                                Aucun cours
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
  include "footer.php";
?>