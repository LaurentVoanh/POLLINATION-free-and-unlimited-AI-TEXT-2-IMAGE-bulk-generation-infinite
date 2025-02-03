<?php
// Configuration du dossier de destination
$uploadDir = './image/';

// Vérifier si un fichier a été uploadé
if (isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $fileName = basename($file['name']);
    $uploadPath = $uploadDir . $fileName;

    // Vérifier et créer le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Tenter d'uploader le fichier
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo "L'image a été enregistrée avec succès : $fileName";
    } else {
        echo "Erreur lors de l'enregistrement de l'image.";
    }
} else {
    echo "Aucune image n'a été reçue.";
}
?>