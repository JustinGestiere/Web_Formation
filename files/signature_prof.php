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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Signatures - Professeur</title>
    <link href="../css/styles.css" rel="stylesheet">
    <style>
        .signature-form {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .eleves-list {
            margin: 20px 0;
            max-height: 400px;
            overflow-y: auto;
        }
        .signature-status {
            margin-top: 30px;
        }
        .status-pending {
            color: orange;
        }
        .status-signed {
            color: green;
        }
    </style>
</head>
<body>
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
        <div class="signature-status">
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
                echo "<ul class='list-group'>";
                while ($signature = $stmt_status->fetch()) {
                    $status_class = $signature['statut'] == 'En attente' ? 'status-pending' : 'status-signed';
                    echo "<li class='list-group-item'>";
                    echo htmlspecialchars($signature['nom']) . " " . htmlspecialchars($signature['prenoms']);
                    echo " - " . htmlspecialchars($signature['cours_titre']);
                    echo " (" . htmlspecialchars($signature['class_name']) . " à " . $signature['start_time'] . ")";
                    echo " <span class='" . $status_class . "'>[" . htmlspecialchars($signature['statut']) . "]</span>";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Aucune signature en attente pour aujourd'hui.</p>";
            }
            ?>
        </div>
    </div>

    <script>
    document.getElementById('emploi_du_temps_id').addEventListener('change', function() {
        const edtId = this.value;
        const elevesDiv = document.getElementById('eleves-list');
        
        if (edtId) {
            // Charger les élèves de la classe correspondant au cours
            fetch(`get_eleves.php?edt_id=${edtId}`)
                .then(response => response.json())
                .then(eleves => {
                    elevesDiv.innerHTML = '<div class="form-group">';
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
                });
        } else {
            elevesDiv.innerHTML = '';
        }
    });
    </script>
</body>
</html>