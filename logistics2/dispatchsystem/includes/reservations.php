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

function fetch_counts($dbh, $user_id) {
    $stmt = $dbh->prepare('SELECT TRIM(status) AS status, COUNT(*) AS c FROM vehicle_reservations WHERE user_id = :uid GROUP BY TRIM(status)');
    $stmt->execute([':uid' => $user_id]);
    $counts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $counts[$row['status']] = (int)$row['c'];
    return $counts;
}

function fetch_counts_all($dbh) {
    $counts = [];
    foreach ($dbh->query('SELECT TRIM(status) AS status, COUNT(*) AS c FROM vehicle_reservations GROUP BY TRIM(status)')->fetchAll(PDO::FETCH_ASSOC) as $row)
        $counts[$row['status']] = (int)$row['c'];
    return $counts;
}

function fetch_reservations($dbh, $user_id, $filter, $viewAll) {
    if ($filter === 'All') {
        $stmt = $viewAll
            ? $dbh->query('SELECT * FROM vehicle_reservations ORDER BY pickup_datetime DESC LIMIT 300')
            : $dbh->prepare('SELECT * FROM vehicle_reservations WHERE user_id = :uid ORDER BY pickup_datetime DESC LIMIT 300');
        if (!$viewAll) $stmt->execute([':uid' => $user_id]);
    } else {
        $stmt = $viewAll
            ? $dbh->prepare('SELECT * FROM vehicle_reservations WHERE status = :st ORDER BY pickup_datetime DESC LIMIT 300')
            : $dbh->prepare('SELECT * FROM vehicle_reservations WHERE user_id = :uid AND status = :st ORDER BY pickup_datetime DESC LIMIT 300');
        $params = $viewAll ? [':st' => $filter] : [':uid' => $user_id, ':st' => $filter];
        $stmt->execute($params);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
