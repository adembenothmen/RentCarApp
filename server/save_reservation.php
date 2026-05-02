<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../client/home.html');
    exit;
}

require_once __DIR__ . '/db.php';

$carId = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$cin = trim($_POST['cin'] ?? '');
$startDate = trim($_POST['start_date'] ?? '');
$endDate = trim($_POST['end_date'] ?? '');
$paymentMethod = trim($_POST['payment_method'] ?? '');

$requiredFieldsMissing = (
    $carId <= 0 ||
    $name === '' ||
    $email === '' ||
    $phone === '' ||
    $cin === '' ||
    $startDate === '' ||
    $endDate === '' ||
    $paymentMethod === ''
);

if ($requiredFieldsMissing) {
    header('Location: ../client/booking.html?car=' . $carId . '&error=' . urlencode('Please fill all booking fields.'));
    exit;
}

$carStmt = $db->prepare('SELECT id, name, price_per_day FROM cars WHERE id = :id LIMIT 1');
$carStmt->execute(['id' => $carId]);
$car = $carStmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header('Location: ../client/cars.html');
    exit;
}

$startDateTime = DateTime::createFromFormat('Y-m-d', $startDate);
$endDateTime = DateTime::createFromFormat('Y-m-d', $endDate);

if (!$startDateTime || !$endDateTime || $endDateTime <= $startDateTime) {
    header('Location: ../client/booking.html?car=' . $carId . '&error=' . urlencode('End date must be after start date.'));
    exit;
}

$days = (int)$endDateTime->diff($startDateTime)->days;
$pricePerDay = (int)$car['price_per_day'];
$totalPrice = $days * $pricePerDay;

$reservationNumber = 'RES-' . date('YmdHis') . '-' . random_int(1000, 9999);
$timestamp = date('Y-m-d H:i:s');
try {
    $db->beginTransaction();

    $clientStmt = $db->prepare('SELECT id FROM clients WHERE cin = :cin LIMIT 1');
    $clientStmt->execute(['cin' => $cin]);
    $existingClient = $clientStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingClient) {
        $clientId = (int)$existingClient['id'];
    } else {
        $insertClientSql = 'INSERT INTO clients (name,email,phone,cin,created_at
        ) VALUES (
            :name,
            :email,
            :phone,
            :cin,
            :created_at
        )';

        $insertClientStmt = $db->prepare($insertClientSql);
        
        $insertClientStmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'cin' => $cin,
            'created_at' => $timestamp,
        ]);

        $clientId = (int)$db->lastInsertId();
    }



    $insertReservationSql = "INSERT INTO reservations (
        reservation_number,
        client_id,
        car_id,
        start_date,
        end_date,
        days,
        payment_method,
        total_price,
        created_at
    ) VALUES (
        :reservation_number,
        :client_id,
        :car_id,
        :start_date,
        :end_date,
        :days,
        :payment_method,
        :total_price,
        :created_at
    )";

    $insertReservationStmt = $db->prepare($insertReservationSql);
    $insertReservationStmt->execute([
        'reservation_number' => $reservationNumber,
        'client_id' => $clientId,
        'car_id' => (int)$car['id'],
        'start_date' => $startDate,
        'end_date' => $endDate,
        'days' => $days,
        'payment_method' => $paymentMethod,
        'total_price' => $totalPrice,
        'created_at' => $timestamp,
    ]);

    $db->commit();
    
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    header('Location: ../client/booking.html?car=' . $carId . '&error=' . urlencode('Unable to save client/reservation. Please check clients and reservations table columns.'));
    exit;
}


$_SESSION['bookingData'] = [
    'reservation_number' => $reservationNumber,
    'client_id' => $clientId,
    'car_id' => (int)$car['id'],
    'car_name' => $car['name'],
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'cin' => $cin,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'days' => $days,
    'price_per_day' => $pricePerDay,
    'total_price' => $totalPrice,
    'payment_method' => $paymentMethod,
    'timestamp' => $timestamp,
];


header('Location: ../client/confirm.html?saved=1');
exit;
