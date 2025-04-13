<?php
/**
 * Page pour visualiser les signatures
 * Permet aux professeurs et élèves de voir les signatures
 */

// Démarrage de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['prof', 'eleve'])) {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=web_formation', 'root', 'AulrrpTCD7Tk2nJ55H4v');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur BDD (voir_signature): " . $e->getMessage());
    exit("Erreur de connexion à la base de données.");
}

// Récupération des informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Vérification que l'ID de signature est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Aucune signature spécifiée.";
    header("Location: " . ($user_role === 'prof' ? 'signature_prof.php' : 'signature_eleve.php'));
    exit();
}

$signature_id = intval($_GET['id']);

// Récupération des données de la signature
try {
    if ($user_role === 'prof') {
        // Les professeurs peuvent voir toutes les signatures des cours qu'ils enseignent
        $stmt = $pdo->prepare("SELECT s.*, u.nom, u.prenoms, c.titre as cours_titre, c.professeur_id
                              FROM sign s
                              JOIN users u ON s.user_id = u.id
                              JOIN cours c ON s.cours_id = c.id
                              WHERE s.id = :id AND c.professeur_id = :prof_id");
        $stmt->execute([
            ':id' => $signature_id,
            ':prof_id' => $user_id
        ]);
    } else {
        // Les élèves ne peuvent voir que leurs propres signatures
        $stmt = $pdo->prepare("SELECT s.*, u.nom, u.prenoms, c.titre as cours_titre
                              FROM sign s
                              JOIN users u ON s.user_id = u.id
                              JOIN cours c ON s.cours_id = c.id
                              WHERE s.id = :id AND s.user_id = :user_id");
        $stmt->execute([
            ':id' => $signature_id,
            ':user_id' => $user_id
        ]);
    }
    
    $signature = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$signature) {
        $_SESSION['error'] = "Signature non trouvée ou vous n'avez pas les droits pour y accéder.";
        header("Location: " . ($user_role === 'prof' ? 'signature_prof.php' : 'signature_eleve.php'));
        exit();
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération de la signature: " . $e->getMessage());
    $_SESSION['error'] = "Erreur lors de la récupération de la signature.";
    header("Location: " . ($user_role === 'prof' ? 'signature_prof.php' : 'signature_eleve.php'));
    exit();
}

// Inclure le header approprié selon le rôle
if ($user_role === 'prof') {
    include 'header_prof.php';
} else {
    include 'header_eleve.php';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisation de signature</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        .signature-container {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 15px;
            margin-bottom: 1rem;
            background-color: #fff;
        }
        
        .signature-image {
            max-width: 100%;
            height: auto;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Visualisation de signature</h1>
                <p class="lead">
                    <?= htmlspecialchars($signature['cours_titre']) ?>
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?= $user_role === 'prof' ? 'signature_prof.php' : 'signature_eleve.php' ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">Détails de la signature</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Élève :</strong> <?= htmlspecialchars($signature['nom'] . ' ' . $signature['prenoms']) ?></p>
                        <p><strong>Cours :</strong> <?= htmlspecialchars($signature['cours_titre']) ?></p>
                        <p><strong>Date de signature :</strong> <?= date('d/m/Y H:i', strtotime($signature['date_signature'])) ?></p>
                        <p><strong>Statut :</strong> 
                            <?php if ($signature['signed'] == 1): ?>
                                <span class="badge bg-success">Présent</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Absent</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="signature-container">
                            <h5>Signature :</h5>
                            <?php if (!empty($signature['signature_data'])): ?>
                                <img src="<?= $signature['signature_data'] ?>" alt="Signature" class="signature-image">
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    Aucune signature disponible.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php require_once "footer.php"; ?>
