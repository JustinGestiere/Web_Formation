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
    $data = trim($data); // Supprime les espaces en début et fin
    $data = stripslashes($data); // Supprime les antislashs
    $data = htmlspecialchars($data); // Convertit les caractères spéciaux en entités HTML
    return $data;
}

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ======== TRAITEMENT DES FORMULAIRES ========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Création d'un cours
    if (isset($_POST['titre'])) {
        try {
            // Récupération des données du formulaire avec vérification
            $titre = securiser($_POST['titre']);
            $description = isset($_POST['description']) ? securiser($_POST['description']) : '';
            $date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
            $date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
            $professeur_id = isset($_POST['professeur_id']) ? intval($_POST['professeur_id']) : 0;
            $class_id = isset($_POST['classes_id']) ? intval($_POST['classes_id']) : 0;
            $matiere_id = isset($_POST['matiere_id']) ? intval($_POST['matiere_id']) : 0;

            // Débogage des valeurs reçues
            error_log("Données de création reçues - Titre: $titre, Date début: $date_debut, Date fin: $date_fin, Prof ID: $professeur_id, Classe ID: $classes_id, Matière ID: $matiere_id");

            // Vérification que les champs obligatoires sont remplis
            if (empty($titre) || empty($date_debut) || empty($date_fin) || 
                empty($professeur_id) || empty($class_id) || empty($matiere_id)) {
                $_SESSION['message'] = "Tous les champs obligatoires doivent être remplis.";
            } else {
                // Vérification de l'unicité du titre du cours
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE titre = :titre");
                $stmt->execute([':titre' => $titre]);
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    $_SESSION['message'] = "Un cours avec ce titre existe déjà. Veuillez en choisir un autre.";
                } else {
                    // Insertion du nouveau cours
                    $stmt = $pdo->prepare("
                        INSERT INTO cours (titre, description, date_debut, date_fin, professeur_id, class_id, matiere_id)
                        VALUES (:titre, :description, :date_debut, :date_fin, :professeur_id, :class_id, :matiere_id)
                    ");
                    
                    $params = [
                        ':titre' => $titre,
                        ':description' => $description,
                        ':date_debut' => $date_debut,
                        ':date_fin' => $date_fin,
                        ':professeur_id' => $professeur_id,
                        ':class_id' => $class_id,
                        ':matiere_id' => $matiere_id
                    ];
                    
                    error_log("Paramètres d'insertion: " . print_r($params, true));
                    
                    $result = $stmt->execute($params);
                    
                    if ($result) {
                        $_SESSION['message'] = "Le cours a été créé avec succès.";
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        error_log("Erreur SQL: " . print_r($errorInfo, true));
                        $_SESSION['message'] = "Une erreur est survenue lors de la création du cours: " . $errorInfo[2];
                    }
                }
                header("Location: cours.php");
                exit();
            }
        } catch (Exception $e) {
            error_log("Exception lors de la création du cours : " . $e->getMessage());
            $_SESSION['message'] = "Une erreur est survenue lors de la création du cours: " . $e->getMessage();
            header("Location: cours.php");
            exit();
        }
    }
    
    // Suppression d'un cours
    if (isset($_POST['supprimer_id']) && !empty($_POST['supprimer_id'])) {
        $supprimer_id = intval($_POST['supprimer_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM cours WHERE id = :id");
            $stmt->execute([':id' => $supprimer_id]);
            $_SESSION['message'] = "Le cours a été supprimé avec succès.";
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du cours : " . $e->getMessage());
            $_SESSION['message'] = "Une erreur est survenue lors de la suppression du cours.";
        }
        header("Location: cours.php");
        exit();
    }
}

// Récupération du message depuis la session
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Effacer le message après l'avoir affiché
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

// Récupération des données pour les formulaires
$professeurs = $pdo->query("SELECT id, nom, prenoms FROM users WHERE roles = 'prof'")->fetchAll(PDO::FETCH_ASSOC);
$classes = $pdo->query("SELECT id, name FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$matieres = $pdo->query("SELECT id, name FROM matieres")->fetchAll(PDO::FETCH_ASSOC);
$cours_list = $pdo->query("SELECT id, titre FROM cours")->fetchAll(PDO::FETCH_ASSOC);

// Récupération de la liste des cours pour l'affichage
try {
    $sql = "SELECT titre FROM cours ORDER BY titre";
    $stmt = $pdo->query($sql);
    $cours_names = $stmt->fetchAll();
    $cours_count = count($cours_names);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des cours : " . $e->getMessage());
    $cours_names = [];
    $cours_count = 0;
}
?>

<section>
    <!-- Titre principal de la page -->
    <div class="titre_cours">
        <h1>Gestion des cours</h1>
    </div>

    <div class="page_cours"> 
        <!-- BLOC 1: CRÉATION DE COURS -->
        <div class="blocs_cours">
            <details>
                <summary>
                    <h4>Créer un cours</h4>
                </summary>
                <form method="POST" action="">
                    <!-- Champ titre avec exemple de format -->
                    <label for="titre">Titre :</label>
                    <input type="text" id="titre" name="titre" placeholder="Ex: Développement Web - HTML/CSS" required>
                    <br><br>

                    <!-- Champ description -->
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="4" placeholder="Description détaillée du cours..."></textarea>
                    <br><br>

                    <!-- Champs date et heure de début et fin -->
                    <label for="date_debut">Date et heure de début :</label>
                    <input type="datetime-local" id="date_debut" name="date_debut" required>
                    <br><br>

                    <label for="date_fin">Date et heure de fin :</label>
                    <input type="datetime-local" id="date_fin" name="date_fin" required>
                    <br><br>

                    <!-- Sélection du professeur -->
                    <label for="professeur_id">Professeur :</label>
                    <select id="professeur_id" name="professeur_id" required>
                        <option value="">-- Sélectionner un professeur --</option>
                        <?php foreach ($professeurs as $professeur): ?>
                            <option value="<?php echo $professeur['id']; ?>">
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
                            <option value="<?php echo $classe['id']; ?>">
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
                            <option value="<?php echo $matiere['id']; ?>">
                                <?php echo htmlspecialchars($matiere['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <!-- Bouton de soumission -->
                    <button type="submit">Créer le cours</button>
                </form>
            </details>
        </div>

        <!-- BLOC 2: MODIFICATION DE COURS -->
        <div class="blocs_cours">
            <details>
                <summary>
                    <h4>Modifier un cours</h4>
                </summary>
                <!-- Formulaire de sélection du cours à modifier -->
                <form method="GET" action="modifier_cours.php">
                    <label for="cours_id">Sélectionner un cours à modifier :</label>
                    <select id="cours_id" name="id" required>
                        <option value="">-- Sélectionner un cours --</option>
                        <?php foreach ($cours_list as $cours_item): ?>
                            <option value="<?php echo $cours_item['id']; ?>">
                                <?php echo htmlspecialchars($cours_item['titre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>
                    <button type="submit">Modifier le cours</button>
                </form>
            </details>
        </div>

        <!-- BLOC 3: VISUALISATION DES COURS -->
        <div class="blocs_cours">
            <details>
                <summary>
                    <h4>Voir les cours</h4>
                </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        // Affichage de la liste des cours ou message si aucun cours
                        if ($cours_count > 0) {
                            foreach ($cours_names as $name) {
                                echo "<li>" . htmlspecialchars($name["titre"]) . "</li>";
                            }
                        } else {
                            echo "<p>Aucun cours trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <!-- BLOC 4: SUPPRESSION DE COURS -->
        <div class="blocs_cours">
            <details>
                <summary><h4>Supprimer un cours</h4></summary>
                <!-- Formulaire de suppression -->
                <form method="POST" action="">
                    <label for="supprimer_id">Sélectionner un cours à supprimer :</label>
                    <select id="supprimer_id" name="supprimer_id" required>
                        <option value="">-- Sélectionner un cours --</option>
                        <?php foreach ($cours_list as $cours_item): ?>
                            <option value="<?php echo $cours_item['id']; ?>">
                                <?php echo htmlspecialchars($cours_item['titre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>
                    <button type="submit">Supprimer le cours</button>
                </form>
            </details>
        </div>
    </div>

    <!-- Zone d'affichage des messages (succès/erreur) -->
    <div class="message">
        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>  
</section>

<?php
// Inclusion du pied de page
include "footer.php";
?>
