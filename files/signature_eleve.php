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

// Récupérer les cours avec leur statut de signature
$query = "SELECT 
            c.*,
            m.name as matiere_nom,
            p.nom as prof_nom,
            p.prenoms as prof_prenoms,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM sign 
                    WHERE classe_id = c.class_id 
                    AND user_id = :eleve_id 
                    AND DATE(date_signature) = DATE(c.date_debut)
                ) THEN 'signed'
                WHEN EXISTS (
                    SELECT 1 FROM sign 
                    WHERE classe_id = c.class_id 
                    AND DATE(date_signature) = DATE(c.date_debut)
                ) THEN 'to_sign'
                ELSE 'not_available'
            END as signature_status
          FROM cours c
          INNER JOIN matieres m ON c.matiere_id = m.id
          INNER JOIN users p ON c.professeur_id = p.id
          WHERE c.class_id = :classe_id
          ORDER BY c.date_debut DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'classe_id' => $eleve['classe_id'],
    'eleve_id' => $eleve_id
]);
$cours = $stmt->fetchAll();
?>

<div class="main-content">
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Mes signatures</h1>
                <p class="lead">
                    <?= htmlspecialchars($eleve['prenoms'] . ' ' . $eleve['nom']) ?> - 
                    Classe : <?= htmlspecialchars($eleve['classe_nom']) ?>
                </p>
            </div>
            <div class="col-md-6 text-md-right">
                <a href="eleve.php" class="btn btn-primary">
                    <i class="fas fa-calendar"></i> Voir l'emploi du temps
                </a>
            </div>
        </div>

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

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Cours</th>
                        <th>Matière</th>
                        <th>Professeur</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cours as $c): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($c['date_debut'])) ?></td>
                            <td><?= htmlspecialchars($c['titre']) ?></td>
                            <td><?= htmlspecialchars($c['matiere_nom']) ?></td>
                            <td><?= htmlspecialchars($c['prof_nom'] . ' ' . $c['prof_prenoms']) ?></td>
                            <td>
                                <?php if ($c['signature_status'] === 'signed'): ?>
                                    <span class="badge bg-success">Signé</span>
                                <?php elseif ($c['signature_status'] === 'to_sign'): ?>
                                    <span class="badge bg-warning">À signer</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Non disponible</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['signature_status'] === 'to_sign'): ?>
                                    <button class="btn btn-primary btn-sm" onclick="showSignatureModal(<?= $c['id'] ?>)">
                                        <i class="fas fa-signature"></i> Signer
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de signature -->
<div class="modal fade" id="signatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Signer le cours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="signatureForm" action="enregistrer_signature.php" method="POST">
                    <input type="hidden" name="cours_id" id="cours_id">
                    <div class="form-group">
                        <label>Votre signature :</label>
                        <canvas id="signature-pad" class="border w-100" height="200"></canvas>
                        <input type="hidden" name="signature_data" id="signature_data">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="clearSignature()">Effacer</button>
                <button type="button" class="btn btn-primary" onclick="saveSignature()">Valider</button>
            </div>
        </div>
    </div>
</div>

<!-- SignaturePad JS -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
let signaturePad;
let currentModal;

function showSignatureModal(coursId) {
    document.getElementById('cours_id').value = coursId;
    currentModal = new bootstrap.Modal(document.getElementById('signatureModal'));
    currentModal.show();
    
    // Initialiser le pad de signature
    const canvas = document.getElementById('signature-pad');
    canvas.width = canvas.offsetWidth;
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

function saveSignature() {
    if (signaturePad && !signaturePad.isEmpty()) {
        document.getElementById('signature_data').value = signaturePad.toDataURL();
        document.getElementById('signatureForm').submit();
    } else {
        alert('Veuillez signer avant de valider');
    }
}
</script>

<?php require_once "footer.php"; ?>