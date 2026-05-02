<?php
// Ce fichier renvoie les données de réservation stockées en session
//  pour transmettre au front-end les informations finales.


session_start();

// Indique que la réponse renvoyée est du JSON (UTF-8)
header('Content-Type: application/json; charset=utf-8');



// Vérifie si les données de réservation existent en session
if (!isset($_SESSION['bookingData']) || !is_array($_SESSION['bookingData'])) {
    // Si rien r
    echo json_encode([
        'success' => false,
        'message' => 'No booking confirmation found.',
    ]);
    exit;
}


// Récupère les données de réservation depuis la session
$bookingData = $_SESSION['bookingData'];

// Supprime les données de réservation de la session pour éviter qu'elles
// soient renvoyées plusieurs fois (consommation unique).
unset($_SESSION['bookingData']);


// Renvoie une réponse JSON contenant les informations de réservation
echo json_encode([
    'success' => true,
    'bookingData' => $bookingData,
]);
