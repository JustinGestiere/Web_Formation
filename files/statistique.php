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
?>

<head>
    <link href="../css/statistique.css" rel="stylesheet" />
</head>

<section>
    <div>
        <div class="titre_statistiques">
            <h2>
                Chiffres clés Web_Formation
            </h2>
        </div>
        <div class="statistiques">
            <details class="blocs_statistiques">
                <summary>Classes</summary>
                    <p>En cours de développement</p>
            </details>



            <details class="blocs_statistiques">
                <summary>Cours</summary>
                    <p>En cours de développement</p>
            </details>



            <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les élèves
                        $sql = "SELECT * FROM users WHERE roles = 'eleve'";
                        $stmt = $pdo->query($sql);
                        $eleves = $stmt->fetchAll();
                        $elevesCount = count($eleves);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des élèves : " . $e->getMessage());
                        $eleves = [];
                        $elevesCount = 0;
                    }
                ?>
                <p>
                    <h3>Nombre d'élèves ( <?php echo $elevesCount; ?> )</h3>
                </p>
            </summary>
                <ul>
                    <?php
                    if ($elevesCount > 0) {
                        foreach ($eleves as $eleve) {
                            echo "<li>" . htmlspecialchars($eleve["nom"]) . " " . htmlspecialchars($eleve["prenoms"]) . " (" . htmlspecialchars($eleve["emails"]) . ")</li>";
                        }
                    } else {
                        echo "<p>Aucun élève trouvé.</p>";
                    }
                    ?>
                </ul>
            </details>



            <details class="blocs_statistiques">
                <summary>Professeurs</summary>
                    <p>En cours de développement</p>
            </details>



            <details class="blocs_statistiques">
                <summary>Present</summary>
                    <p>En cours de développement</p>
            </details>



            <details class="blocs_statistiques">
                <summary>Absent</summary>
                    <p>En cours de développement</p>
            </details>
        </div>
    </div>
</section>

<?php
  include "footer.php";
?>