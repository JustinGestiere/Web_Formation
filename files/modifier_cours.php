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

// Récupération des données pour les formulaires
$professeurs = $pdo->query("SELECT id, nom, prenoms FROM users WHERE roles = 'prof'")->fetchAll(PDO::FETCH_ASSOC);
$classes = $pdo->query("SELECT id, name FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$matieres = $pdo->query("SELECT id, name FROM matieres")->fetchAll(PDO::FETCH_ASSOC);

// Vérification qu'un ID de cours est fourni
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    $_SESSION['message'] = "Aucun cours sélectionné pour modification.";
    header("Location: cours.php");
    exit();
}

// Récupération des informations du cours à modifier
$cours_id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
$stmt = $pdo->prepare("SELECT * FROM cours WHERE id = :id");
$stmt->execute([':id' => $cours_id]);
$cours = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cours) {
    $_SESSION['message'] = "Le cours demandé n'existe pas.";
    header("Location: cours.php");
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
echo '<link href="../css/cours.css" rel="stylesheet" />';

// Définition de la fonction securiser si elle n'existe pas déjà
if (!function_exists('securiser')) {
    function securiser($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération des données du formulaire avec vérification
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $titre = isset($_POST['titre']) ? securiser($_POST['titre']) : '';
        $description = isset($_POST['description']) ? securiser($_POST['description']) : '';
        $date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
        $date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
        $professeur_id = isset($_POST['professeur_id']) ? intval($_POST['professeur_id']) : 0;
        $classes_id = isset($_POST['classes_id']) ? intval($_POST['classes_id']) : 0;
        $matiere_id = isset($_POST['matiere_id']) ? intval($_POST['matiere_id']) : 0;

        // Débogage
        error_log("Données reçues - ID: $id, Titre: $titre, Date début: $date_debut, Date fin: $date_fin, Prof ID: $professeur_id, Classe ID: $classes_id, Matière ID: $matiere_id");

        // Vérification que les champs obligatoires sont remplis
        if (empty($titre) || empty($date_debut) || empty($date_fin) || 
            empty($professeur_id) || empty($classes_id) || empty($matiere_id)) {
            $message = "Tous les champs obligatoires doivent être remplis.";
        } else {
            // Vérification de l'unicité du titre (sauf pour le cours actuel)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE titre = :titre AND id != :id");
            $stmt->execute([':titre' => $titre, ':id' => $id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $message = "Un cours avec ce titre existe déjà. Veuillez en choisir un autre.";
            } else {
                // Mise à jour du cours dans la base de données
                $stmt = $pdo->prepare("UPDATE cours SET titre = :titre, description = :description, date_debut = :date_debut, date_fin = :date_fin, professeur_id = :professeur_id, classes_id = :classes_id, matiere_id = :matiere_id WHERE id = :id");
                
                $params = [
                    ':titre' => $titre,
                    ':description' => $description,
                    ':date_debut' => $date_debut,
                    ':date_fin' => $date_fin,
                    ':professeur_id' => $professeur_id,
                    ':classes_id' => $classes_id,
                    ':matiere_id' => $matiere_id,
                    ':id' => $id
                ];
                
                error_log("Paramètres de mise à jour: " . print_r($params, true));
                
                $result = $stmt->execute($params);

                if ($result) {
                    $_SESSION['message'] = "Le cours a été mis à jour avec succès.";
                    header("Location: cours.php");
                    exit();
                } else {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Erreur SQL: " . print_r($errorInfo, true));
                    $message = "Une erreur est survenue lors de la mise à jour du cours: " . $errorInfo[2];
                }
            }
        }
    } catch (Exception $e) {
        error_log("Exception lors de la mise à jour du cours : " . $e->getMessage());
        $message = "Une erreur est survenue lors de la mise à jour du cours: " . $e->getMessage();
    }
}

// Formatage des dates pour les champs datetime-local
try {
    // Vérification que les dates existent et sont au bon format
    if (isset($cours['date_debut']) && !empty($cours['date_debut'])) {
        $date_debut_formatted = str_replace(' ', 'T', $cours['date_debut']);
    } else {
        $date_debut_formatted = date('Y-m-d\TH:i');
    }
    
    if (isset($cours['date_fin']) && !empty($cours['date_fin'])) {
        $date_fin_formatted = str_replace(' ', 'T', $cours['date_fin']);
    } else {
        $date_fin_formatted = date('Y-m-d\TH:i', strtotime('+1 hour'));
    }
    
    error_log("Dates formatées - Début: $date_debut_formatted, Fin: $date_fin_formatted");
} catch (Exception $e) {
    error_log("Erreur lors du formatage des dates : " . $e->getMessage());
    $date_debut_formatted = date('Y-m-d\TH:i');
    $date_fin_formatted = date('Y-m-d\TH:i', strtotime('+1 hour'));
}
?>

<section>
    <div class="titre_cours">
        <h1>Modifier un cours</h1>
    </div>

    <div class="page_cours">
        <div class="blocs_cours">
            <form method="POST" action="">
                <!-- Champ titre avec exemple de format -->
                <label for="titre">Titre :</label>
                <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($cours['titre']); ?>" required>
                <br><br>

                <!-- Champ description -->
                <label for="description">Description :</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($cours['description']); ?></textarea>
                <br><br>

                <!-- Champs date et heure de début et fin -->
                <label for="date_debut">Date et heure de début :</label>
                <input type="datetime-local" id="date_debut" name="date_debut" value="<?php echo $date_debut_formatted; ?>" required>
                <br><br>

                <label for="date_fin">Date et heure de fin :</label>
                <input type="datetime-local" id="date_fin" name="date_fin" value="<?php echo $date_fin_formatted; ?>" required>
                <br><br>

                <!-- Sélection du professeur -->
                <label for="professeur_id">Professeur :</label>
                <select id="professeur_id" name="professeur_id" required>
                    <option value="">-- Sélectionner un professeur --</option>
                    <?php foreach ($professeurs as $professeur): ?>
                        <option value="<?php echo $professeur['id']; ?>" <?php echo ($professeur['id'] == $cours['professeur_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($professeur['nom'] . ' ' . $professeur['prenoms']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>

                <!-- Sélection de la classe -->
                <label for="classes_id">Classe :</label>
                <select id="classes_id" name="classes_id" required>
                    <option value="">-- Sélectionner une classe --</option>
                    <?php foreach ($classes as $classe): ?>
                        <?php 
                        // Débogage de la sélection de classe
                        $selected = ($classe['id'] == $cours['classes_id']) ? 'selected' : '';
                        error_log("Classe ID: " . $classe['id'] . ", Cours classe_id: " . $cours['classes_id'] . ", Selected: " . $selected);
                        ?>
                        <option value="<?php echo $classe['id']; ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($classe['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>

                <!-- Sélection de la matière -->
                <label for="matiere_id">Matière :</label>
                <select id="matiere_id" name="matiere_id" required>
                    <option value="">-- Sélectionner une matière --</option>
                    <?php foreach ($matieres as $matiere): ?>
                        <option value="<?php echo $matiere['id']; ?>" <?php echo ($matiere['id'] == $cours['matiere_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($matiere['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>

                <!-- ID caché pour identifier le cours à modifier -->
                <input type="hidden" name="id" value="<?php echo $cours['id']; ?>">
                
                <!-- Boutons d'action -->
                <button type="submit">Enregistrer les modifications</button>
                <a href="cours.php" class="btn btn-secondary">Annuler</a>
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
