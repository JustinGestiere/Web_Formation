<?php
// ======== INITIALISATION DE LA SESSION ET VÉRIFICATION DE CONNEXION ========
session_start(); // Démarre la session pour gérer les utilisateurs connectés

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Variable pour stocker les messages d'erreur ou de succès
$message = "";

// Récupération du message depuis la session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Effacer le message après l'avoir affiché
}

// Connexion à la base de données
require_once 'bdd.php';

// ======== TRAITEMENT DES FORMULAIRES ========
// Traitement de la création d'une classe
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name']) && !isset($_POST['submit'])) {
    // Récupération et nettoyage du nom de classe
    $name_classe = securiser($_POST['name']);

    // Validation: vérifier que le champ n'est pas vide
    if (empty($name_classe)) {
        $_SESSION['message'] = "Le nom de la classe doit être rempli.";
    } else {
        // Vérification de l'existence de la classe dans la base
        $sql = "SELECT * FROM classes WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name_classe);
        $stmt->execute();
        
        // Si la classe existe déjà
        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "Cette classe existe déjà.";
        } else {           
            // Insertion de la nouvelle classe dans la base de données
            $sql = "INSERT INTO classes (name) VALUES (:name)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name_classe);
            
            // Exécution et vérification de la requête
            if ($stmt->execute()) {
                $_SESSION['message'] = "Nouvelle classe enregistrée.";
            } else {
                $_SESSION['message'] = "Erreur lors de l'enregistrement de la classe. Veuillez réessayer.";
            }
        }
    }
    header("Location: classes.php");
    exit();
}

// Traitement de la suppression d'une classe
if (isset($_POST['submit']) && isset($_POST['classe_id']) && !empty($_POST['classe_id'])) {
    // Conversion en entier pour s'assurer que c'est un nombre
    $classe_id = intval($_POST['classe_id']);
    
    try {
        // Préparation et exécution de la requête de suppression
        $sql = "DELETE FROM classes WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$classe_id])) {
            $_SESSION['message'] = "Classe supprimée avec succès.";
        } else {
            $_SESSION['message'] = "Erreur lors de la suppression.";
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression de la classe : " . $e->getMessage());
        $_SESSION['message'] = "Une erreur est survenue lors de la suppression de la classe.";
    }
    
    header("Location: classes.php");
    exit();
}

// ======== FONCTIONS UTILITAIRES ========
/**
 * Sécurise les données entrées par l'utilisateur
 * @param string $data Donnée à nettoyer
 * @return string Donnée nettoyée
 */
function securiser($data) {
    $data = trim($data); // Supprime les espaces en début et fin
    $data = stripslashes($data); // Supprime les antislashs
    $data = htmlspecialchars($data); // Convertit les caractères spéciaux en entités HTML
    return $data;
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

?>
<head>
    <link href="../css/classes.css" rel="stylesheet" /> <!-- Chargement du style CSS -->
</head>

<section>
    <!-- Titre principal de la page -->
    <div class="titre_classes">
        <h1>Gestion des classes</h1>
    </div>

    <div class="page_classes">
        
        <!-- ======== BLOC CRÉATION DE CLASSE ======== -->
        <div class="blocs_classes"> 
            <details>
                <summary><h4>Créer une classe</h4></summary>
                <form method="post" class="p-4 border border-light rounded">

                    <div class="form-group">
                        <label for="name">Nom de la classe :</label>
                        <input type="text" placeholder="Nom de la classe" class="form-control" id="name" name="name" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Enregistrement</button>
                </form>
            </details>
        </div>

        <!-- ======== BLOC MODIFICATION DE CLASSE ======== -->
        <div class="blocs_classes">
            <details>
                <summary><h4>Modifier les classes</h4></summary>
                <form method="GET" action="modifier_classe.php" class="p-4 border border-light rounded">
                    <label for="classe_id">Choisissez une classe à modifier :</label>
                    <select name="id" id="classe_id" required>
                        <option value="">-- Sélectionnez une classe --</option>
                        <?php
                        // Récupération de toutes les classes pour le menu déroulant
                        $sql = "SELECT id, name FROM classes ORDER BY name";
                        $stmt = $pdo->query($sql);
                        $classes_list = $stmt->fetchAll();
                        
                        // Affichage des options du menu déroulant
                        if (count($classes_list) > 0) {
                            foreach ($classes_list as $classe) {
                                echo "<option value='" . htmlspecialchars($classe['id']) . "'>" . htmlspecialchars($classe['name']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>Aucune classe disponible</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </form>
            </details>
        </div>

        <!-- ======== BLOC VISUALISATION DES CLASSES ======== -->
        <div class="blocs_classes">
            <details>
                <summary><h4>Voir les classes</h4></summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        try {
                            // Récupération des classes par ordre alphabétique
                            $sql = "SELECT name FROM classes ORDER BY name";
                            $stmt = $pdo->query($sql);
                            $names = $stmt->fetchAll();
                            $namesCount = count($names);
                        } catch (PDOException $e) {
                            // Gestion des erreurs de base de données
                            error_log("Erreur lors de la récupération des classes : " . $e->getMessage());
                            $names = [];
                            $namesCount = 0;
                        }

                        // Affichage des classes ou message si aucune classe
                        if ($namesCount > 0) {
                            foreach ($names as $name) {
                                echo "<li>" . htmlspecialchars($name["name"]) . "</li>";
                            }
                        } else {
                            echo "<p>Aucune classe trouvée.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <!-- ======== BLOC SUPPRESSION DE CLASSE ======== -->
        <div class="blocs_classes">
            <details>
                <summary><h4>Supprimer les classes</h4></summary>
                <div class="liste_classe">
                    <form method="post" class="p-4 border border-light rounded">
                        <label for="classe">Choisissez une classe :</label>
                        <select name="classe_id" id="classe" required>
                            <option value="">-- Sélectionnez une classe --</option>
                            <?php
                            // Récupération de toutes les classes pour le menu déroulant
                            $sql = "SELECT id, name FROM classes ORDER BY name";
                            $stmt = $pdo->query($sql);
                            $classes = $stmt->fetchAll();

                            // Affichage des options du menu déroulant
                            if (count($classes) > 0) {
                                foreach ($classes as $classe) {
                                    echo "<option value='" . htmlspecialchars($classe['id']) . "'>" . htmlspecialchars($classe['name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>Aucune classe disponible</option>";
                            }
                            ?>
                        </select>
                        <button id="supprimer" type="submit" name="submit">Supprimer</button>
                    </form>


                </div>
            </details>
        </div>
    </div>

    <!-- ======== AFFICHAGE DES MESSAGES ======== -->
    <div class="message">
        <?php if (isset($message) && $message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php
  include "footer.php"; // Inclusion du pied de page
?>