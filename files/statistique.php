<?php
session_start(); // Démarre la session si ce n'est pas déjà fait
require_once 'config.php';

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
            <h1>
                Chiffres clés Web_Formation
            </h1>
        </div>
        <div class="statistiques">
            <details class="blocs_statistiques">
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
                        <h4>Classes ( <?php echo $namesCount; ?> )</h4>
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



            <details class="blocs_statistiques">
                <summary>
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
                    <p>
                        <h4>Matières ( <?php echo $matiereCount; ?> )</h4>
                    </p>
                </summary>
                <div class="liste_statistiques">
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



            <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les élèves
                        $sql = "SELECT * FROM users WHERE roles = 'eleve' ORDER BY emails";
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
                    <h4>Élèves ( <?php echo $elevesCount; ?> )</h4>
                </p>
            </summary>
                <div class="liste_statistiques">
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
                </div>
            </details>



            <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les professeurs
                        $sql = "SELECT * FROM users WHERE roles = 'prof' ORDER BY emails";
                        $stmt = $pdo->query($sql);
                        $professeurs = $stmt->fetchAll();
                        $professeursCount = count($professeurs);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des professeurs : " . $e->getMessage());
                        $professeurs = [];
                        $professeursCount = 0;
                    }
                ?>
                <p>
                    <h4>Professeurs ( <?php echo $professeursCount; ?> )</h4>
                </p>
            </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        if ($professeursCount > 0) {
                            foreach ($professeurs as $prof) {
                                echo "<li>" . htmlspecialchars($prof["nom"]) . " " . htmlspecialchars($prof["prenoms"]) . " (" . htmlspecialchars($prof["emails"]) . ")</li>";
                            }
                        } else {
                            echo "<p>Aucun professeur trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>



            <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les visiteurs
                        $sql = "SELECT * FROM users WHERE roles = 'visiteur' ORDER BY emails";
                        $stmt = $pdo->query($sql);
                        $visiteurs = $stmt->fetchAll();
                        $visiteursCount = count($visiteurs);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des visiteurs : " . $e->getMessage());
                        $visiteurs = [];
                        $visiteursCount = 0;
                    }
                ?>
                <p>
                    <h4>Visiteur ( <?php echo $visiteursCount; ?> )</h4>
                </p>
            </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        if ($visiteursCount > 0) {
                            foreach ($visiteurs as $visiteur) {
                                echo "<li>" . htmlspecialchars($visiteur["nom"]) . " " . htmlspecialchars($visiteur["prenoms"]) . " (" . htmlspecialchars($visiteur["emails"]) . ")</li>";
                            }
                        } else {
                            echo "<p>Aucun visiteur trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>



            <details class="blocs_statistiques">
            <summary>
                <?php
                    try {
                        // Récupérer les admins
                        $sql = "SELECT * FROM users WHERE roles = 'admin' ORDER BY emails";
                        $stmt = $pdo->query($sql);
                        $administrateurs = $stmt->fetchAll();
                        $administrateursCount = count($administrateurs);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la récupération des administrateurs : " . $e->getMessage());
                        $administrateurs = [];
                        $administrateursCount = 0;
                    }
                ?>
                <p>
                    <h4>Admin ( <?php echo $administrateursCount; ?> )</h4>
                </p>
            </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        if ($administrateursCount > 0) {
                            foreach ($administrateurs as $admin) {
                                echo "<li>" . htmlspecialchars($admin["nom"]) . " " . htmlspecialchars($admin["prenoms"]) . " (" . htmlspecialchars($admin["emails"]) . ")</li>";
                            }
                        } else {
                            echo "<p>Aucun administrateur trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>
    </div>
</section>

<?php
  include "footer.php";
?>