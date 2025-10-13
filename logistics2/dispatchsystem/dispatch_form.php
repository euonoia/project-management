<?php
// filepath: c:\xampp\htdocs\PM-TNVS\logistics2\dispatchsystem\auto_dispatch.php
include('../../database/connect.php');

// Get reservation ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Reservation ID missing']);
    exit;
}

// Fetch reservation
$stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit;
}

// Determine driver name (vehicle owner first, fallback to reservation user)
$driverName = '';
$driverContact = ''; // optional, you can fetch from user table if exists

if (!empty($reservation['vehicle_registration_id'])) {
    $stmtOwner = $dbh->prepare('
        SELECT u.firstname, u.lastname, v.user_id, v.vehicle_plate
        FROM vehicles v
        INNER JOIN users u ON v.user_id = u.user_id
        WHERE v.registration_id = :reg
        LIMIT 1
    ');
    $stmtOwner->execute([':reg' => $reservation['vehicle_registration_id']]);
    if ($rowOwner = $stmtOwner->fetch(PDO::FETCH_ASSOC)) {
        $driverName = $rowOwner['firstname'] . ' ' . $rowOwner['lastname'];
        $driverContact = ''; // fetch if needed
    }
}

// Fallback to reservation user
if ($driverName === '' && !empty($reservation['user_id'])) {
    $stmtUser = $dbh->prepare('SELECT firstname, lastname FROM users WHERE user_id = :uid LIMIT 1');
    $stmtUser->execute([':uid' => $reservation['user_id']]);
    if ($rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC)) {
        $driverName = $rowUser['firstname'] . ' ' . $rowUser['lastname'];
    }
}

// Automatic dispatch values
$dispatchTime = date('Y-m-d H:i:s');
$odometerStart = $reservation['odometer_start'] ?? 0;

// Update reservation with dispatch info
$stmtUpdate = $dbh->prepare('
    UPDATE vehicle_reservations
    SET status = "Dispatched",
        assigned_driver = :driver,
        driver_contact = :contact,
        dispatch_time = :dispatch,
        odometer_start = :odometer
    WHERE id = :id
');

$success = $stmtUpdate->execute([
    ':driver' => $driverName,
    ':contact' => $driverContact,
    ':dispatch' => $dispatchTime,
    ':odometer' => $odometerStart,
    ':id' => $id,
]);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Reservation dispatched automatically',
        'assigned_driver' => $driverName,
        'dispatch_time' => $dispatchTime
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to dispatch reservation']);
}
