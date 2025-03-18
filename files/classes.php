<?php
session_start(); // Démarre la session si elle n'est pas déjà démarrée, pour gérer les utilisateurs connectés

// Inclusion du header approprié en fonction du rôle de l'utilisateur
if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            include "header_admin.php"; // Inclure le header pour un utilisateur avec le rôle 'admin'
            break;
        case 'prof':
            include "header_prof.php"; // Inclure le header pour un utilisateur avec le rôle 'prof'
            break;
        default:
            include "header.php"; // Inclure le header par défaut pour les autres utilisateurs
            break;
    }
} else {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit(); // Terminer le script pour éviter que le reste du code s'exécute
}

$message = ""; // Initialisation d'une variable pour afficher des messages d'erreur ou de succès

?>
<head>
    <link href="../css/classes.css" rel="stylesheet" /> <!-- Lien vers le fichier CSS pour styliser la page -->
</head>

<section>
    <div class="titre_classes">
        <h1>Gestion des classes</h1> <!-- Titre principal de la page -->
    </div>

    <div class="page_classes">
        
        <!-- Bloc pour créer une nouvelle classe -->
        <div class="blocs_classes"> 
            <details>
                <summary><h4>Créer une classe</h4></summary>
                <form method="post" class="p-4 border border-light rounded">
                    <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Si la requête est de type POST (soumission du formulaire)
                            // Récupérer et nettoyer le nom de la classe
                            $name_classe = trim($_POST['name']);
                        
                            // Validation des données
                            if (empty($name_classe)) {
                                $message = "Le nom de la classe doit être rempli."; // Message d'erreur si le champ est vide
                            } else {
                                // Vérifier si une classe avec ce nom existe déjà dans la base de données
                                $sql = "SELECT * FROM class WHERE name = :name";
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':name', $name_classe);
                                $stmt->execute();
                                
                                if ($stmt->rowCount() > 0) {
                                    $message = "Cette classe existe déjà."; // Message si la classe existe déjà
                                } else {           
                                    // Si la classe n'existe pas, on peut l'ajouter
                                    $sql = "INSERT INTO class (name) VALUES (:name)";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->bindParam(':name', $name_classe);
                                    
                                    // Exécution de la requête pour insérer la classe
                                    if ($stmt->execute()) {
                                        $message = "Nouvelle classe enregistrée."; // Message de succès
                                    } else {
                                        $message = "Erreur lors de l'enregistrement de la classe. Veuillez réessayer."; // Message d'erreur
                                    }
                                }
                            }
                        }
                    ?>
                    <div class="form-group">
                        <label for="name">Nom de la classe :</label>
                        <input type="text" placeholder="Nom de la classe" class="form-control" id="name" name="name" required>
                    </div>

                    <!-- Affichage des messages d'erreur ou de succès -->
                    <?php if ($message): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">Enregistrement</button>
                </form>
            </details>
        </div>

        <!-- Bloc pour modifier une classe -->
        <div class="blocs_classes">
            <details>
                <summary><h4>Modifier les classes</h4></summary>
                <div>
                    <?php
                    // Récupérer toutes les classes de la base de données
                    $stmt = $pdo->query("SELECT id, name FROM class");

                    // Afficher chaque classe dans un formulaire pour modification
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <form method="POST" action="modifier_class.php" style="margin-bottom: 10px;">
                            <label for="classe_<?php echo $row['id']; ?>">Classe :</label>
                            <input type="text" id="classe_<?php echo $row['id']; ?>" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit">Enregistrer</button>
                        </form>
                    <?php endwhile; ?>
                </div>
            </details>
        </div>

        <!-- Bloc pour voir toutes les classes -->
        <div class="blocs_classes">
            <details>
                <summary><h4>Voir les classes</h4></summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        try {
                            // Récupérer toutes les classes par ordre alphabétique
                            $sql = "SELECT name FROM class ORDER BY name";
                            $stmt = $pdo->query($sql);
                            $names = $stmt->fetchAll();
                            $namesCount = count($names);
                        } catch (PDOException $e) {
                            // Gestion des erreurs de récupération des classes
                            error_log("Erreur lors de la récupération des classes : " . $e->getMessage());
                            $names = [];
                            $namesCount = 0;
                        }

                        if ($namesCount > 0) {
                            foreach ($names as $name) {
                                echo "<li>" . htmlspecialchars($name["name"]) . "</li>";
                            }
                        } else {
                            echo "<p>Aucune classe trouvée.</p>"; // Message si aucune classe n'est trouvée
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <!-- Bloc pour supprimer une classe -->
        <div class="blocs_classes">
            <details>
                <summary><h4>Supprimer les classes</h4></summary>
                <div class="liste_classe">
                    <form method="post" class="p-4 border border-light rounded">
                        <label for="classe">Choisissez une classe :</label>
                        <select name="classe_id" id="classe" required>
                            <option value="">-- Sélectionnez une classe --</option>
                            <?php
                            // Récupérer toutes les classes pour les afficher dans un menu déroulant
                            $sql = "SELECT id, name FROM class";
                            $stmt = $pdo->query($sql);
                            $classes = $stmt->fetchAll(); // Récupérer les classes

                            if (count($classes) > 0) {
                                // Afficher chaque classe comme option dans le menu
                                foreach ($classes as $classe) {
                                    echo "<option value='" . htmlspecialchars($classe['id']) . "'>" . htmlspecialchars($classe['name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>Aucune classe disponible</option>"; // Si aucune classe n'est trouvée
                            }
                            ?>
                        </select>
                        <button id="supprimer" type="submit" name="submit">Supprimer</button>
                    </form>

                    <?php
                    // Traitement de la suppression de la classe
                    if (isset($_POST['submit'])) {
                        if (isset($_POST['classe_id']) && !empty($_POST['classe_id'])) {
                            $classe_id = intval($_POST['classe_id']);
                            $sql = "DELETE FROM class WHERE id = ?";
                            $stmt = $pdo->prepare($sql);
                            
                            if ($stmt->execute([$classe_id])) {
                                $_SESSION['message'] = "Classe supprimée avec succès."; // Message de succès
                            } else {
                                $_SESSION['message'] = "Erreur lors de la suppression."; // Message d'erreur
                            }
                        } else {
                            $_SESSION['message'] = "Aucune classe sélectionnée."; // Message si aucune classe n'est sélectionnée
                        }
                    }
                    ?>
                </div>
            </details>
        </div>
    </div>

    <!-- Affichage des messages d'erreur ou de succès -->
    <div class="message">
        <?php if (isset($message) && $message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php
  include "footer.php"; // Inclusion du footer à la fin de la page
?>
