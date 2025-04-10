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

$professeur_id = $_SESSION['user_id'];

// Récupérer les cours du professeur avec leur statut de signature
$query = "SELECT c.*, cl.name as classe_nom, m.name as matiere_nom,
          CASE WHEN EXISTS (
              SELECT 1 FROM sign s 
              WHERE s.cours_id = c.id 
              AND s.professeur_id = :professeur_id_sign
          ) THEN true ELSE false END as is_signed
          FROM cours c 
          INNER JOIN classes cl ON c.class_id = cl.id
          INNER JOIN matieres m ON c.matiere_id = m.id
          WHERE c.professeur_id = :professeur_id
          ORDER BY c.date_debut DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'professeur_id' => $professeur_id,
    'professeur_id_sign' => $professeur_id
]);
$cours = $stmt->fetchAll();

// Récupérer tous les élèves par classe
$eleves_par_classe = [];
foreach ($cours as $c) {
    if (!isset($eleves_par_classe[$c['class_id']])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE classe_id = :classe_id AND roles = 'eleve'");
        $stmt->execute(['classe_id' => $c['class_id']]);
        $eleves_par_classe[$c['class_id']] = $stmt->fetchAll();
    }
}

// Préparer les données pour le calendrier
$events = array();
foreach ($cours as $c) {
    $events[] = array(
        'id' => $c['id'],
        'title' => $c['titre'] . ' - ' . $c['matiere_nom'] . ' - ' . $c['classe_nom'],
        'start' => $c['date_debut'],
        'end' => $c['date_fin'],
        'backgroundColor' => $c['is_signed'] ? '#28a745' : '#007bff',
        'borderColor' => $c['is_signed'] ? '#28a745' : '#007bff',
        'textColor' => '#ffffff',
        'extendedProps' => array(
            'is_signed' => $c['is_signed'],
            'class_id' => $c['class_id']
        )
    );
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signatures des élèves</title>
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />
</head>
<body>

<div class="container mt-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <h1>Signatures des élèves</h1>
            <div id="calendar" class="mb-4"></div>
        </div>
        <div class="col-md-4">
            <div id="liste-eleves" class="mt-4" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Liste des élèves</h5>
                        <small id="cours-info"></small>
                    </div>
                    <div class="card-body">
                        <form id="signature-form" action="signature_traitement.php" method="POST">
                            <input type="hidden" name="cours_id" id="cours_id_input">
                            <div id="eleves-container">
                                <!-- La liste des élèves sera injectée ici -->
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="fas fa-signature"></i> Envoyer pour signature
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.4.0/main.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ['dayGrid', 'timeGrid', 'interaction'],
        defaultView: 'timeGridWeek',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'fr',
        timeZone: 'Europe/Paris',
        events: <?= json_encode($events) ?>,
        eventClick: function(info) {
            var event = info.event;
            var coursId = event.id;
            var classId = event.extendedProps.class_id;
            
            // Mettre à jour l'ID du cours dans le formulaire
            document.getElementById('cours_id_input').value = coursId;
            
            // Afficher les informations du cours
            document.getElementById('cours-info').textContent = event.title;
            
            // Afficher la liste des élèves pour cette classe
            var elevesContainer = document.getElementById('eleves-container');
            elevesContainer.innerHTML = '';
            
            var eleves = <?= json_encode($eleves_par_classe) ?>[classId] || [];
            eleves.forEach(function(eleve) {
                var div = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML = `
                    <input class="form-check-input" type="checkbox" name="eleves_present[]" 
                           value="${eleve.id}" id="eleve-${eleve.id}">
                    <label class="form-check-label" for="eleve-${eleve.id}">
                        ${eleve.prenoms} ${eleve.nom}
                    </label>
                `;
                elevesContainer.appendChild(div);
            });
            
            // Afficher le conteneur de la liste des élèves
            document.getElementById('liste-eleves').style.display = 'block';
        }
    });
    
    calendar.render();
});
</script>

</body>
</html>

<?php require_once "footer.php"; ?>
