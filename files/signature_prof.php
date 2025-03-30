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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $professeur_id = $_SESSION['user_id']; // ID du prof connecté
    $classe_id = $_POST['classe_id'];
    $date_presence = date("Y-m-d");
    
    $eleves_presents = isset($_POST['presences']) ? $_POST['presences'] : [];

    foreach ($eleves_presents as $eleve_id) {
        $sql_edt = "SELECT id FROM emploi_du_temps WHERE class_id = ? AND DATE(start_datetime) = ?";
        $stmt_edt = $pdo->prepare($sql_edt);
        $stmt_edt->execute([$classe_id, $date_presence]);
        $emploi = $stmt_edt->fetch(PDO::FETCH_ASSOC);

        if ($emploi) {
            $emploi_du_temps_id = $emploi['id'];

            // Ajouter la demande de signature
            $sql_insert = "INSERT INTO sign (user_id, emploi_du_temps_id, file_name, statut, professeur_id) 
                           VALUES (?, ?, NULL, 'En attente', ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$eleve_id, $emploi_du_temps_id, $professeur_id]);
        }
    }

    echo "Présence enregistrée ! Les élèves doivent signer maintenant.";
}
?>


<?php
  include "footer.php";
?>