<?php
session_start(); // Démarre la session si ce n'est pas déjà fait
include('bdd.php');

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
        <h1>
            Gestion des utilisateurs
        </h1>
    </div>

    <div class="page_utilisateurs"> 
        <div class="blocs_utilisateurs"> <!-- Créer les utilisateurs -->
            <details>
                <summary>
                    <h4>Créer un utilisateur</h4>
                </summary>
            </details>
        </div>

        <div class="blocs_utilisateurs"> <!-- Modifer les utilisateurs -->
            <details>
                    <summary>
                        <h4>Modifier les utilisateurs</h4>
                    </summary>
                    <div>
                    </div>
                </details>
        </div>

        <div class="blocs_utilisateurs"> <!-- Voir les utilisateurs -->
            <details>
                <summary>
                    <?php
                        try {
                            // Récupérer les utilisateurs
                            $sql = "SELECT nom, prenoms, roles FROM users ORDER BY nom, prenoms, roles";
                            $stmt = $pdo->query($sql);
                            $names = $stmt->fetchAll();
                            $namesCount = count($names);
                        } catch (PDOException $e) {
                            error_log("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
                            $names = [];
                            $namesCount = 0;
                        }
                    ?>
                    <p>
                        <h4>Voir les utilisateurs</h4>
                    </p>
                </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        if ($namesCount > 0) {
                            foreach ($names as $name) {
                                echo "<li>" . htmlspecialchars($name["nom"]) . " " . htmlspecialchars($name["prenoms"]) . " : " . htmlspecialchars($name["roles"]) ."</li>";
                            }
                        } else {
                            echo "<p>Aucun utilisateurs trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <div class="blocs_utilisateurs"> <!-- Supprimer les utilisateurs -->
            <details>
                <summary>
                    <h4>Supprimer les utilisateurs</h4>
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