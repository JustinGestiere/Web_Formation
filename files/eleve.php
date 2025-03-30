<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "header_eleve.php";

// Vérification si l'utilisateur est un élève
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'eleve') {
    header("Location: login.php");
    exit();
}

require_once "bdd.php";

// Variables principales
$week_offset = isset($_GET['week_offset']) ? (int)$_GET['week_offset'] : 0;
$eleve_id = $_SESSION['user_id'];

// Récupérer les informations de l'élève
$stmt = $pdo->prepare("SELECT u.*, c.name as classe_nom, c.id as classe_id 
                       FROM users u 
                       LEFT JOIN classes c ON u.classe_id = c.id 
                       WHERE u.id = :eleve_id");
$stmt->execute(['eleve_id' => $eleve_id]);
$eleve = $stmt->fetch();

if (!$eleve) {
    die("Élève non trouvé");
}

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

// Récupération des cours pour la classe de l'élève
$query = "
    SELECT c.*, m.name as matiere_nom, p.nom as prof_nom, p.prenoms as prof_prenoms
    FROM cours c
    INNER JOIN matieres m ON c.matiere_id = m.id
    INNER JOIN users p ON c.professeur_id = p.id
    WHERE c.class_id = :class_id 
    AND DATE(date_debut) BETWEEN :start_date AND :end_date
    ORDER BY date_debut ASC";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'class_id' => $eleve['classe_id'],
    'start_date' => $start_date->format('Y-m-d'),
    'end_date' => $week_start->modify('-1 day')->format('Y-m-d')
]);
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organisation des cours par jour
$cours_par_jour = [];
foreach ($days as $day) {
    $cours_par_jour[$day->format('Y-m-d')] = array_filter($cours, function ($cours_item) use ($day) {
        return date('Y-m-d', strtotime($cours_item['date_debut'])) === $day->format('Y-m-d');
    });
}
?>

<div class="main-content">
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Mon emploi du temps</h1>
                <p class="lead">
                    <?= htmlspecialchars($eleve['prenoms'] . ' ' . $eleve['nom']) ?> - 
                    Classe : <?= htmlspecialchars($eleve['classe_nom']) ?>
                </p>
            </div>
            <div class="col-md-6 text-md-right">
                <a href="signature_eleve.php" class="btn btn-primary">
                    <i class="fas fa-signature"></i> Accéder aux signatures
                </a>
            </div>
        </div>

        <!-- Navigation entre les semaines -->
        <div class="navigation-semaine mb-4 text-center">
            <form method="GET" action="" class="d-inline">
                <input type="hidden" name="week_offset" value="<?php echo $week_offset - 1; ?>">
                <button type="submit" class="btn btn-secondary">← Semaine précédente</button>
            </form>
            <span class="mx-3">
                Semaine du <?php echo $start_date->format('d/m/Y'); ?> au <?php echo $week_start->format('d/m/Y'); ?>
            </span>
            <form method="GET" action="" class="d-inline">
                <input type="hidden" name="week_offset" value="<?php echo $week_offset + 1; ?>">
                <button type="submit" class="btn btn-secondary">Semaine suivante →</button>
            </form>
        </div>

        <!-- Calendrier -->
        <div class="calendrier">
            <table class="table table-bordered">
                <thead class="table-dark">
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
                            <td class="align-top">
                                <div class="date-header mb-2">
                                    <?php echo $day->format('d/m/Y'); ?>
                                </div>
                                <?php if (!empty($cours_par_jour[$day->format('Y-m-d')])): ?>
                                    <?php foreach ($cours_par_jour[$day->format('Y-m-d')] as $cours_item): ?>
                                        <div class="cours p-2 mb-2 bg-light rounded">
                                            <strong><?php echo htmlspecialchars($cours_item['titre']); ?></strong><br>
                                            <small>
                                                <?php echo htmlspecialchars($cours_item['matiere_nom']); ?><br>
                                                <?php echo htmlspecialchars($cours_item['prof_nom'] . ' ' . $cours_item['prof_prenoms']); ?><br>
                                                <?php echo date('H:i', strtotime($cours_item['date_debut'])); ?> - 
                                                <?php echo date('H:i', strtotime($cours_item['date_fin'])); ?><br>
                                                <?php if (!empty($cours_item['salle'])): ?>
                                                    Salle: <?php echo htmlspecialchars($cours_item['salle']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted">Aucun cours</div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.navigation-semaine {
    margin: 20px 0;
    text-align: center;
}
.calendrier {
    margin-top: 20px;
}
.date-header {
    font-weight: bold;
    background-color: #f8f9fa;
    padding: 5px;
    border-radius: 4px;
}
.cours {
    border-left: 4px solid #007bff;
}
</style>

<?php require_once "footer.php"; ?>