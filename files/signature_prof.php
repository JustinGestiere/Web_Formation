<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification de la connexion et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    require_once "bdd.php";
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $professeur_id = $_SESSION['user_id'];
    $emploi_du_temps_id = $_POST['emploi_du_temps_id'];
    $eleves_presents = isset($_POST['presences']) ? $_POST['presences'] : [];

    foreach ($eleves_presents as $eleve_id) {
        // Vérifier si une demande existe déjà
        $sql_check = "SELECT id FROM sign WHERE user_id = ? AND emploi_du_temps_id = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$eleve_id, $emploi_du_temps_id]);
        
        if (!$stmt_check->fetch()) {
            // Ajouter la demande de signature si elle n'existe pas
            $sql_insert = "INSERT INTO sign (user_id, emploi_du_temps_id, file_name, statut, professeur_id) 
                          VALUES (?, ?, NULL, 'En attente', ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$eleve_id, $emploi_du_temps_id, $professeur_id]);
        }
    }
    
    echo "<div class='alert alert-success'>Demandes de signatures envoyées avec succès !</div>";
}

// Inclusion du header
include "header_prof.php";
?>

<!-- Scripts nécessaires pour le menu -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<div class="container mt-4">
    <div class="signature-form">
        <h2>Demander des Signatures</h2>
        
        <!-- Formulaire de sélection -->
        <form method="POST" action="">
            <!-- Sélection du cours -->
            <div class="form-group">
                <label for="emploi_du_temps_id">Sélectionnez un cours :</label>
                <select name="emploi_du_temps_id" id="emploi_du_temps_id" class="form-control" required>
                    <option value="">-- Choisir un cours --</option>
                    <?php
                    // Récupérer les cours du jour pour ce professeur
                    $sql_cours = "SELECT edt.id, edt.title, c.name as class_name,
                                        DATE_FORMAT(edt.start_datetime, '%H:%i') as start_time
                                FROM emploi_du_temps edt
                                INNER JOIN classes c ON edt.class_id = c.id
                                WHERE edt.professeur_id = ?
                                AND DATE(edt.start_datetime) = CURRENT_DATE
                                ORDER BY edt.start_datetime";
                    $stmt_cours = $pdo->prepare($sql_cours);
                    $stmt_cours->execute([$_SESSION['user_id']]);
                    
                    while ($cours = $stmt_cours->fetch()) {
                        echo "<option value='" . $cours['id'] . "'>" 
                            . htmlspecialchars($cours['title']) 
                            . " - " . htmlspecialchars($cours['class_name'])
                            . " à " . $cours['start_time']
                            . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Liste des élèves -->
            <div id="eleves-list"></div>

            <button type="submit" class="btn btn-primary">Demander les signatures</button>
        </form>

        <!-- Affichage des signatures en attente -->
        <div class="signature-status mt-4">
            <h3>Statut des Signatures</h3>
            <?php
            $sql_status = "SELECT s.*, u.nom, u.prenoms, edt.title as cours_titre,
                                  c.name as class_name, DATE_FORMAT(edt.start_datetime, '%H:%i') as start_time
                          FROM sign s
                          JOIN users u ON s.user_id = u.id
                          JOIN emploi_du_temps edt ON s.emploi_du_temps_id = edt.id
                          JOIN classes c ON edt.class_id = c.id
                          WHERE s.professeur_id = ?
                          AND DATE(edt.start_datetime) = CURRENT_DATE
                          ORDER BY s.statut DESC, edt.start_datetime, u.nom, u.prenoms";
            $stmt_status = $pdo->prepare($sql_status);
            $stmt_status->execute([$_SESSION['user_id']]);
            
            if ($stmt_status->rowCount() > 0) {
                echo "<ul class='list-group mt-3'>";
                while ($signature = $stmt_status->fetch()) {
                    $status_class = $signature['statut'] == 'En attente' ? 'text-warning' : 'text-success';
                    echo "<li class='list-group-item'>";
                    echo htmlspecialchars($signature['nom']) . " " . htmlspecialchars($signature['prenoms']);
                    echo " - " . htmlspecialchars($signature['cours_titre']);
                    echo " (" . htmlspecialchars($signature['class_name']) . " à " . $signature['start_time'] . ")";
                    echo " <span class='" . $status_class . "'>[" . htmlspecialchars($signature['statut']) . "]</span>";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='mt-3'>Aucune signature en attente pour aujourd'hui.</p>";
            }
            ?>
        </div>
    </div>
</div>

<script>
// Gestion des élèves
document.getElementById('emploi_du_temps_id').addEventListener('change', function() {
    const edtId = this.value;
    const elevesDiv = document.getElementById('eleves-list');
    
    if (edtId) {
        // Charger les élèves de la classe correspondant au cours
        fetch(`get_eleves.php?edt_id=${edtId}`)
            .then(response => response.json())
            .then(eleves => {
                if (eleves.error) {
                    elevesDiv.innerHTML = `<div class="alert alert-danger">${eleves.error}</div>`;
                    return;
                }
                elevesDiv.innerHTML = '<div class="form-group mt-4"><h4>Liste des élèves</h4>';
                eleves.forEach(eleve => {
                    elevesDiv.innerHTML += `
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="presences[]" 
                                   value="${eleve.id}" id="eleve_${eleve.id}">
                            <label class="form-check-label" for="eleve_${eleve.id}">
                                ${eleve.nom} ${eleve.prenoms}
                            </label>
                        </div>`;
                });
                elevesDiv.innerHTML += '</div>';
            })
            .catch(error => {
                elevesDiv.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des élèves</div>';
                console.error('Error:', error);
            });
    } else {
        elevesDiv.innerHTML = '';
    }
});
</script>

<?php
include "footer.php";
?>