<?php
session_start();
include('../../../database/connect.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

function back_with_flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    header('Location: ../../reservation/reserve.php');
    exit();
}

// Only accept POST create requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (isset($_POST['action']) ? trim((string)$_POST['action']) : '') !== 'create') {
    back_with_flash('error', 'Invalid request');
}

$user_id = (string)$_SESSION['user_id'];

// Sanitise inputs
$vehicle_registration_id = isset($_POST['vehicle_registration_id']) ? trim((string)$_POST['vehicle_registration_id']) : '';
$passengers_count = isset($_POST['passengers_count']) ? (int)$_POST['passengers_count'] : 0;
$trip_date = isset($_POST['trip_date']) ? trim((string)$_POST['trip_date']) : '';
$pickup_time = isset($_POST['pickup_datetime']) ? trim((string)$_POST['pickup_datetime']) : '';
$dropoff_time = isset($_POST['dropoff_datetime']) ? trim((string)$_POST['dropoff_datetime']) : '';
$pickup_location = isset($_POST['pickup_location']) ? trim((string)$_POST['pickup_location']) : '';
$dropoff_location = isset($_POST['dropoff_location']) ? trim((string)$_POST['dropoff_location']) : '';
$requester_name = isset($_POST['requester_name']) ? trim((string)$_POST['requester_name']) : '';
$purpose = isset($_POST['purpose']) ? trim((string)$_POST['purpose']) : '';

// Basic validation
if ($vehicle_registration_id === '') back_with_flash('error', 'Please select a vehicle.');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $trip_date)) back_with_flash('error', 'Invalid trip date');
if (!preg_match('/^\d{2}:\d{2}$/', $pickup_time)) back_with_flash('error', 'Invalid pickup time');
if (!preg_match('/^\d{2}:\d{2}$/', $dropoff_time)) back_with_flash('error', 'Invalid dropoff time');
if ($passengers_count <= 0) back_with_flash('error', 'Passengers must be 1+');
if ($pickup_location === '' || $dropoff_location === '') back_with_flash('error', 'Locations required');
if ($requester_name === '') back_with_flash('error', 'Requester required');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $user_id = $_SESSION['user_id'];
    $vehicle_registration_id = $_POST['vehicle_registration_id'];
    $pickup_lat = $_POST['pickup_lat'];
    $pickup_lng = $_POST['pickup_lng'];
    $pickup_address = $_POST['pickup_address'];
    $dropoff_lat = $_POST['dropoff_lat'];
    $dropoff_lng = $_POST['dropoff_lng'];
    $dropoff_address = $_POST['dropoff_address'];

    // Save pickup location
    $stmt = $dbh->prepare('INSERT INTO saved_locations (user_id, vehicle_registration_id, type, latitude, longitude, address) VALUES (?, ?, "pickup", ?, ?, ?)');
    $stmt->execute([$user_id, $vehicle_registration_id, $pickup_lat, $pickup_lng, $pickup_address]);
    $pickup_location_id = $dbh->lastInsertId();

    // Save dropoff location
    $stmt = $dbh->prepare('INSERT INTO saved_locations (user_id, vehicle_registration_id, type, latitude, longitude, address) VALUES (?, ?, "dropoff", ?, ?, ?)');
    $stmt->execute([$user_id, $vehicle_registration_id, $dropoff_lat, $dropoff_lng, $dropoff_address]);
    $dropoff_location_id = $dbh->lastInsertId();
}
// Parse datetimes
$pickup_dt = DateTime::createFromFormat('Y-m-d H:i', $trip_date . ' ' . $pickup_time);
$dropoff_dt = DateTime::createFromFormat('Y-m-d H:i', $trip_date . ' ' . $dropoff_time);
if (!$pickup_dt || !$dropoff_dt) back_with_flash('error', 'Invalid date/time combination');

$pickup_datetime = $pickup_dt->format('Y-m-d H:i:s');
$dropoff_datetime = $dropoff_dt->format('Y-m-d H:i:s');


// Generate server-side reservation ref
function generateReservationRef() {
    return 'VR' . date('ymdHis') . strtoupper(bin2hex(random_bytes(3)));
}
$reservation_ref = generateReservationRef();

// Use PDO ($dbh) if available (preferred). If $conn (mysqli) exists, convert to PDO-like flow.
try {
    // fetch vehicle_plate and capacity
    $vehicle_plate = '';
    $passenger_capacity = null;

    if (isset($dbh) && $dbh instanceof PDO) {
        $stmt = $dbh->prepare('SELECT vehicle_plate, COALESCE(NULLIF(passenger_capacity, \'\'), NULL) AS cap FROM vehicles WHERE registration_id = :rid LIMIT 1');
        $stmt->execute([':rid' => $vehicle_registration_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) back_with_flash('error', 'Selected vehicle not found');
        $vehicle_plate = $row['vehicle_plate'] ?? '';
        $passenger_capacity = isset($row['cap']) && $row['cap'] !== null ? (int)$row['cap'] : null;
    } elseif (isset($conn) && $conn instanceof mysqli) {
        $s = $conn->prepare('SELECT vehicle_plate, COALESCE(NULLIF(passenger_capacity, \'\'), NULL) AS cap FROM vehicles WHERE registration_id = ? LIMIT 1');
        $s->bind_param('s', $vehicle_registration_id);
        $s->execute();
        $s->bind_result($vehicle_plate, $capRaw);
        $s->fetch();
        $s->close();
        if ($vehicle_plate === null) back_with_flash('error', 'Selected vehicle not found');
        $passenger_capacity = ($capRaw !== null && $capRaw !== '') ? (int)$capRaw : null;
    } else {
        throw new RuntimeException('Database connection not available');
    }

    // capacity check
    if ($passenger_capacity !== null && $passengers_count > $passenger_capacity) {
        back_with_flash('error', 'Passengers exceed vehicle capacity (' . $passenger_capacity . ')');
    }

    // Insert reservation (use prepared statements)
    if (isset($dbh) && $dbh instanceof PDO) {
        $sql = 'INSERT INTO vehicle_reservations
            (user_id, reservation_ref, vehicle_registration_id, vehicle_plate, passengers_count, trip_date, pickup_datetime, dropoff_datetime, pickup_location, dropoff_location, requester_name, purpose, status, created_at)
            VALUES
            (:user_id, :reservation_ref, :vehicle_registration_id, :vehicle_plate, :passengers_count, :trip_date, :pickup_datetime, :dropoff_datetime, :pickup_location, :dropoff_location, :requester_name, :purpose, :status, NOW())';
        $stmt = $dbh->prepare($sql);
        $ok = $stmt->execute([
            ':user_id' => $user_id,
            ':reservation_ref' => $reservation_ref,
            ':vehicle_registration_id' => $vehicle_registration_id,
            ':vehicle_plate' => $vehicle_plate,
            ':passengers_count' => $passengers_count,
            ':trip_date' => $trip_date,
            ':pickup_datetime' => $pickup_datetime,
            ':dropoff_datetime' => $dropoff_datetime,
            ':pickup_location' => $pickup_location,
            ':dropoff_location' => $dropoff_location,
            ':requester_name' => $requester_name,
            ':purpose' => $purpose,
            ':status' => 'Pending'
        ]);
        if (!$ok) {
            $info = $stmt->errorInfo();
            throw new RuntimeException('Insert failed: ' . ($info[2] ?? json_encode($info)));
        }
    } else {
        // mysqli path
        $sql = "INSERT INTO vehicle_reservations
            (user_id, reservation_ref, vehicle_registration_id, vehicle_plate, passengers_count, trip_date, pickup_datetime, dropoff_datetime, pickup_location, dropoff_location, requester_name, purpose, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new RuntimeException('Prepare failed: ' . $conn->error);
        $stmt->bind_param(
            'ssssisssssss',
            $user_id,
            $reservation_ref,
            $vehicle_registration_id,
            $vehicle_plate,
            $passengers_count,
            $trip_date,
            $pickup_datetime,
            $dropoff_datetime,
            $pickup_location,
            $dropoff_location,
            $requester_name,
            $purpose
        );
        if (!$stmt->execute()) {
            throw new RuntimeException('Execute failed: ' . $stmt->error);
        }
        $stmt->close();
    }
        
    // After successful reservation creation
    $ws = stream_socket_client("tcp://localhost:8080");
    if ($ws) {
        fwrite($ws, json_encode(['type' => 'new_reservation']));
        fclose($ws);
    }

    // Use the generated reservation_ref, not lastInsertId
    // $reservation_ref = generateReservationRef(); // already set above

    // Cost analysis values from hidden fields
    $distance_km = $_POST['distance_km'];
    $estimated_time = $_POST['estimated_time'];
    $driver_earnings = $_POST['driver_earnings'];
    $passenger_fare = $_POST['passenger_fare'];
    $incentives = $_POST['incentives'];

    // Insert into cost_analysis
    $stmt = $dbh->prepare("INSERT INTO cost_analysis 
        (user_id, registration_id, reservation_ref, distance_km, estimated_time, driver_earnings, passenger_fare, incentives) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $vehicle_registration_id,
        $reservation_ref, // use the generated reference
        $distance_km,
        $estimated_time,
        $driver_earnings,
        $passenger_fare,
        $incentives
    ]);

    back_with_flash('success', 'Reservation created successfully. Ref: ' . $reservation_ref);

} catch (Throwable $e) {
    back_with_flash('error', 'Insert failed: ' . $e->getMessage());
}
?>