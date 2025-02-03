<?php
// Configuration du dossier de destination
$uploadDir = './image/';

// Recevoir les données JSON
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['json']) && isset($data['filename'])) {
    $jsonContent = $data['json'];
    $fileName = $data['filename'];
    $uploadPath = $uploadDir . $fileName;

    // Vérifier et créer le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Tenter d'enregistrer le fichier JSON
    if (file_put_contents($uploadPath, json_encode($jsonContent, JSON_PRETTY_PRINT))) {
        echo "Le fichier JSON a été enregistré avec succès : $fileName";
    } else {
        echo "Erreur lors de l'enregistrement du fichier JSON.";
    }
} else {
    echo "Données JSON invalides.";
}
?>