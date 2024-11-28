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
    <link href="../css/matieres.css" rel="stylesheet" />
</head>

<section>
    <div class="titre_matieres">
        <h2>
            Gestion des matières
        </h2>
    </div>

    <div class="page_matieres">
        <div class="blocs_matieres"> <!-- Créer les matières -->
            <details>
                <summary>
                    <h4>Créer une matière</h4>
                </summary>
                <form method="post" class="p-4 border border-light rounded">
                    <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            // Récupération des données du formulaire et nettoyage
                            $name_matiere = trim($_POST['name']);
                        
                            // Validation des entrées
                            if (empty($name_matiere)) {
                                $error = "Le nom de la matiere doit être remplie.";
                            } else {
                                // Vérifier si l'email existe déjà dans la base de données
                                $sql = "SELECT * FROM matieres WHERE name = :name";
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':name', $name_matiere);
                                $stmt->execute();
                                
                                if ($stmt->rowCount() > 0) {
                                    $error = "Cette matière existe déjà.";
                                } else {           
                                    // Préparation de la requête d'insertion
                                    $sql = "INSERT INTO matieres (name) VALUES (:name)";
                                    $stmt = $pdo->prepare($sql);
                        
                                    // Liaison des paramètres
                                    $stmt->bindParam(':name', $name_matiere);
                        
                                    // Exécution de la requête
                                    if ($stmt->execute()) {
                                        // Redirection après l'inscription réussie
                                        $error = "Nouvelle matière enregistrer.";
                                    } else {
                                        $error = "Erreur lors de l'enregistrement de la matière. Veuillez réessayer.";
                                    }
                                }
                            }
                        }
                    ?>
                    <div class="form-group">
                        <label for="name">Nom de la matière :</label>
                        <input type="text" placeholder="Mathématiques" class="form-control" id="name" name="name" required>
                    </div>

                    <!-- Afficher les erreurs ici -->
                    <?php if ($error): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">Enregistrement</button>
                </form>
            </details>
        </div>

        <div class="blocs_matieres"> <!-- Modifier les matières -->
            <details>
                <summary>
                    <h4>Modifier une matière</h4>   
                </summary>
                <div>
                    ok2
                </div>
            </details>
        </div>

        <div class="blocs_matieres"> <!-- Voir les matières -->
            <details>
                <summary>
                    <?php
                        try {
                            // Récupérer les matiere
                            $sql = "SELECT name FROM matieres ORDER BY name";
                            $stmt = $pdo->query($sql);
                            $matiere = $stmt->fetchAll();
                            $matiereCount = count($matiere);
                        } catch (PDOException $e) {
                            error_log("Erreur lors de la récupération des matières : " . $e->getMessage());
                            $matiere = [];
                            $matiereCount = 0;
                        }
                    ?>
                    <p>
                        <h4>Voir les matières</h4>
                    </p>
                </summary>
                <div class="liste_matieres">
                    <ul>
                        <?php
                        if ($matiereCount > 0) {
                            foreach ($matiere as $matieres) {
                                echo "<li>" . htmlspecialchars($matieres["name"]) . "</li>";
                            }
                        } else {
                            echo "<p>Aucune matiere trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <div class="blocs_matieres"> <!-- Supprimer les matières -->
    <details>
        <summary>
            <h4>Supprimer les matières</h4>
        </summary>
        <div class="liste_matieres">
            <form method="post" class="p-4 border border-light rounded">
                <label for="matiere">Choisissez une matière :</label>
                <select name="matiere_id" id="matiere" required>
                    <option value="">-- Sélectionnez une matière --</option>
                    <?php
                    // Requête pour récupérer les matières
                    $sql = "SELECT id, name FROM matieres";
                    $stmt = $pdo->query($sql);
                    $matieres = $stmt->fetchAll(); // Récupération des matières sous forme associative
                    
                    if (count($matieres) > 0) {
                        // Afficher chaque matière comme option
                        foreach ($matieres as $matiere) {
                            echo "<option value='" . htmlspecialchars($matiere['id']) . "'>" . htmlspecialchars($matiere['name']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Aucune matière disponible</option>";
                    }
                    ?>
                </select>
                <button type="submit" name="submit">Supprimer</button>
            </form>

            <?php
                // Traitement de la suppression (si le formulaire est soumis)
                if (isset($_POST['submit'])) {
                    if (isset($_POST['matiere_id']) && !empty($_POST['matiere_id'])) {
                        $matiere_id = intval($_POST['matiere_id']); // Sécurisation de l'ID

                        // Requête pour supprimer la matière
                        $sql = "DELETE FROM matieres WHERE id = ?";
                        $stmt = $pdo->prepare($sql);

                        if ($stmt->execute([$matiere_id])) {
                            $message = "Matière supprimée avec succès.";
                        } else {
                            $message = "Erreur lors de la suppression.";
                        }
                    } else {
                        $message = "Aucune matière sélectionnée.";
                    }

                    // Affichage du message
                    if (isset($message)) {
                        echo "<p>$message</p>";
                    }
                }
            ?>
        </div>
    </details>
</div>

    </div>  
</section>

<?php
  include "footer.php";
?>