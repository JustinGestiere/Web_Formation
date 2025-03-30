<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
try {
    require_once "bdd.php";
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification de la connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

// Vérification du rôle
if ($_SESSION['user_role'] !== 'prof') {
    die("Accès non autorisé. Vous devez être professeur pour accéder à cette page.");
}

// Inclusion du header
try {
    include "header_prof.php";
} catch (Exception $e) {
    die("Erreur lors du chargement du header : " . $e->getMessage());
}

// Récupérer le décalage de semaine depuis l'URL
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

// Récupération des classes du professeur
$classes_query = "SELECT DISTINCT c.id, c.name 
                 FROM classes c
                 INNER JOIN cours co ON c.id = co.class_id
                 WHERE co.professeur_id = :prof_id
                 ORDER BY c.name";
$stmt = $pdo->prepare($classes_query);
$stmt->execute([':prof_id' => $_SESSION['user_id']]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des cours du professeur
$cours = [];
$query = "
    SELECT c.titre, c.date_debut, c.date_fin, c.class_id, cl.name as class_name
    FROM cours c
    INNER JOIN classes cl ON c.class_id = cl.id
    WHERE c.professeur_id = :prof_id
    AND DATE(c.date_debut) BETWEEN :start_date AND :end_date
";
$params = [
    ':prof_id' => $_SESSION['user_id'],
    ':start_date' => $start_date->format('Y-m-d'),
    ':end_date' => $week_start->modify('-1 day')->format('Y-m-d') // Vendredi
];

if ($selected_class_id > 0) {
    $query .= " AND c.class_id = :class_id";
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
    <link href="../css/accueil.css" rel="stylesheet" />
    <title>Emploi du temps - Professeur</title>
</head>
<body>
    <div class="statistiques">
        <!-- Bloc Classes -->
        <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les classes
                        $sql = "SELECT name FROM classes ORDER BY name";
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
                        echo "<p>Aucune classe trouvée.</p>";
                    }
                    ?>
                </ul>
            </div>
        </details>

        <!-- Bloc Élèves -->
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

        <!-- Bloc Professeurs -->
        <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les professeurs
                        $sql = "SELECT * FROM users WHERE roles = 'prof' ORDER BY emails";
                        $stmt = $pdo->query($sql);
                        $profs = $stmt->fetchAll();
                        $profsCount = count($profs);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des professeurs : " . $e->getMessage());
                        $profs = [];
                        $profsCount = 0;
                    }
                ?>
                <p>
                    <h4>Professeurs ( <?php echo $profsCount; ?> )</h4>
                </p>
            </summary>
            <div class="liste_statistiques">
                <ul>
                    <?php
                    if ($profsCount > 0) {
                        foreach ($profs as $prof) {
                            echo "<li>" . htmlspecialchars($prof["nom"]) . " " . htmlspecialchars($prof["prenoms"]) . " (" . htmlspecialchars($prof["emails"]) . ")</li>";
                        }
                    } else {
                        echo "<p>Aucun professeur trouvé.</p>";
                    }
                    ?>
                </ul>
            </div>
        </details>

        <!-- Bloc Mes Cours -->
        <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer tous les cours du professeur
                        $sql = "SELECT titre FROM cours WHERE professeur_id = ? ORDER BY titre";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$_SESSION['user_id']]);
                        $mes_cours = $stmt->fetchAll();
                        $mes_coursCount = count($mes_cours);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des cours : " . $e->getMessage());
                        $mes_cours = [];
                        $mes_coursCount = 0;
                    }
                ?>
                <p>
                    <h4>Mes Cours ( <?php echo $mes_coursCount; ?> )</h4>
                </p>
            </summary>
            <div class="liste_statistiques">
                <ul>
                    <?php
                    if ($mes_coursCount > 0) {
                        foreach ($mes_cours as $cours) {
                            echo "<li>" . htmlspecialchars($cours["titre"]) . "</li>";
                        }
                    } else {
                        echo "<p>Aucun cours trouvé.</p>";
                    }
                    ?>
                </ul>
            </div>
        </details>
    </div>

    <h1>Mon Emploi du temps</h1>

    <!-- Formulaire de sélection de classe -->
    <form method="GET" action="">
        <input type="hidden" name="week_offset" value="<?php echo $week_offset; ?>">
        <label for="class_id">Filtrer par classe :</label>
        <select id="class_id" name="class_id" onchange="this.form.submit()">
            <option value="">-- Toutes mes classes --</option>
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
            Semaine du <?php echo $start_date->format('d/m/Y'); ?> au <?php echo $week_start->format('d/m/Y'); ?>
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
                                        <?php echo htmlspecialchars($cours_item['class_name']); ?><br>
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
try {
    include "footer.php";
} catch (Exception $e) {
    echo "Erreur lors du chargement du footer : " . $e->getMessage();
}
?>