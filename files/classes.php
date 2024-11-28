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
$error="";
?>

<head>
    <link href="../css/classes.css" rel="stylesheet" />
</head>

<section>
    <div class="titre_classes">
        <h2>
            Gestion des classes
        </h2>
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
                                $error = "Le nom de la classe doit être remplie.";
                            } else {
                                // Vérifier si l'email existe déjà dans la base de données
                                $sql = "SELECT * FROM class WHERE name = :name";
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':name', $name_classe);
                                $stmt->execute();
                                
                                if ($stmt->rowCount() > 0) {
                                    $error = "Cette classe existe déjà.";
                                } else {           
                                    // Préparation de la requête d'insertion
                                    $sql = "INSERT INTO class (name) VALUES (:name)";
                                    $stmt = $pdo->prepare($sql);
                        
                                    // Liaison des paramètres
                                    $stmt->bindParam(':name', $name_classe);
                        
                                    // Exécution de la requête
                                    if ($stmt->execute()) {
                                        // Redirection après l'inscription réussie
                                        $error = "Nouvelle classe enregistrer.";
                                    } else {
                                        $error = "Erreur lors de l'enregistrement de la classe. Veuillez réessayer.";
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
                    <?php if ($error): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Enregistrement</button>
                </form>
            </details>
        </div>

        <div class="blocs_classes">
            <details>
                <summary>
                    <h4>Modifier une classe</h4>
                </summary>
                <div>
                    ok2
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

        <div class="blocs_classes">
            <details>
                <summary>
                    <h4>Supprimer une classe</h4>
                </summary>
                <div>
                    ok4
                </div>
            </details>
        </div>
    </div>  
</section>

<?php
  include "footer.php";
?>