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

// Récupérer la classe de l'élève
$stmt = $pdo->prepare("SELECT classe_id FROM users WHERE id = :eleve_id");
$stmt->execute(['eleve_id' => $eleve_id]);
$eleve = $stmt->fetch();

if (!$eleve) {
    die("Élève non trouvé");
}

// Récupérer les cours de la classe avec leur statut de signature
$query = "SELECT c.*, cl.name as classe_nom, m.name as matiere_nom, p.nom as prof_nom, p.prenoms as prof_prenoms,
          CASE WHEN s.id IS NOT NULL THEN 
            CASE WHEN EXISTS (
                SELECT 1 FROM sign 
                WHERE classe_id = c.class_id 
                AND user_id = :eleve_id 
                AND DATE(date_signature) = DATE(c.date_debut)
            ) THEN 'signed' 
            ELSE 'to_sign' 
            END
          ELSE NULL 
          END as signature_status
          FROM cours c 
          INNER JOIN classes cl ON c.class_id = cl.id
          INNER JOIN matieres m ON c.matiere_id = m.id
          INNER JOIN users p ON c.professeur_id = p.id
          LEFT JOIN sign s ON c.id = (
              SELECT sign.classe_id 
              FROM sign 
              WHERE sign.classe_id = c.class_id 
              AND DATE(sign.date_signature) = DATE(c.date_debut)
              LIMIT 1
          )
          WHERE c.class_id = :classe_id";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'classe_id' => $eleve['classe_id'],
    'eleve_id' => $eleve_id
]);
$cours = $stmt->fetchAll();

// Préparer les données pour le calendrier
$events = array();
foreach ($cours as $c) {
    // Définir la couleur en fonction du statut
    $color = '#6c757d'; // Gris par défaut
    if ($c['signature_status'] === 'to_sign') {
        $color = '#ffc107'; // Jaune pour à signer
    } elseif ($c['signature_status'] === 'signed') {
        $color = '#28a745'; // Vert pour signé
    }

    $events[] = array(
        'id' => $c['id'],
        'title' => $c['titre'] . ' - ' . $c['matiere_nom'],
        'start' => $c['date_debut'],
        'end' => $c['date_fin'],
        'backgroundColor' => $color,
        'borderColor' => $color,
        'textColor' => '#ffffff',
        'extendedProps' => array(
            'signature_status' => $c['signature_status'],
            'professeur' => $c['prof_nom'] . ' ' . $c['prof_prenoms']
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

        <div class="row">
            <div class="col-md-8">
                <h1>Mon emploi du temps</h1>
                <!-- Calendrier -->
                <div id="calendar" class="mb-4"></div>
            </div>
            <div class="col-md-4">
                <div id="signature-details" class="mt-4" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Détails du cours</h5>
                            <small id="cours-info"></small>
                        </div>
                        <div class="card-body">
                            <p><strong>Professeur:</strong> <span id="prof-info"></span></p>
                            <div id="signature-actions">
                                <!-- Le formulaire de signature sera injecté ici -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Légende</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <div>
                                <span class="badge bg-warning">&nbsp;</span> À signer
                            </div>
                            <div>
                                <span class="badge bg-success">&nbsp;</span> Signé
                            </div>
                            <div>
                                <span class="badge bg-secondary">&nbsp;</span> Pas encore disponible
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            
            // Mettre à jour les informations du cours
            document.getElementById('cours-info').textContent = event.title;
            document.getElementById('prof-info').textContent = event.extendedProps.professeur;
            
            let html = '';
            if (event.extendedProps.signature_status === 'to_sign') {
                html = `
                    <form action="enregistrer_signature.php" method="POST">
                        <input type="hidden" name="cours_id" value="${event.id}">
                        <div class="form-group">
                            <label for="signature">Votre signature :</label>
                            <canvas id="signature-pad" class="border" width="100%" height="200"></canvas>
                            <input type="hidden" name="signature_data" id="signature_data">
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="clearSignature()">Effacer</button>
                            <button type="submit" class="btn btn-primary" onclick="saveSignature(event)">Signer</button>
                        </div>
                    </form>
                `;
            } else if (event.extendedProps.signature_status === 'signed') {
                html = '<div class="alert alert-success">Vous avez déjà signé ce cours</div>';
            } else {
                html = '<div class="alert alert-secondary">La signature n\'est pas encore disponible</div>';
            }
            
            document.getElementById('signature-actions').innerHTML = html;
            document.getElementById('signature-details').style.display = 'block';
            
            // Initialiser le pad de signature si nécessaire
            if (event.extendedProps.signature_status === 'to_sign') {
                initSignaturePad();
            }
        }
    });
    calendar.render();
});

// Signature pad
let signaturePad = null;

function initSignaturePad() {
    const canvas = document.getElementById('signature-pad');
    canvas.width = canvas.offsetWidth;
    canvas.height = 200;
    
    signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)'
    });
}

function clearSignature() {
    if (signaturePad) {
        signaturePad.clear();
    }
}

function saveSignature(e) {
    e.preventDefault();
    if (signaturePad && !signaturePad.isEmpty()) {
        document.getElementById('signature_data').value = signaturePad.toDataURL();
        e.target.form.submit();
    } else {
        alert('Veuillez signer avant de soumettre');
    }
}
</script>

<!-- SignaturePad JS -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<?php require_once "footer.php"; ?>
