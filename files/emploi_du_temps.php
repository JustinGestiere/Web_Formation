<?php
session_start();
include('bdd.php'); // Inclure la connexion à la base de données

// Gestion de la classe sélectionnée
$selected_class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// Gestion de la semaine
$week_offset = isset($_GET['week_offset']) ? (int)$_GET['week_offset'] : 0;

// Calcul du lundi de la semaine en cours
$start_date = new DateTime();
$day_of_week = (int) $start_date->format('N'); // Numéro du jour (1 = Lundi, 7 = Dimanche)
$start_date->modify('-' . ($day_of_week - 1) . ' days'); // Ajuster au lundi
$start_date->modify("{$week_offset} week"); // Ajouter le décalage des semaines

// Récupérer les classes disponibles
$classes = $pdo->query("SELECT id, name FROM class")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les cours pour la classe sélectionnée
$cours = [];
if ($selected_class_id > 0) {
    $stmt = $pdo->prepare("
        SELECT titre, date_debut, date_fin 
        FROM cours 
        WHERE class_id = :class_id 
        AND DATE(date_debut) BETWEEN :start_date AND :end_date
        ORDER BY date_debut ASC
    ");
    $stmt->execute([
        ':class_id' => $selected_class_id,
        ':start_date' => $start_date->format('Y-m-d'),
        ':end_date' => $start_date->modify('+4 days')->format('Y-m-d')
    ]);
    $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <option value="">-- Sélectionner une classe --</option>
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
            Semaine du <?php echo $start_date->format('d/m/Y'); ?> au <?php echo $start_date->modify('+4 days')->format('d/m/Y'); ?>
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
                <?php
                // Générer les jours de la semaine
                $days = [];
                $start_date->modify('-4 days'); // Revenir au lundi après navigation
                for ($i = 0; $i < 5; $i++) {
                    $days[] = $start_date->format('Y-m-d');
                    $start_date->modify('+1 day');
                }

                // Préparer les cours pour chaque jour
                $cours_par_jour = [];
                foreach ($days as $day) {
                    $cours_par_jour[$day] = array_filter($cours, function ($cours_item) use ($day) {
                        return date('Y-m-d', strtotime($cours_item['date_debut'])) === $day;
                    });
                }

                // Affichage des cours
                echo '<tr>';
                foreach ($days as $day) {
                    echo '<td>';
                    if (isset($cours_par_jour[$day]) && count($cours_par_jour[$day]) > 0) {
                        foreach ($cours_par_jour[$day] as $cours_item) {
                            echo '<div class="cours">';
                            echo '<strong>' . htmlspecialchars($cours_item['titre']) . '</strong><br>';
                            echo date('H:i', strtotime($cours_item['date_debut'])) . ' - ' . date('H:i', strtotime($cours_item['date_fin']));
                            echo '</div><br>';
                        }
                    } else {
                        echo 'Aucun cours';
                    }
                    echo '</td>';
                }
                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
  include "footer.php";
?>