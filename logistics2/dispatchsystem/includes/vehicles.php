<?php
function fetch_vehicles($dbh, $user_id, $isAdmin) {
    $sql = $isAdmin
        ? "SELECT registration_id, vehicle_plate, car_brand, model, vehicle_type,
                  COALESCE(NULLIF(passenger_capacity,''), NULL) AS passenger_capacity
           FROM vehicles ORDER BY vehicle_plate ASC"
        : "SELECT registration_id, vehicle_plate, car_brand, model, vehicle_type,
                  COALESCE(NULLIF(passenger_capacity,''), NULL) AS passenger_capacity
           FROM vehicles WHERE user_id = :uid ORDER BY vehicle_plate ASC";

    $stmt = $isAdmin ? $dbh->query($sql) : $dbh->prepare($sql);
    if (!$isAdmin) $stmt->execute([':uid' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
