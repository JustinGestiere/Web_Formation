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
    <link href="../css/utilisateurs.css" rel="stylesheet" />
</head>

<section>
    <div class="titre_utilisateurs">
        <h2>
            Gestion des matières
        </h2>
    </div>

    <div class="page_utilisateurs">
        <div class="blocs_utilisateurs">
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

        <div class="blocs_utilisateurs">
            <details>
                <summary>
                    <h4>Modifier une matière</h4>
                </summary>
                <div>
                    ok2
                </div>
            </details>
        </div>

        <div class="blocs_utilisateurs">
            <details>
                <summary>
                    <h4>Voir une matière</h4>
                </summary>
                <div>
                    ok3
                </div>
            </details>
        </div>

        <div class="blocs_utilisateurs">
            <details>
                <summary>
                    <h4>Supprimer une matière</h4>
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