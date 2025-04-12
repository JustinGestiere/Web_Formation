<?php
// Démarrage de la session
session_start();

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

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Création d'une matière
    if (isset($_POST['name']) && !isset($_POST['submit'])) {
        $name_matiere = securiser($_POST['name']);
        
        if (empty($name_matiere)) {
            $_SESSION['message'] = "Le nom de la matière doit être rempli.";
        } else {
            // Vérification de l'existence de la matière
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM matieres WHERE name = :name");
            $stmt->execute([':name' => $name_matiere]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $_SESSION['message'] = "Cette matière existe déjà.";
            } else {
                // Insertion de la nouvelle matière
                try {
                    $stmt = $pdo->prepare("INSERT INTO matieres (name) VALUES (:name)");
                    $stmt->execute([':name' => $name_matiere]);
                    $_SESSION['message'] = "Nouvelle matière enregistrée avec succès.";
                } catch (PDOException $e) {
                    error_log("Erreur lors de la création de la matière : " . $e->getMessage());
                    $_SESSION['message'] = "Une erreur est survenue lors de l'enregistrement de la matière.";
                }
            }
        }
        header("Location: matieres.php");
        exit();
    }
    
    // Suppression d'une matière
    if (isset($_POST['submit']) && isset($_POST['matiere_id']) && !empty($_POST['matiere_id'])) {
        $matiere_id = intval($_POST['matiere_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM matieres WHERE id = ?");
            if ($stmt->execute([$matiere_id])) {
                $_SESSION['message'] = "Matière supprimée avec succès.";
            } else {
                $_SESSION['message'] = "Erreur lors de la suppression.";
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de la matière : " . $e->getMessage());
            $_SESSION['message'] = "Une erreur est survenue lors de la suppression de la matière.";
        }
        header("Location: matieres.php");
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

// Récupération de la liste des matières pour l'affichage
try {
    $sql = "SELECT id, name FROM matieres ORDER BY name";
    $stmt = $pdo->query($sql);
    $matieres = $stmt->fetchAll();
    $matiereCount = count($matieres);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des matières : " . $e->getMessage());
    $matieres = [];
    $matiereCount = 0;
}
?>

<head>
    <link href="../css/matieres.css" rel="stylesheet" />
</head>

<section>
    <!-- Titre principal de la page -->
    <div class="titre_matieres">
        <h1>Gestion des matières</h1>
    </div>

    <div class="page_matieres">
        <!-- BLOC 1: CRÉATION DE MATIÈRE -->
        <div class="blocs_matieres">
            <details>
                <summary><h4>Créer une matière</h4></summary>
                <form method="POST" action="" class="p-4 border border-light rounded">
                    <div class="form-group">
                        <label for="name">Nom de la matière :</label>
                        <input type="text" placeholder="Mathématiques" class="form-control" id="name" name="name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </details>
        </div>

        <!-- BLOC 2: MODIFICATION DE MATIÈRE -->
        <div class="blocs_matieres">
            <details>
                <summary><h4>Modifier les matières</h4></summary>
                <form method="GET" action="modifier_matiere.php" class="p-4 border border-light rounded">
                    <label for="matiere_id">Choisissez une matière à modifier :</label>
                    <select name="id" id="matiere_id" required>
                        <option value="">-- Sélectionnez une matière --</option>
                        <?php foreach ($matieres as $matiere): ?>
                            <option value="<?php echo $matiere['id']; ?>">
                                <?php echo htmlspecialchars($matiere['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </form>
            </details>
        </div>

        <!-- BLOC 3: VISUALISATION DES MATIÈRES -->
        <div class="blocs_matieres">
            <details>
                <summary><h4>Voir les matières</h4></summary>
                <div class="liste_matieres">
                    <ul>
                        <?php
                        // Affichage de la liste des matières ou message si aucune matière
                        if ($matiereCount > 0) {
                            foreach ($matieres as $matiere) {
                                echo "<li>" . htmlspecialchars($matiere["name"]) . "</li>";
                            }
                        } else {
                            echo "<p>Aucune matière trouvée.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <!-- BLOC 4: SUPPRESSION DE MATIÈRE -->
        <div class="blocs_matieres">
            <details>
                <summary><h4>Supprimer les matières</h4></summary>
                <div class="liste_matiere">
                    <form method="POST" action="" class="p-4 border border-light rounded">
                        <label for="matiere">Choisissez une matière :</label>
                        <select name="matiere_id" id="matiere" required>
                            <option value="">-- Sélectionnez une matière --</option>
                            <?php foreach ($matieres as $matiere): ?>
                                <option value="<?php echo $matiere['id']; ?>">
                                    <?php echo htmlspecialchars($matiere['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button id="supprimer" type="submit" name="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
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
