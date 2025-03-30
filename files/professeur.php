<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
try {
    require_once "bdd.php";
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification de la connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

// Vérification du rôle
if ($_SESSION['user_role'] !== 'prof') {
    die("Accès non autorisé. Vous devez être professeur pour accéder à cette page.");
}

// Inclusion du header
try {
    include "header_prof.php";
} catch (Exception $e) {
    die("Erreur lors du chargement du header : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/emploi_du_temps.css" rel="stylesheet">
    <link href="../css/accueil.css" rel="stylesheet" />
    <title>Accueil Professeur</title>
</head>
<body>
    <div class="container">
        <h1>Tableau de bord du professeur</h1>
        
        <?php
        try {
            // Récupération des classes du professeur
            $sql = "SELECT DISTINCT c.name 
                   FROM classes c
                   INNER JOIN cours co ON c.id = co.classe_id
                   WHERE co.professeur_id = ?
                   ORDER BY c.name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <div class="section">
                <h2>Mes Classes</h2>
                <ul>
                    <?php foreach ($classes as $classe): ?>
                        <li><?php echo htmlspecialchars($classe['name']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php
            // Récupération des cours de la semaine
            $debut_semaine = date('Y-m-d');
            $fin_semaine = date('Y-m-d', strtotime('+7 days'));

            $sql = "SELECT c.titre, c.date_debut, c.date_fin, cl.name as classe_name
                   FROM cours c
                   INNER JOIN classes cl ON c.classe_id = cl.id
                   WHERE c.professeur_id = ?
                   AND c.date_debut BETWEEN ? AND ?
                   ORDER BY c.date_debut";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $debut_semaine, $fin_semaine]);
            $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="section">
                <h2>Mes Cours cette semaine</h2>
                <ul>
                    <?php foreach ($cours as $cours_item): ?>
                        <li>
                            <?php 
                            echo htmlspecialchars($cours_item['titre']) . ' - ' . 
                                 htmlspecialchars($cours_item['classe_name']) . ' - ' .
                                 date('d/m/Y H:i', strtotime($cours_item['date_debut'])) . ' à ' .
                                 date('H:i', strtotime($cours_item['date_fin']));
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php
        } catch (PDOException $e) {
            echo '<div class="error">Erreur lors de la récupération des données : ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>
</body>
</html>

<?php
try {
    include "footer.php";
} catch (Exception $e) {
    echo "Erreur lors du chargement du footer : " . $e->getMessage();
}
?>