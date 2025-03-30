<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Connexion à la base de données
try {
    require_once "bdd.php";
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit();
}

// Récupérer et décoder les données JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['signature_id']) || !isset($data['image_data'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

// Vérifier que la demande de signature existe et correspond à l'utilisateur
$sql_check = "SELECT id FROM sign WHERE id = ? AND user_id = ? AND statut = 'En attente'";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$data['signature_id'], $_SESSION['user_id']]);

if (!$stmt_check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Demande de signature invalide']);
    exit();
}

try {
    // Créer le dossier signatures s'il n'existe pas
    $upload_dir = '../signatures/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Extraire les données de l'image
    $image_data = str_replace('data:image/png;base64,', '', $data['image_data']);
    $image_data = str_replace(' ', '+', $image_data);
    $image_data = base64_decode($image_data);

    // Générer un nom de fichier unique
    $filename = uniqid('signature_') . '_' . date('Ymd_His') . '.png';
    $file_path = $upload_dir . $filename;

    // Sauvegarder l'image
    if (file_put_contents($file_path, $image_data) === false) {
        throw new Exception('Erreur lors de la sauvegarde du fichier');
    }

    // Mettre à jour la base de données
    $sql_update = "UPDATE sign 
                   SET file_name = ?, 
                       statut = 'Signé', 
                       updated_at = NOW()
                   WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    
    if (!$stmt_update->execute([$filename, $data['signature_id']])) {
        // En cas d'erreur, supprimer le fichier
        unlink($file_path);
        throw new Exception('Erreur lors de la mise à jour de la base de données');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()
    ]);
}