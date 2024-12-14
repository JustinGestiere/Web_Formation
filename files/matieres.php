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

// Vérifiez s'il y a un message en session et assignez-le à $message, puis supprimez-le de la session.
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Supprimez le message de la session après l'affichage
}

?>

<head>
    <link href="../css/matieres.css" rel="stylesheet" />
</head>

<section>
    <div class="titre_matieres">
        <h1>
            Gestion des matières
        </h1>
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
                            $name_matiere = isset($_POST['name']) ? trim($_POST['name']) : '';
                            if (empty($name_matiere)) {
                                $message = "Le nom de la matière doit être rempli.";
                            } else {
                                $sql = "SELECT * FROM matieres WHERE name = :name";
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':name', $name_matiere);
                                $stmt->execute();
                        
                                if ($stmt->rowCount() > 0) {
                                    $message = "Cette matière existe déjà.";
                                } else {
                                    $sql = "INSERT INTO matieres (name) VALUES (:name)";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->bindParam(':name', $name_matiere);
                        
                                    if ($stmt->execute()) {
                                        $_SESSION['message'] = "Nouvelle matière enregistrée avec succès.";
                                        header("Location: matieres.php");
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
                    <p>
                        <h4>Voir les matières</h4>
                    </p>
                </summary>
                <div class="liste_matieres">
                    <ul>
                        <?php
                            try {
                                // Récupérer les matieres
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
                                $matiere_id = intval($_POST['matiere_id']);
                                $sql = "DELETE FROM matieres WHERE id = ?";
                                $stmt = $pdo->prepare($sql);
                        
                                if ($stmt->execute([$matiere_id])) {
                                    $_SESSION['message'] = "Matière supprimée avec succès.";
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