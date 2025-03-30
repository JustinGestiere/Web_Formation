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

// Récupérer les cours du professeur avec leur statut de signature
$professeur_id = $_SESSION['user_id'];

$query = "SELECT c.*, cl.name as classe_nom, m.name as matiere_nom,
          CASE WHEN s.id IS NOT NULL THEN true ELSE false END as is_signed
          FROM cours c 
          INNER JOIN classes cl ON c.class_id = cl.id
          INNER JOIN matieres m ON c.matiere_id = m.id
          LEFT JOIN sign s ON c.id = (
              SELECT sign.classe_id 
              FROM sign 
              WHERE sign.classe_id = c.class_id 
              AND DATE(sign.date_signature) = DATE(c.date_debut)
              LIMIT 1
          )
          WHERE c.professeur_id = :professeur_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['professeur_id' => $professeur_id]);
$cours = $stmt->fetchAll();

// Sélectionner les élèves d'un cours
if (isset($_POST['cours_id'])) {
    $cours_id = $_POST['cours_id'];
    
    $stmt = $pdo->prepare("SELECT u.* FROM users u
                          INNER JOIN cours c ON u.classe_id = c.class_id
                          WHERE c.id = :cours_id AND u.roles = 'eleve'");
    $stmt->execute(['cours_id' => $cours_id]);
    $eleves = $stmt->fetchAll();
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
            'is_signed' => $c['is_signed']
        )
    );
}
?>

<div class="main-content">
    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <h1>Signatures des élèves</h1>
        
        <!-- Calendrier -->
        <div id="calendar" class="mb-4"></div>

        <!-- Modal pour les élèves -->
        <div class="modal fade" id="elevesModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sélectionner les élèves présents</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modalContent">
                        <!-- Le contenu sera chargé dynamiquement -->
                    </div>
                </div>
            </div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="cours_id">Sélectionner un cours:</label>
                <select name="cours_id" id="cours_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Choisir un cours</option>
                    <?php if (!empty($cours)) : ?>
                        <?php foreach ($cours as $c) : ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($_POST['cours_id']) && $_POST['cours_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['titre'] . ' - ' . $c['matiere_nom'] . ' - ' . $c['classe_nom'] . ' (' . date('d/m/Y H:i', strtotime($c['date_debut'])) . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </form>

        <?php if (isset($eleves) && !empty($eleves)) : ?>
            <form method="POST" action="signature_traitement.php" class="mt-4">
                <input type="hidden" name="cours_id" value="<?= htmlspecialchars($cours_id) ?>">
                <h2>Élèves présents</h2>
                <div class="form-group">
                    <?php foreach ($eleves as $eleve) : ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="eleves_present[]" value="<?= $eleve['id'] ?>" id="eleve_<?= $eleve['id'] ?>">
                            <label class="form-check-label" for="eleve_<?= $eleve['id'] ?>"><?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenoms']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer pour signature</button>
            </form>
        <?php elseif (isset($_POST['cours_id'])) : ?>
            <div class="alert alert-info mt-4">
                Aucun élève trouvé dans ce cours.
            </div>
        <?php endif; ?>
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
            if (event.extendedProps.is_signed) {
                alert('Ce cours a déjà été signé');
                return;
            }
            
            // Charger les élèves pour ce cours
            fetch('get_eleves.php?cours_id=' + event.id)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalContent').innerHTML = html;
                    $('#elevesModal').modal('show');
                });
        }
    });
    calendar.render();
});
</script>

<?php require_once "footer.php"; ?>
