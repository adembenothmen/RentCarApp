<?php
// API endpoint: Fetch car data (single car by ID or list with optional filtering)
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';

// Get request parameters
$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = trim($_GET['type'] ?? '');
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
$allowedTypes = ['Sedan', 'SUV', 'Electric', 'Luxury'];


try {
    // Fetch single car by ID
    if ($carId > 0) {
        $stmt = $db->prepare("SELECT id, name, type, seats, transmission, fuel, price_per_day, image FROM cars WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $carId]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Car not found.']);
            exit;
        }

        echo json_encode(['success' => true, 'car' => $car]);
        exit;
    }

    
    
    // Fetch list of cars with optional type filtering
    $sql = "SELECT id, name, type, seats, transmission, fuel, price_per_day, image FROM cars";
    $params = [];

    if ($type !== '' && in_array($type, $allowedTypes, true)) {
        $sql .= ' WHERE type = :type';
        $params['type'] = $type;
    }

    $sql .= ' ORDER BY id ASC';

    if ($limit > 0) {
        $sql .= ' LIMIT ' . $limit;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'cars' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load cars.']);
}
