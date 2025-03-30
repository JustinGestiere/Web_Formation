<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "header.php";

// Vérification si l'utilisateur est un élève
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'eleve') {
    header("Location: login.php");
    exit();
}

require_once "bdd.php";

$eleve_id = $_SESSION['user_id'];

// Récupérer les informations de l'élève
$stmt = $pdo->prepare("SELECT u.*, c.name as classe_nom 
                       FROM users u 
                       LEFT JOIN classes c ON u.classe_id = c.id 
                       WHERE u.id = :eleve_id");
$stmt->execute(['eleve_id' => $eleve_id]);
$eleve = $stmt->fetch();

if (!$eleve) {
    die("Élève non trouvé");
}

// Récupérer les cours de la classe
$query = "SELECT c.*, cl.name as classe_nom, m.name as matiere_nom, 
          p.nom as prof_nom, p.prenoms as prof_prenoms
          FROM cours c 
          INNER JOIN classes cl ON c.class_id = cl.id
          INNER JOIN matieres m ON c.matiere_id = m.id
          INNER JOIN users p ON c.professeur_id = p.id
          WHERE c.class_id = :classe_id
          ORDER BY c.date_debut ASC";

$stmt = $pdo->prepare($query);
$stmt->execute(['classe_id' => $eleve['classe_id']]);
$cours = $stmt->fetchAll();

// Préparer les données pour le calendrier
$events = array();
foreach ($cours as $c) {
    $events[] = array(
        'id' => $c['id'],
        'title' => $c['titre'] . ' - ' . $c['matiere_nom'],
        'start' => $c['date_debut'],
        'end' => $c['date_fin'],
        'backgroundColor' => '#007bff',
        'borderColor' => '#007bff',
        'textColor' => '#ffffff',
        'extendedProps' => array(
            'professeur' => $c['prof_nom'] . ' ' . $c['prof_prenoms'],
            'matiere' => $c['matiere_nom'],
            'salle' => $c['salle']
        )
    );
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

        <!-- Calendrier -->
        <div id="calendar"></div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />

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
        locale: 'fr',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?= json_encode($events) ?>,
        eventClick: function(info) {
            var event = info.event;
            var details = `
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">${event.title}</h5>
                        <p class="card-text">
                            <strong>Professeur:</strong> ${event.extendedProps.professeur}<br>
                            <strong>Matière:</strong> ${event.extendedProps.matiere}<br>
                            <strong>Salle:</strong> ${event.extendedProps.salle || 'Non spécifiée'}<br>
                            <strong>Horaires:</strong> ${formatDate(event.start)} - ${formatDate(event.end)}
                        </p>
                    </div>
                </div>
            `;
            
            // Utiliser Bootstrap modal ou alert pour afficher les détails
            alert(event.title + '\n\n' + 
                  'Professeur: ' + event.extendedProps.professeur + '\n' +
                  'Matière: ' + event.extendedProps.matiere + '\n' +
                  'Salle: ' + (event.extendedProps.salle || 'Non spécifiée'));
        }
    });
    calendar.render();
});

function formatDate(date) {
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<?php require_once "footer.php"; ?>