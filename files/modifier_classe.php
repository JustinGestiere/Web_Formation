<?php
// ======== INITIALISATION DE LA SESSION ET VÉRIFICATION DE CONNEXION ========
session_start(); // Démarre la session pour gérer les données utilisateur entre les pages

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

/**
 * Sécurise les données entrées par l'utilisateur
 * @param string $data Donnée à nettoyer
 * @return string Donnée nettoyée
 */
function securiser($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Vérification qu'un ID de classe est fourni
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    $_SESSION['message'] = "Aucune classe sélectionnée pour modification.";
    header("Location: classes.php");
    exit();
}

// Récupération des informations de la classe à modifier
$classe_id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = :id");
$stmt->execute([':id' => $classe_id]);
$classe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$classe) {
    $_SESSION['message'] = "La classe demandée n'existe pas.";
    header("Location: classes.php");
    exit();
}

// Inclusion du header approprié selon le rôle
if (isset($_SESSION['user_role'])) {
    // Utilisation d'un switch pour choisir le header selon le rôle
    switch ($_SESSION['user_role']) {
        case 'admin':
            include "header_admin.php"; // Header pour administrateurs
            break;
        case 'prof':
            include "header_prof.php"; // Header pour professeurs
            break;
        default:
            include "header.php"; // Header par défaut pour autres rôles
            break;
    }
}

// Ajout de la feuille de style CSS spécifique
echo '<link href="../css/classes.css" rel="stylesheet" />';

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = securiser($_POST['name']);

    if (empty($name)) {
        $message = "Le nom de la classe ne peut pas être vide.";
    } else {
        try {
            // Vérification si le nom existe déjà pour une autre classe
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE name = :name AND id != :id");
            $check_stmt->execute([':name' => $name, ':id' => $id]);
            $exists = $check_stmt->fetchColumn();

            if ($exists) {
                $message = "Une classe avec ce nom existe déjà.";
            } else {
                // Mise à jour de la classe dans la base de données
                $stmt = $pdo->prepare("UPDATE classes SET name = :name WHERE id = :id");
                $stmt->execute([
                    ':name' => $name,
                    ':id' => $id
                ]);

                $_SESSION['message'] = "La classe a été mise à jour avec succès.";
                header("Location: classes.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de la classe : " . $e->getMessage());
            $message = "Une erreur est survenue lors de la mise à jour de la classe.";
        }
    }
}
?>

<section>
    <div class="titre_classes">
        <h1>Modifier une classe</h1>
    </div>

    <div class="page_classes">
        <div class="blocs_classes">
            <form method="POST" action="" class="p-4 border border-light rounded">
                <div class="form-group">
                    <label for="name">Nom de la classe :</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($classe['name']); ?>" required>
                </div>
                
                <!-- ID caché pour identifier la classe à modifier -->
                <input type="hidden" name="id" value="<?php echo $classe['id']; ?>">
                
                <!-- Boutons d'action -->
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="classes.php" class="btn btn-secondary">Annuler</a>
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
?>
