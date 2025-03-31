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

// Définir le jour actif pour la vue mobile (par défaut aujourd'hui ou lundi si week-end)
$active_day_index = isset($_GET['active_day']) ? (int)$_GET['active_day'] : min(max(date('N') - 1, 0), 4);
if ($week_offset === 0) {
    $active_day_index = min(max(date('N') - 1, 0), 4);
} else {
    $active_day_index = isset($_GET['active_day']) ? (int)$_GET['active_day'] : 0;
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
            <div class="col-md-6 text-md-end">
                <a href="signature_eleve.php" class="btn btn-primary">
                    <i class="fas fa-signature"></i> Accéder aux signatures
                </a>
            </div>
        </div>

        <!-- Navigation entre les semaines -->
        <div class="navigation-semaine mb-4 text-center">
            <div class="btn-group">
                <a href="?week_offset=<?= $week_offset - 1 ?>&active_day=<?= $active_day_index ?>" class="btn btn-outline-primary">
                    <i class="fas fa-chevron-left"></i> Semaine précédente
                </a>
                <span class="btn btn-outline-primary disabled">
                    Semaine du <?= $start_date->format('d/m/Y') ?>
                </span>
                <a href="?week_offset=<?= $week_offset + 1 ?>&active_day=<?= $active_day_index ?>" class="btn btn-outline-primary">
                    Semaine suivante <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <!-- Navigation des jours (mobile) -->
        <div class="d-block d-md-none mb-3">
            <div class="nav nav-pills nav-fill">
                <?php foreach ($days as $index => $day): ?>
                    <a href="?week_offset=<?= $week_offset ?>&active_day=<?= $index ?>" 
                       class="nav-item nav-link <?= $index === $active_day_index ? 'active' : '' ?>">
                        <?= $day->format('D j') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Vue mobile -->
        <div class="d-block d-md-none">
            <?php 
            $current_day = $days[$active_day_index]->format('Y-m-d');
            ?>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?= $days[$active_day_index]->format('l j F Y') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($cours_par_jour[$current_day])): ?>
                        <?php foreach ($cours_par_jour[$current_day] as $cours_item): ?>
                            <div class="cours p-3 mb-3 bg-light rounded shadow-sm">
                                <h5 class="mb-2"><?= htmlspecialchars($cours_item['titre']) ?></h5>
                                <div class="cours-details">
                                    <p class="mb-1"><i class="fas fa-book"></i> <?= htmlspecialchars($cours_item['matiere_nom']) ?></p>
                                    <p class="mb-1"><i class="fas fa-user"></i> <?= htmlspecialchars($cours_item['prof_nom'] . ' ' . $cours_item['prof_prenoms']) ?></p>
                                    <p class="mb-1"><i class="far fa-clock"></i> <?= date('H:i', strtotime($cours_item['date_debut'])) ?> - <?= date('H:i', strtotime($cours_item['date_fin'])) ?></p>
                                    <?php if (!empty($cours_item['salle'])): ?>
                                        <p class="mb-0"><i class="fas fa-door-open"></i> Salle: <?= htmlspecialchars($cours_item['salle']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>Aucun cours prévu ce jour</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Vue desktop -->
        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <?php foreach ($days as $day): ?>
                                <th class="text-center"><?= $day->format('l j/m') ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php foreach ($days as $day): ?>
                                <td class="align-top">
                                    <?php if (!empty($cours_par_jour[$day->format('Y-m-d')])): ?>
                                        <?php foreach ($cours_par_jour[$day->format('Y-m-d')] as $cours_item): ?>
                                            <div class="cours p-2 mb-2 bg-light rounded shadow-sm">
                                                <strong><?= htmlspecialchars($cours_item['titre']) ?></strong><br>
                                                <small>
                                                    <?= htmlspecialchars($cours_item['matiere_nom']) ?><br>
                                                    <?= htmlspecialchars($cours_item['prof_nom'] . ' ' . $cours_item['prof_prenoms']) ?><br>
                                                    <?= date('H:i', strtotime($cours_item['date_debut'])) ?> - 
                                                    <?= date('H:i', strtotime($cours_item['date_fin'])) ?>
                                                    <?php if (!empty($cours_item['salle'])): ?>
                                                        <br>Salle: <?= htmlspecialchars($cours_item['salle']) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted text-center">Aucun cours</div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.navigation-semaine {
    margin: 20px 0;
}

.cours {
    border-left: 4px solid #007bff;
    transition: all 0.3s ease;
}

.cours:hover {
    background-color: #f8f9fa !important;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Styles mobile */
@media (max-width: 767.98px) {
    .nav-pills .nav-link {
        padding: 0.5rem 0.25rem;
        font-size: 0.9rem;
    }

    .nav-pills .nav-link.active {
        background-color: #007bff;
        color: white;
    }

    .cours-details p {
        margin-left: 1.5rem;
        position: relative;
    }

    .cours-details i {
        position: absolute;
        left: -1.5rem;
        top: 0.25rem;
        width: 1rem;
        text-align: center;
    }
}

/* Styles desktop */
@media (min-width: 768px) {
    .table td {
        height: 150px;
        width: 20%;
    }
}
</style>

<?php require_once "footer.php"; ?>