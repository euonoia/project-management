<?php
// Fetch user info
function get_user(PDO $dbh, $user_id) {
    $stmt = $dbh->prepare('SELECT firstname, lastname FROM users WHERE user_id = :uid LIMIT 1');
    $stmt->execute([':uid' => $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all vehicles except current user
function get_vehicles(PDO $dbh, $user_id) {
    $stmt = $dbh->prepare("
        SELECT registration_id, vehicle_plate, car_brand, model, vehicle_type, 
               COALESCE(NULLIF(passenger_capacity,''), NULL) AS passenger_capacity
        FROM vehicles
        WHERE user_id != :uid
        ORDER BY vehicle_plate ASC
    ");
    $stmt->execute([':uid' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch user's last 10 reservations
function get_user_reservations(PDO $dbh, $user_id, array $statuses = ['Pending', 'Dispatched', 'Completed', 'Cancelled']) {
    // Prepare placeholders for the IN clause
    $inPlaceholders = implode(',', array_fill(0, count($statuses), '?'));

    $sql = "
        SELECT 
            id, 
            reservation_ref, 
            status, 
            trip_date, 
            pickup_datetime, 
            dropoff_datetime,
            pickup_location,
            dropoff_location
        FROM vehicle_reservations
        WHERE user_id = ? 
          AND status IN ($inPlaceholders)
        ORDER BY pickup_datetime DESC
        LIMIT 10
    ";

    $stmt = $dbh->prepare($sql);

    // Merge user_id with statuses for positional parameters
    $params = array_merge([$user_id], $statuses);

    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Fetch user's last 10 reservations
function get_user_reservations_history(PDO $dbh, $user_id, array $statuses = ['Completed', 'Cancelled']) {
    // Prepare placeholders for the IN clause
    $inPlaceholders = implode(',', array_fill(0, count($statuses), '?'));

    $sql = "
        SELECT 
            id, 
            reservation_ref, 
            status, 
            trip_date, 
            pickup_datetime, 
            dropoff_datetime,
            pickup_location,
            dropoff_location
        FROM vehicle_reservations_history
        WHERE user_id = ? 
          AND status IN ($inPlaceholders)
        ORDER BY pickup_datetime DESC
        LIMIT 10
    ";

    $stmt = $dbh->prepare($sql);

    // Merge user_id with statuses for positional parameters
    $params = array_merge([$user_id], $statuses);

    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Fetch active reservation (not completed or cancelled)
function get_active_reservation(PDO $dbh, $user_id) {
   $stmt = $dbh->prepare("
    SELECT 
    vrh.*,
    v.model AS vehicle_model,
    v.plate_number,
    CONCAT(d.firstname, ' ', d.lastname) AS driver_name,
    CONCAT(u.firstname, ' ', u.lastname) AS requester_name
FROM vehicle_reservations_history vrh
LEFT JOIN vehicles v 
    ON vrh.vehicle_registration_id = v.registration_id
LEFT JOIN users d 
    ON v.user_id = d.user_id  -- driver
LEFT JOIN users u 
    ON vrh.user_id = u.user_id  -- requester
ORDER BY vrh.trip_date DESC;

");

    $stmt->execute([':uid' => $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_vehicles_grouped(PDO $dbh, $user_id) {
    $stmt = $dbh->prepare("
        SELECT registration_id, vehicle_plate, car_brand, model, vehicle_type, 
               COALESCE(NULLIF(passenger_capacity,''), NULL) AS passenger_capacity
        FROM vehicles
        WHERE user_id != :uid
        ORDER BY vehicle_type, vehicle_plate ASC
    ");
    $stmt->execute([':uid' => $user_id]);

    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by vehicle_type for quick lookup
    $grouped = [];
    foreach ($vehicles as $v) {
        $type = strtolower($v['vehicle_type']);
        if (!isset($grouped[$type])) {
            $grouped[$type] = [];
        }
        $grouped[$type][] = $v;
    }
    return $grouped;
}

function get_available_vehicles(PDO $dbh, $user_id) {
    $stmt = $dbh->prepare("
        SELECT v.registration_id, v.vehicle_plate, v.car_brand, v.model, v.vehicle_type,
               COALESCE(NULLIF(v.passenger_capacity,''), 1) AS passenger_capacity
        FROM vehicles v
        WHERE v.user_id != :uid
          AND NOT EXISTS (
              SELECT 1
              FROM vehicle_reservations r
              WHERE r.vehicle_registration_id = v.registration_id
              AND r.status NOT IN ('Completed', 'Cancelled')
          )
        ORDER BY v.vehicle_type, v.vehicle_plate ASC
    ");
    $stmt->execute([':uid' => $user_id]);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by type for frontend
    $grouped = [];
    foreach ($vehicles as $v) {
        $type = strtolower($v['vehicle_type']);
        if (!isset($grouped[$type])) $grouped[$type] = [];
        $grouped[$type][] = $v;
    }
    return $grouped;
}

$availableVehicles = get_available_vehicles($dbh, $_SESSION['user_id']);

// Cancel a reservation (sets status to 'Cancelled')
function cancel_reservation(PDO $dbh, int $reservation_id) {
    $stmt = $dbh->prepare("UPDATE vehicle_reservations SET status = 'Cancelled' WHERE id = :id");
    return $stmt->execute([':id' => $reservation_id]);
}

?>

