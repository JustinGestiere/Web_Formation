<?php
session_start(); // Démarre la session si ce n'est pas déjà fait

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Connexion à la base de données
try {
    require_once 'bdd.php';
} catch (Exception $e) {
    error_log("Erreur BDD: " . $e->getMessage());
    exit("Erreur de connexion à la base de données.");
}

// Vérification qu'un ID de matière est fourni
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    $_SESSION['message'] = "Aucune matière sélectionnée pour modification.";
    header("Location: matieres.php");
    exit();
}

// Récupération des informations de la matière à modifier
$matiere_id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
$stmt = $pdo->prepare("SELECT * FROM matieres WHERE id = :id");
$stmt->execute([':id' => $matiere_id]);
$matiere = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$matiere) {
    $_SESSION['message'] = "La matière demandée n'existe pas.";
    header("Location: matieres.php");
    exit();
}

// Inclusion du header approprié selon le rôle
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
switch ($role) {
    case 'admin':
        include "header_admin.php";
        break;
    case 'prof':
        include "header_prof.php";
        break;
    default:
        include "header.php";
        break;
}

// Ajout de la feuille de style CSS spécifique
echo '<link href="../css/matieres.css" rel="stylesheet" />';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $name = trim($_POST['name']);

        if (empty($name)) {
            $message = "Le nom de la matière ne peut pas être vide.";
        } else {
            try {
                // Vérification si le nom existe déjà pour une autre matière
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM matieres WHERE name = :name AND id != :id");
                $check_stmt->execute([':name' => $name, ':id' => $id]);
                $exists = $check_stmt->fetchColumn();

                if ($exists) {
                    $message = "Une matière avec ce nom existe déjà.";
                } else {
                    // Mise à jour de la matière dans la base de données
                    $stmt = $pdo->prepare("UPDATE matieres SET name = :name WHERE id = :id");
                    $stmt->execute([
                        ':name' => $name,
                        ':id' => $id
                    ]);

                    $_SESSION['message'] = "La matière a été mise à jour avec succès.";
                    header("Location: matieres.php");
                    exit();
                }
            } catch (PDOException $e) {
                error_log("Erreur lors de la mise à jour de la matière : " . $e->getMessage());
                $message = "Une erreur est survenue lors de la mise à jour de la matière.";
            }
        }
    }
?>

<section>
    <div class="titre_matieres">
        <h1>Modifier une matière</h1>
    </div>

    <div class="page_matieres">
        <div class="blocs_matieres">
            <form method="POST" action="" class="p-4 border border-light rounded">
                <div class="form-group">
                    <label for="name">Nom de la matière :</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($matiere['name']); ?>" required>
                </div>
                
                <!-- ID caché pour identifier la matière à modifier -->
                <input type="hidden" name="id" value="<?php echo $matiere['id']; ?>">
                
                <!-- Boutons d'action -->
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="matieres.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>

    <!-- Zone d'affichage des messages (succès/erreur) -->
    <?php if (isset($message) && !empty($message)): ?>
    <div class="message">
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
    <?php endif; ?>
</section>

<?php
// Inclusion du pied de page
include "footer.php";