<?php
require_once __DIR__ . '/admin_auth.php';

header('Content-Type: application/json; charset=utf-8');

function send_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}


// Stop here if the admin is not logged in.
if (!is_admin_logged_in()) {
    send_json(['success' => false, 'message' => 'Unauthorized'], 401);
}




try {
    // Handle button actions from the dashboard.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $id = (int)($_POST['id'] ?? 0);

        // Confirm one reservation.
        if ($action === 'confirm_reservation' && $id > 0) {
            $stmt = $db->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = :id");
            $stmt->execute(['id' => $id]);
            send_json(['success' => true, 'message' => 'Reservation confirmed successfully.']);
        }

        // Update reservation dates, payment method, and total price.
        if ($action === 'modify_reservation' && $id > 0) {
            $startDate = trim($_POST['start_date'] ?? '');
            $endDate = trim($_POST['end_date'] ?? '');
            $paymentMethod = trim($_POST['payment_method'] ?? '');

            if ($startDate === '' || $endDate === '' || $paymentMethod === '') {
                send_json(['success' => false, 'message' => 'Start date, end date, and payment method are required.'], 400);
            }

            $startDateTime = DateTime::createFromFormat('Y-m-d', $startDate);
            $endDateTime = DateTime::createFromFormat('Y-m-d', $endDate);

            if (!$startDateTime || !$endDateTime || $endDateTime <= $startDateTime) {
                send_json(['success' => false, 'message' => 'End date must be after start date.'], 400);
            }

            $stmt = $db->prepare('SELECT c.price_per_day FROM reservations r LEFT JOIN cars c ON c.id = r.car_id WHERE r.id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $pricePerDay = (int)($stmt->fetchColumn() ?: 0);

            if ($pricePerDay <= 0) {
                send_json(['success' => false, 'message' => 'Reservation not found.'], 404);
            }

            $days = (int)$startDateTime->diff($endDateTime)->days;
            $totalPrice = $days * $pricePerDay;

            $updateStmt = $db->prepare('UPDATE reservations SET start_date = :start_date, end_date = :end_date, days = :days, payment_method = :payment_method, total_price = :total_price WHERE id = :id');
            $updateStmt->execute([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_method' => $paymentMethod,
                'days' => $days,
                'total_price' => $totalPrice,
                'id' => $id,
            ]);

            send_json(['success' => true, 'message' => 'Reservation updated successfully.']);
        }

        // Delete one reservation.
        if ($action === 'delete_reservation' && $id > 0) {
            $stmt = $db->prepare('DELETE FROM reservations WHERE id = :id');
            $stmt->execute(['id' => $id]);
            send_json(['success' => true, 'message' => 'Reservation deleted successfully.']);
        }

        send_json(['success' => false, 'message' => 'Invalid action or record ID.'], 400);
    }

    
    // Load reservations for the table.
    $sql = 'SELECT r.id, r.reservation_number, r.start_date, r.end_date, r.days, r.payment_method, r.total_price, r.status, r.created_at, c.name AS client_name, c.email AS client_email, c.phone AS client_phone, c.cin AS client_cin, car.name AS car_name
            FROM reservations r
            LEFT JOIN clients c ON c.id = r.client_id
            LEFT JOIN cars car ON car.id = r.car_id';

    $conditions = [];
    $params = [];

    // Filter by status.
    $status = trim($_GET['filter_status'] ?? '');
    if ($status === 'pending' || $status === 'confirmed') {
        $conditions[] = 'LOWER(r.status) = :status';
        $params['status'] = $status;
    }

    // Search by client name.
    $search = trim($_GET['search'] ?? '');
    if ($search !== '') {
        $conditions[] = 'LOWER(c.name) LIKE :search';
        $params['search'] = strtolower($search) . '%';
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Sort by date.
    $dateOrder = trim($_GET['date_order'] ?? 'nearest');
    $sql .= $dateOrder === 'latest' ? ' ORDER BY r.start_date DESC' : ' ORDER BY r.start_date ASC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary counters shown on the dashboard.
    $counts = [
        'total_reservations' => (int)$db->query('SELECT COUNT(*) FROM reservations')->fetchColumn(),
        'pending_reservations' => (int)$db->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn(),
        'confirmed_reservations' => (int)$db->query("SELECT COUNT(*) FROM reservations WHERE status = 'confirmed'")->fetchColumn(),
    ];

    send_json([
        'success' => true,
        'username' => $_SESSION['admin_username'] ?? 'admin',
        'counts' => $counts,
        'reservations' => $reservations,
    ]);
} catch (PDOException $e) {
    send_json(['success' => false, 'message' => 'Unable to complete the requested action.'], 500);
}

