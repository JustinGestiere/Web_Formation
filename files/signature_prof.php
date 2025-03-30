<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: login.php");
    exit();
}

require_once "header_prof.php";
require_once "bdd.php"; // Connexion à la BDD

$prof_id = $_SESSION['user_id'];
$message = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['classe_id']) && isset($_POST['presences'])) {
    $classe_id = $_POST['classe_id'];
    $date_presence = date("Y-m-d");
    $eleves_presents = $_POST['presences'];

    // Vérifier si un cours est prévu aujourd'hui
    $sql_edt = "SELECT id FROM emploi_du_temps WHERE class_id = ? AND DATE(start_datetime) = ?";
    $stmt_edt = $pdo->prepare($sql_edt);
    $stmt_edt->execute([$classe_id, $date_presence]);
    $emploi = $stmt_edt->fetch(PDO::FETCH_ASSOC);

    if ($emploi) {
        $emploi_du_temps_id = $emploi['id'];

        foreach ($eleves_presents as $eleve_id) {
            $sql_insert = "INSERT INTO sign (user_id, emploi_du_temps_id, file_name, statut, professeur_id) 
                           VALUES (?, ?, NULL, 'En attente', ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$eleve_id, $emploi_du_temps_id, $prof_id]);
        }

        $message = "Présence enregistrée ! Les élèves doivent maintenant signer.";
    } else {
        $message = "Aucun cours trouvé pour aujourd'hui.";
    }
}

// Récupérer les classes du professeur
$sql_classes = "SELECT c.id, c.nom FROM classes c 
                JOIN professeur_classes pc ON c.id = pc.classe_id 
                WHERE pc.professeur_id = ?";
$stmt_classes = $pdo->prepare($sql_classes);
$stmt_classes->execute([$prof_id]);
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="main-content">
    <div class="container mt-4">
        <h1>Page des signatures</h1>

        <?php if ($message) { echo "<p style='color: green;'>$message</p>"; } ?>

        <form action="" method="POST">
            <label for="classe">Sélectionnez une classe :</label>
            <select name="classe_id" id="classe" required>
                <option value="">Choisir une classe</option>
                <?php foreach ($classes as $classe) { ?>
                    <option value="<?= $classe['id'] ?>"><?= htmlspecialchars($classe['nom']) ?></option>
                <?php } ?>
            </select>

            <div id="eleves-list">
                <!-- Les élèves seront affichés ici via JavaScript -->
            </div>

            <button type="submit" class="btn btn-primary mt-3">Valider la présence</button>
        </form>
    </div>
</div>

<script>
document.getElementById('classe').addEventListener('change', function() {
    let classeId = this.value;
    
    if (classeId) {
        fetch('signature_prof.php?classe_id=' + classeId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('eleves-list').innerHTML = data;
            });
    } else {
        document.getElementById('eleves-list').innerHTML = '';
    }
});
</script>

<?php
// Gestion de l'AJAX pour charger les élèves
if (isset($_GET['classe_id'])) {
    $classe_id = $_GET['classe_id'];

    $sql_eleves = "SELECT id, nom, prenoms FROM users WHERE classe_id = ? AND roles = 'eleve'";
    $stmt_eleves = $pdo->prepare($sql_eleves);
    $stmt_eleves->execute([$classe_id]);
    $eleves = $stmt_eleves->fetchAll(PDO::FETCH_ASSOC);

    if (count($eleves) > 0) {
        echo "<h3>Liste des élèves</h3>";
        foreach ($eleves as $eleve) {
            echo '<div>
                    <input type="checkbox" name="presences[]" value="'.$eleve['id'].'">
                    '.htmlspecialchars($eleve['nom']).' '.htmlspecialchars($eleve['prenoms']).'
                  </div>';
        }
    } else {
        echo "<p>Aucun élève trouvé.</p>";
    }
    exit(); // Fin du script pour ne pas charger tout le reste
}
?>

<?php require_once "footer.php"; ?>
