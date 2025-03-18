<?php
// ======== INITIALISATION DE LA SESSION ET VÉRIFICATION DE CONNEXION ========
session_start(); // Démarre la session pour gérer les utilisateurs connectés

// Vérification du rôle de l'utilisateur et inclusion du header approprié
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
} else {
    // Redirection vers la page de connexion si non connecté
    header("Location: login.php");
    exit(); // Arrêt du script pour éviter tout traitement supplémentaire
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

// Récupération des messages de session
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Suppression du message après récupération
}
?>

<head>
    <link href="../css/matieres.css" rel="stylesheet" /> <!-- Chargement du style CSS -->
</head>

<section>
    <!-- Titre principal de la page -->
    <div class="titre_matieres">
        <h1>Gestion des matières</h1>
    </div>

    <div class="page_matieres">
        <!-- ======== BLOC CRÉATION DE MATIÈRE ======== -->
        <div class="blocs_matieres">
            <details>
                <summary><h4>Créer une matière</h4></summary>
                <form method="post" class="p-4 border border-light rounded">
                    <?php
                    // Traitement du formulaire de création de matière
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
                        // Récupération et nettoyage du nom de matière
                        $name_matiere = securiser($_POST['name']);
                        
                        // Validation: vérifier que le champ n'est pas vide
                        if (empty($name_matiere)) {
                            $message = "Le nom de la matière doit être rempli.";
                        } else {
                            // Vérification de l'existence de la matière dans la base
                            $sql = "SELECT * FROM matieres WHERE name = :name";
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':name', $name_matiere);
                            $stmt->execute();
                    
                            // Si la matière existe déjà
                            if ($stmt->rowCount() > 0) {
                                $message = "Cette matière existe déjà.";
                            } else {
                                // Insertion de la nouvelle matière dans la base de données
                                $sql = "INSERT INTO matieres (name) VALUES (:name)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':name', $name_matiere);
                    
                                // Exécution et vérification de la requête
                                if ($stmt->execute()) {
                                    $_SESSION['message'] = "Nouvelle matière enregistrée avec succès.";
                                    header("Location: matieres.php"); // Redirection pour éviter les soumissions multiples
                                    exit();
                                } else {
                                    $message = "Erreur lors de l'enregistrement de la matière. Veuillez réessayer.";
                                }
                            }
                        }
                    }
                    ?>
                    <div class="form-group">
                        <label for="name">Nom de la matière :</label>
                        <input type="text" placeholder="Mathématiques" class="form-control" id="name" name="name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrement</button>
                </form>
            </details>
        </div>

        <!-- ======== BLOC MODIFICATION DE MATIÈRE ======== -->
        <div class="blocs_matieres">
            <details>
                <summary><h4>Modifier les matières</h4></summary>
                <div>
                    <?php
                    // Récupération de toutes les matières pour modification
                    $stmt = $pdo->query("SELECT id, name FROM matieres ORDER BY name");

                    // Affichage d'un formulaire par matière
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <form method="POST" action="modifier_matiere.php" style="margin-bottom: 10px;">
                            <label for="matiere_<?php echo $row['id']; ?>">Matière :</label>
                            <input type="text" id="matiere_<?php echo $row['id']; ?>" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit">Enregistrer</button>
                        </form>
                    <?php endwhile; ?>
                </div>
            </details>
        </div>

        <!-- ======== BLOC VISUALISATION DES MATIÈRES ======== -->
        <div class="blocs_matieres">
            <details>
                <summary><h4>Voir les matières</h4></summary>
                <div class="liste_matieres">
                    <ul>
                        <?php
                        try {
                            // Récupération des matières par ordre alphabétique
                            $sql = "SELECT name FROM matieres ORDER BY name";
                            $stmt = $pdo->query($sql);
                            $matieres = $stmt->fetchAll();
                            $matiereCount = count($matieres);
                        } catch (PDOException $e) {
                            // Gestion des erreurs de base de données
                            error_log("Erreur lors de la récupération des matières : " . $e->getMessage());
                            $matieres = [];
                            $matiereCount = 0;
                        }

                        // Affichage des matières ou message si aucune matière
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

        <!-- ======== BLOC SUPPRESSION DE MATIÈRE ======== -->
        <div class="blocs_matieres">
            <details>
                <summary><h4>Supprimer les matières</h4></summary>
                <div class="liste_matiere">
                    <form method="post" class="p-4 border border-light rounded">
                        <label for="matiere">Choisissez une matière :</label>
                        <select name="matiere_id" id="matiere" required>
                            <option value="">-- Sélectionnez une matière --</option>
                            <?php
                            // Récupération de toutes les matières pour le menu déroulant
                            $sql = "SELECT id, name FROM matieres ORDER BY name";
                            $stmt = $pdo->query($sql);
                            $matieres = $stmt->fetchAll();
                            
                            // Affichage des options du menu déroulant
                            if (count($matieres) > 0) {
                                foreach ($matieres as $matiere) {
                                    echo "<option value='" . htmlspecialchars($matiere['id']) . "'>" . htmlspecialchars($matiere['name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>Aucune matière disponible</option>";
                            }
                            ?>
                        </select>
                        <button id="supprimer" type="submit" name="submit">Supprimer</button>
                    </form>

                    <?php
                    // Traitement de la suppression d'une matière
                    if (isset($_POST['submit'])) {
                        if (isset($_POST['matiere_id']) && !empty($_POST['matiere_id'])) {
                            // Conversion en entier pour s'assurer que c'est un nombre
                            $matiere_id = intval($_POST['matiere_id']);
                            
                            // Préparation et exécution de la requête de suppression
                            $sql = "DELETE FROM matieres WHERE id = ?";
                            $stmt = $pdo->prepare($sql);
                    
                            if ($stmt->execute([$matiere_id])) {
                                $_SESSION['message'] = "Matière supprimée avec succès.";
                                // Redirection pour appliquer le message de session
                                header("Location: matieres.php");
                                exit();
                            } else {
                                $_SESSION['message'] = "Erreur lors de la suppression.";
                            }
                        } else {
                            $_SESSION['message'] = "Aucune matière sélectionnée.";
                        }
                    }
                    ?>
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
    
    <!-- Inclusion des scripts JavaScript -->
    <script src="../js/matieres.js"></script>
</section>

<?php
  include "footer.php"; // Inclusion du pied de page
?>