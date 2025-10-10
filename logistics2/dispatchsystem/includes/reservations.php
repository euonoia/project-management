<?php
function get_reservation(PDO $dbh, $user_id, $id, $isAdmin = false) {
    $sql = $isAdmin
        ? 'SELECT * FROM vehicle_reservations WHERE id = :id'
        : 'SELECT * FROM vehicle_reservations WHERE id = :id AND user_id = :uid';
    $stmt = $dbh->prepare($sql);
    $params = $isAdmin ? [':id' => (int)$id] : [':id' => (int)$id, ':uid' => $user_id];
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function fetch_reservations($dbh, $user_id, $filter, $viewAll) {
    if ($filter === 'All') {
        if ($viewAll) $stmt = $dbh->query('SELECT * FROM vehicle_reservations ORDER BY pickup_datetime DESC LIMIT 300');
        else {
            $stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE user_id = :uid ORDER BY pickup_datetime DESC LIMIT 300');
            $stmt->execute([':uid' => $user_id]);
        }
    } else {
        if ($viewAll) {
            $stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE status = :st ORDER BY pickup_datetime DESC LIMIT 300');
            $stmt->execute([':st' => $filter]);
        } else {
            $stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE user_id = :uid AND status = :st ORDER BY pickup_datetime DESC LIMIT 300');
            $stmt->execute([':uid' => $user_id, ':st' => $filter]);
        }
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
