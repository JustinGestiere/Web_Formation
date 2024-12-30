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
    <link href="../css/cours.css" rel="stylesheet" />
</head>

<section>
    <div class="titre_cours">
        <h1>
            Gestion des cours
        </h1>
    </div>

    <div class="page_cours"> 
        <div class="blocs_cours"> <!-- Créer les cours -->
            <details>
                <summary>
                    <h4>Créer une classe</h4>
                </summary>
            </details>
        </div>

        <div class="blocs_cours"> <!-- Modifer les cours -->
            <details>
                    <summary>
                        <h4>Modifier les cours</h4>
                    </summary>
                    <div>
                    </div>
                </details>
        </div>

        <div class="blocs_cours"> <!-- Voir les cours -->
            <details>
                <summary>
                    <?php
                        try {
                            // Récupérer les cours
                            $sql = "SELECT titre FROM cours ORDER BY titre";
                            $stmt = $pdo->query($sql);
                            $names = $stmt->fetchAll();
                            $namesCount = count($names);
                        } catch (PDOException $e) {
                            error_log("Erreur lors de la récupération des cours : " . $e->getMessage());
                            $names = [];
                            $namesCount = 0;
                        }
                    ?>
                    <p>
                        <h4>Voir les cours</h4>
                    </p>
                </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        if ($namesCount > 0) {
                            foreach ($names as $name) {
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

        <div class="blocs_cours"> <!-- Supprimer les cours -->
            <details>
                <summary>
                    <h4>Supprimer les cours</h4>
                </summary>
                
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