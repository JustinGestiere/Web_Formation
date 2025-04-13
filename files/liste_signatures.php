<?php
/**
 * Page pour visualiser la liste des signatures d'un cours
 * Permet aux professeurs de voir toutes les signatures des élèves pour un cours
 */

// Démarrage de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification des droits d'accès (réservé aux professeurs)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=web_formation', 'root', 'AulrrpTCD7Tk2nJ55H4v');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur BDD (liste_signatures): " . $e->getMessage());
    exit("Erreur de connexion à la base de données.");
}

// Récupération des informations du professeur connecté
$professeur_id = $_SESSION['user_id'];

// Vérification que l'ID du cours est fourni
if (!isset($_GET['cours_id']) || empty($_GET['cours_id'])) {
    $_SESSION['error'] = "Aucun cours spécifié.";
    header("Location: signature_prof.php");
    exit();
}

$cours_id = intval($_GET['cours_id']);

// Vérification que le cours appartient bien au professeur connecté
$stmt = $pdo->prepare("SELECT c.*, cl.name as classe_nom, m.name as matiere_nom
                      FROM cours c
                      INNER JOIN classes cl ON c.class_id = cl.id
                      INNER JOIN matieres m ON c.matiere_id = m.id
                      WHERE c.id = :cours_id AND c.professeur_id = :professeur_id");
$stmt->execute([
    ':cours_id' => $cours_id,
    ':professeur_id' => $professeur_id
]);
$cours = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cours) {
    $_SESSION['error'] = "Cours non trouvé ou vous n'avez pas les droits pour y accéder.";
    header("Location: signature_prof.php");
    exit();
}

// Récupération des signatures pour ce cours
$stmt = $pdo->prepare("SELECT s.*, u.nom, u.prenoms
                      FROM sign s
                      JOIN users u ON s.user_id = u.id
                      WHERE s.cours_id = :cours_id
                      ORDER BY u.nom, u.prenoms");
$stmt->execute([':cours_id' => $cours_id]);
$signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inclusion du header
include 'header_prof.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des signatures</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        .signature-thumbnail {
            max-width: 100px;
            max-height: 60px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Liste des signatures</h1>
                <p class="lead">
                    Cours : <?= htmlspecialchars($cours['titre']) ?><br>
                    Classe : <?= htmlspecialchars($cours['classe_nom']) ?><br>
                    Matière : <?= htmlspecialchars($cours['matiere_nom']) ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="signature_prof.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

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

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">Signatures des élèves</h2>
            </div>
            <div class="card-body">
                <?php if (count($signatures) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Élève</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Signature</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($signatures as $s): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['nom'] . ' ' . $s['prenoms']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($s['date_signature'])) ?></td>
                                        <td>
                                            <?php if ($s['signed'] == 1): ?>
                                                <span class="badge bg-success">Présent</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($s['signature_data'])): ?>
                                                <img src="<?= $s['signature_data'] ?>" alt="Signature" class="signature-thumbnail" onclick="openSignatureModal('<?= htmlspecialchars(addslashes($s['signature_data'])) ?>', '<?= htmlspecialchars($s['nom'] . ' ' . $s['prenoms']) ?>')">
                                            <?php else: ?>
                                                <span class="text-muted">Non disponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="voir_signature.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Aucune signature n'a été enregistrée pour ce cours.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher la signature en grand -->
    <div class="modal fade" id="signatureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Signature de <span id="studentName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="signatureImage" src="" alt="Signature" style="max-width: 100%; border: 1px solid #dee2e6;">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function openSignatureModal(signatureData, studentName) {
        document.getElementById('signatureImage').src = signatureData;
        document.getElementById('studentName').textContent = studentName;
        
        const modal = new bootstrap.Modal(document.getElementById('signatureModal'));
        modal.show();
    }
    </script>
</body>
</html>

<?php require_once "footer.php"; ?>
