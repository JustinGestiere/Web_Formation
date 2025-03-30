<?php
session_start(); // Démarre la session si ce n'est pas déjà fait

// Connexion à la base de données
require_once "db_connect.php";

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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/emploi_du_temps.css" rel="stylesheet">
    <link href="../css/accueil.css" rel="stylesheet" />
    <title>Accueil Professeur</title>
</head>
<body>
    <div class="statistiques">
        <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les classes du professeur connecté
                        $sql = "SELECT DISTINCT c.name 
                               FROM classes c
                               INNER JOIN cours co ON c.id = co.classe_id
                               WHERE co.professeur_id = :prof_id
                               ORDER BY c.name";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':prof_id' => $_SESSION['user_id']]);
                        $names = $stmt->fetchAll();
                        $namesCount = count($names);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des classes : " . $e->getMessage());
                        $names = [];
                        $namesCount = 0;
                    }
                ?>
                <p>
                    <h4>Mes Classes ( <?php echo $namesCount; ?> )</h4>
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

        <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les élèves des classes du professeur
                        $sql = "SELECT DISTINCT u.* 
                               FROM users u
                               INNER JOIN classes_eleves ce ON u.id = ce.eleve_id
                               INNER JOIN classes c ON ce.classe_id = c.id
                               INNER JOIN cours co ON c.id = co.classe_id
                               WHERE co.professeur_id = :prof_id 
                               AND u.roles = 'eleve'
                               ORDER BY u.emails";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':prof_id' => $_SESSION['user_id']]);
                        $eleves = $stmt->fetchAll();
                        $elevesCount = count($eleves);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des élèves : " . $e->getMessage());
                        $eleves = [];
                        $elevesCount = 0;
                    }
                ?>
                <p>
                    <h4>Mes Élèves ( <?php echo $elevesCount; ?> )</h4>
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
                        // Récupérer uniquement le professeur connecté
                        $sql = "SELECT * FROM users WHERE id = :prof_id AND roles = 'prof'";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':prof_id' => $_SESSION['user_id']]);
                        $professeurs = $stmt->fetchAll();
                        $professeursCount = count($professeurs);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des professeurs : " . $e->getMessage());
                        $professeurs = [];
                        $professeursCount = 0;
                    }
                ?>
                <p>
                    <h4>Mon Profil</h4>
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
                        echo "<p>Profil non trouvé.</p>";
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
    $stmt_classes = $pdo->prepare("
        SELECT DISTINCT c.id, c.name 
        FROM classes c
        INNER JOIN cours co ON c.id = co.classe_id
        WHERE co.professeur_id = :prof_id
        ORDER BY c.name
    ");
    $stmt_classes->execute([':prof_id' => $_SESSION['user_id']]);
    $classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des cours pour la classe sélectionnée (ou tous les cours si aucune classe n'est sélectionnée)
    $cours = [];
    $query = "
        SELECT c.titre, c.date_debut, c.date_fin, c.classe_id
        FROM cours c
        WHERE c.professeur_id = :prof_id
        AND DATE(c.date_debut) BETWEEN :start_date AND :end_date
    ";
    $params = [
        ':prof_id' => $_SESSION['user_id'],
        ':start_date' => $start_date->format('Y-m-d'),
        ':end_date' => $week_start->modify('-1 day')->format('Y-m-d') // Vendredi
    ];

    if ($selected_class_id > 0) {
        $query .= " AND c.classe_id = :class_id";
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

    <h1>Calendrier des cours</h1>

    <!-- Formulaire de sélection de classe -->
    <form method="GET" action="">
        <input type="hidden" name="week_offset" value="<?php echo $week_offset; ?>">
        <label for="class_id">Sélectionnez une classe :</label>
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

    <!-- Emploi du temps -->
    <div class="emploi-du-temps">
        <table>
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
                            <div class="date"><?php echo $day->format('d/m/Y'); ?></div>
                            <?php if (!empty($cours_par_jour[$day->format('Y-m-d')])): ?>
                                <?php foreach ($cours_par_jour[$day->format('Y-m-d')] as $cours_item): ?>
                                    <div class="cours">
                                        <strong><?php echo htmlspecialchars($cours_item['titre']); ?></strong><br>
                                        <?php echo date('H:i', strtotime($cours_item['date_debut'])); ?> - <?php echo date('H:i', strtotime($cours_item['date_fin'])); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-cours">Aucun cours</div>
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