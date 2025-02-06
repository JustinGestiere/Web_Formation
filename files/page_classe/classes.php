<?php
session_start(); // Démarre la session si ce n'est pas déjà fait

// Inclure le header approprié en fonction du rôle
if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            include "header_admin.php"; // Si rôle admin
            break;
        case 'prof':
            include "header_prof.php"; // Si rôle prof
            break;
        default:
            include "header.php"; // Sinon le header par défaut
            break;
    }
} else {
    // Si l'utilisateur n'est pas connecté, on peut rediriger vers login
    header("Location: login.php");
    exit();
}
$message="";
?>

<head>
    <link href="../css/classes.css" rel="stylesheet" />
</head>

<section>
    <div class="titre_classes">
        <h1>
            Gestion des classes
        </h1>
    </div>

    <div class="page_classes"> 
        <div class="blocs_classes"> <!-- Créer les classes -->
            <details>
                <summary>
                    <h4>Créer une classe</h4>
                </summary>
                <form method="post" class="p-4 border border-light rounded">
                    <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            // Récupération des données du formulaire et nettoyage
                            $name_classe = trim($_POST['name']);
                        
                            // Validation des entrées
                            if (empty($name_classe)) {
                                $message = "Le nom de la classe doit être remplie.";
                            } else {
                                // Vérifier si l'email existe déjà dans la base de données
                                $sql = "SELECT * FROM class WHERE name = :name";
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':name', $name_classe);
                                $stmt->execute();
                                
                                if ($stmt->rowCount() > 0) {
                                    $message = "Cette classe existe déjà.";
                                } else {           
                                    // Préparation de la requête d'insertion
                                    $sql = "INSERT INTO class (name) VALUES (:name)";
                                    $stmt = $pdo->prepare($sql);
                        
                                    // Liaison des paramètres
                                    $stmt->bindParam(':name', $name_classe);
                        
                                    // Exécution de la requête
                                    if ($stmt->execute()) {
                                        // Redirection après l'inscription réussie
                                        $message = "Nouvelle classe enregistrer.";
                                    } else {
                                        $message = "Erreur lors de l'enregistrement de la classe. Veuillez réessayer.";
                                    }
                                }
                            }
                        }
                    ?>
                    <div class="form-group">
                        <label for="name">Nom de la classe :</label>
                        <input type="text" placeholder="Nom de la classe" class="form-control" id="name" name="name" required>
                    </div>
                    <!-- Afficher les erreurs ici -->
                    <?php if ($message): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Enregistrement</button>
                </form>
            </details>
        </div>

        <div class="blocs_classes"> <!-- Modifer les classes -->
            <details>
                    <summary>
                        <h4>Modifier les classes</h4>
                    </summary>
                    <div>
                        <?php
                        // Récupérer les classes
                        $stmt = $pdo->query("SELECT id, name FROM class");

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <form method="POST" action="modifier_class.php" style="margin-bottom: 10px;">
                                <label for="classe_<?php echo $row['id']; ?>">Classe :</label>
                                <input type="text" id="classe_<?php echo $row['id']; ?>" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit">Enregistrer</button>
                            </form>
                        <?php $message = "Classe modifier avec succès.";endwhile; ?>
                    </div>
                </details>
        </div>

        <div class="blocs_classes"> <!-- Voir les classes -->
            <details>
                <summary>
                    <?php
                        try {
                            // Récupérer les classes
                            $sql = "SELECT name FROM class ORDER BY name";
                            $stmt = $pdo->query($sql);
                            $names = $stmt->fetchAll();
                            $namesCount = count($names);
                        } catch (PDOException $e) {
                            error_log("Erreur lors de la récupération des classes : " . $e->getMessage());
                            $names = [];
                            $namesCount = 0;
                        }
                    ?>
                    <p>
                        <h4>Voir les classes</h4>
                    </p>
                </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        if ($namesCount > 0) {
                            foreach ($names as $name) {
                                echo "<li>" . htmlspecialchars($name["name"]) . "</li>";
                            }
                        } else {
                            echo "<p>Aucune classe trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <div class="blocs_classes"> <!-- Supprimer les classes -->
            <details>
                <summary>
                    <h4>Supprimer les classes</h4>
                </summary>
                <div class="liste_classe">
                    <form method="post" class="p-4 border border-light rounded">
                        <label for="classe">Choisissez une classe :</label>
                        <select name="classe_id" id="classe" required>
                            <option value="">-- Sélectionnez une classe --</option>
                            <?php
                            // Requête pour récupérer les classes
                            $sql = "SELECT id, name FROM class";
                            $stmt = $pdo->query($sql);
                            $classes = $stmt->fetchAll(); // Récupération des classes sous forme associative
                            
                            if (count($classes) > 0) {
                                // Afficher chaque classe comme option
                                foreach ($classes as $classe) {
                                    echo "<option value='" . htmlspecialchars($classe['id']) . "'>" . htmlspecialchars($classe['name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>Aucune matière disponible</option>";
                            }
                            ?>
                        </select>
                        <button id="supprimer" type="submit" name="submit">Supprimer</button>
                    </form>

                    <?php
                        // Traitement de la suppression (si le formulaire est soumis)
                        if (isset($_POST['submit'])) {
                            if (isset($_POST['classe_id']) && !empty($_POST['classe_id'])) {
                                $classe_id = intval($_POST['classe_id']);
                                $sql = "DELETE FROM class WHERE id = ?";
                                $stmt = $pdo->prepare($sql);
                        
                                if ($stmt->execute([$classe_id])) {
                                    $_SESSION['message'] = "Classe supprimée avec succès.";
                                } else {
                                    $_SESSION['message'] = "Erreur lors de la suppression.";
                                }
                            } else {
                                $_SESSION['message'] = "Aucune classe sélectionnée.";
                            }
                        }                        
                    ?>
                </div>
            </details>
        </div>
    </div>
    <div class="message">
        <!-- Afficher les erreurs ici -->
        <?php if (isset($message) && $message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>  
</section>

<?php
  include "footer.php";
?>