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

<?php
// Récupérer l'ID du professeur connecté
$prof_id = $_SESSION['user_id'];

// Récupérer les classes associées au professeur
$stmt_classes = $pdo->prepare("
    SELECT DISTINCT c.* 
    FROM classes c
    INNER JOIN cours co ON c.id = co.classe_id
    WHERE co.professeur_id = ?
");
$stmt_classes->execute([$prof_id]);
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les élèves des classes associées au professeur
$stmt_eleves = $pdo->prepare("
    SELECT DISTINCT u.* 
    FROM utilisateurs u
    INNER JOIN eleves_classes ec ON u.id = ec.eleve_id
    INNER JOIN classes c ON ec.classe_id = c.id
    INNER JOIN cours co ON c.id = co.classe_id
    WHERE co.professeur_id = ? AND u.role = 'eleve'
    ORDER BY u.nom, u.prenom
");
$stmt_eleves->execute([$prof_id]);
$eleves = $stmt_eleves->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les cours du professeur pour cette semaine
$debut_semaine = new DateTime('monday this week');
$fin_semaine = new DateTime('friday this week');
$days = new DatePeriod($debut_semaine, new DateInterval('P1D'), $fin_semaine->modify('+1 day'));

$stmt_cours = $pdo->prepare("
    SELECT c.*, cl.nom as classe_nom, m.nom as matiere_nom
    FROM cours c
    INNER JOIN classes cl ON c.classe_id = cl.id
    INNER JOIN matieres m ON c.matiere_id = m.id
    WHERE c.professeur_id = ?
    AND c.date_debut BETWEEN ? AND ?
    ORDER BY c.date_debut
");
$stmt_cours->execute([
    $prof_id,
    $debut_semaine->format('Y-m-d'),
    $fin_semaine->format('Y-m-d')
]);
$cours = $stmt_cours->fetchAll(PDO::FETCH_ASSOC);

// Organiser les cours par jour
$cours_par_jour = [];
foreach ($cours as $cours_item) {
    $date = (new DateTime($cours_item['date_debut']))->format('Y-m-d');
    if (!isset($cours_par_jour[$date])) {
        $cours_par_jour[$date] = [];
    }
    $cours_par_jour[$date][] = $cours_item;
}
?>

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
  include "files/footer.php";
?>

<!-- Section Classes -->
<div class="section">
    <h2>Mes Classes</h2>
    <div class="liste-classes">
        <?php if (!empty($classes)): ?>
            <?php foreach ($classes as $classe): ?>
                <div class="classe-card">
                    <h3><?php echo htmlspecialchars($classe['nom']); ?></h3>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune classe assignée</p>
        <?php endif; ?>
    </div>
</div>

<!-- Section Élèves -->
<div class="section">
    <h2>Mes Élèves</h2>
    <div class="liste-eleves">
        <?php if (!empty($eleves)): ?>
            <?php foreach ($eleves as $eleve): ?>
                <div class="eleve-card">
                    <h3><?php echo htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']); ?></h3>
                    <p>Email: <?php echo htmlspecialchars($eleve['email']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun élève assigné</p>
        <?php endif; ?>
    </div>
</div>

<!-- Section Emploi du temps -->
<div class="section">
    <h2>Mon Emploi du temps de la semaine</h2>
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
                            <strong><?php echo $day->format('d/m/Y'); ?></strong><br>
                            <?php if (!empty($cours_par_jour[$day->format('Y-m-d')])): ?>
                                <?php foreach ($cours_par_jour[$day->format('Y-m-d')] as $cours_item): ?>
                                    <div class="cours">
                                        <strong><?php echo htmlspecialchars($cours_item['titre']); ?></strong><br>
                                        <?php echo htmlspecialchars($cours_item['classe_nom']); ?> - 
                                        <?php echo htmlspecialchars($cours_item['matiere_nom']); ?><br>
                                        <?php echo date('H:i', strtotime($cours_item['date_debut'])); ?> - 
                                        <?php echo date('H:i', strtotime($cours_item['date_fin'])); ?>
                                    </div>
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
</div>