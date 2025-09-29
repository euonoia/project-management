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
function get_user_reservations(PDO $dbh, $user_id) {
    $stmt = $dbh->prepare('SELECT id, reservation_ref, status, trip_date, pickup_datetime, dropoff_datetime FROM vehicle_reservations WHERE user_id = :uid ORDER BY pickup_datetime DESC LIMIT 10');
    $stmt->execute([':uid' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch active reservation (not completed or cancelled)
function get_active_reservation(PDO $dbh, $user_id) {
    $stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE user_id = :uid AND status NOT IN ("Completed", "Cancelled") ORDER BY pickup_datetime DESC LIMIT 1');
    $stmt->execute([':uid' => $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
