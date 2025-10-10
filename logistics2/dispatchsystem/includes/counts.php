<?php
function fetch_counts($dbh, $user_id) {
    $counts = [];
    $stmt = $dbh->prepare('SELECT TRIM(status) AS status, COUNT(*) AS c FROM vehicle_reservations WHERE user_id = :uid GROUP BY TRIM(status)');
    $stmt->execute([':uid' => $user_id]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $counts[$row['status']] = (int)$row['c'];
    return $counts;
}

function fetch_counts_all($dbh) {
    $counts = [];
    foreach ($dbh->query('SELECT TRIM(status) AS status, COUNT(*) AS c FROM vehicle_reservations GROUP BY TRIM(status)')->fetchAll(PDO::FETCH_ASSOC) as $row)
        $counts[$row['status']] = (int)$row['c'];
    return $counts;
}
?>
