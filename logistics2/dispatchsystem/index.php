<?php
session_name('admin_session');
session_start();
include('../../database/connect.php');

// --- Auth check ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connections/auth/login.php');
    exit();
}

$user_id = (string)$_SESSION['user_id'];

// --- Check admin table ---
$isAdmin = false;
try {
    $stmt = $conn->prepare("SELECT 1 FROM admin WHERE firebase_uid = ? LIMIT 1");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $isAdmin = true;
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Admin check failed: " . $e->getMessage());
}



// --- Helpers ---
function p($key, $default = '') { return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default; }
function q($key, $default = '') { return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default; }
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function to_mysql_dt($val) {
    if ($val === '' || $val === null) return null;
    $val = str_replace('T', ' ', $val);
    $ts = strtotime($val);
    return $ts ? date('Y-m-d H:i:s', $ts) : null;
}
function flash($type, $msg) { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

// --- Vehicles ---
function fetch_vehicles($dbh, $user_id, $isAdmin) {
    $sql = $isAdmin
        ? "SELECT registration_id, vehicle_plate, car_brand, model, vehicle_type, COALESCE(NULLIF(passenger_capacity,''), NULL) AS passenger_capacity FROM vehicles ORDER BY vehicle_plate ASC"
        : "SELECT registration_id, vehicle_plate, car_brand, model, vehicle_type, COALESCE(NULLIF(passenger_capacity,''), NULL) AS passenger_capacity FROM vehicles WHERE user_id = :uid ORDER BY vehicle_plate ASC";
    $stmt = $isAdmin ? $dbh->query($sql) : $dbh->prepare($sql);
    if (!$isAdmin) $stmt->execute([':uid' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$vehicles = fetch_vehicles($dbh, $user_id, $isAdmin);

// --- Reservation Fetch ---
function get_reservation(PDO $dbh, $user_id, $id, $isAdmin = false) {
    $sql = $isAdmin
        ? 'SELECT * FROM vehicle_reservations WHERE id = :id'
        : 'SELECT * FROM vehicle_reservations WHERE id = :id AND user_id = :uid';
    $stmt = $dbh->prepare($sql);
    $params = $isAdmin ? [':id' => (int)$id] : [':id' => (int)$id, ':uid' => $user_id];
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// --- POST Actions ---
function handle_post($dbh, $user_id, $isAdmin) {
    $action = p('action');
    $id = (int)p('id');
    $res = get_reservation($dbh, $user_id, $id, $isAdmin);

    if (!$res) { flash('error', 'Reservation not found.'); header('Location: index.php'); exit(); }

    switch ($action) {
        case 'approve':
            if (in_array($res['status'], ['Cancelled','Completed'], true)) {
                flash('error', 'Cannot approve a ' . $res['status'] . ' reservation.');
            } else {
                $sql = $isAdmin
                    ? "UPDATE vehicle_reservations SET status='Approved' WHERE id=:id"
                    : "UPDATE vehicle_reservations SET status='Approved' WHERE id=:id AND user_id=:uid";
                $params = $isAdmin ? [':id' => $id] : [':id' => $id, ':uid' => $user_id];
                $ok = $dbh->prepare($sql)->execute($params);
                flash($ok ? 'success' : 'error', $ok ? 'Reservation approved.' : 'Update failed.');
            }
            break;

        case 'cancel':
            if ($res['status'] === 'Completed') {
                flash('error', 'Cannot cancel a completed reservation.');
            } else {
                $sql = $isAdmin
                    ? "UPDATE vehicle_reservations SET status='Cancelled' WHERE id=:id"
                    : "UPDATE vehicle_reservations SET status='Cancelled' WHERE id=:id AND user_id=:uid";
                $params = $isAdmin ? [':id' => $id] : [':id' => $id, ':uid' => $user_id];
                $ok = $dbh->prepare($sql)->execute($params);
                flash($ok ? 'success' : 'error', $ok ? 'Reservation cancelled.' : 'Update failed.');
            }
            break;

        case 'dispatch':
            $assigned_driver = p('assigned_driver');
            $driver_contact = p('driver_contact');
            $dispatch_time = to_mysql_dt(p('dispatch_time'));
            $odometer_start = p('odometer_start');
            $odometer_start = ($odometer_start === '' ? null : (int)$odometer_start);

            if (!in_array($res['status'], ['Pending','Approved'], true)) {
                flash('error', 'Only pending or approved reservations can be dispatched.');
            } elseif ($assigned_driver === '' || !$dispatch_time) {
                flash('error', 'Driver and dispatch time are required.');
            } else {
                $sql = $isAdmin
                    ? 'UPDATE vehicle_reservations SET status="Dispatched", assigned_driver=:ad, driver_contact=:dc, dispatch_time=:dt, odometer_start=:os WHERE id=:id'
                    : 'UPDATE vehicle_reservations SET status="Dispatched", assigned_driver=:ad, driver_contact=:dc, dispatch_time=:dt, odometer_start=:os WHERE id=:id AND user_id=:uid';
                $params = [':ad' => $assigned_driver, ':dc' => $driver_contact, ':dt' => $dispatch_time, ':os' => $odometer_start, ':id' => $id];
                if (!$isAdmin) $params[':uid'] = $user_id;
                $ok = $dbh->prepare($sql)->execute($params);
                flash($ok ? 'success' : 'error', $ok ? 'Reservation dispatched.' : 'Failed to update dispatch.');
            }
            break;

        case 'complete':
            $arrival_time = to_mysql_dt(p('arrival_time'));
            $odometer_end = (int)p('odometer_end');
            $notes = p('notes');
            if ($res['status'] !== 'Dispatched') {
                flash('error', 'Only dispatched reservations can be completed.');
            } elseif (!$arrival_time || $odometer_end <= 0) {
                flash('error', 'Arrival time and valid odometer end are required.');
            } elseif ($res['odometer_start'] !== null && (int)$res['odometer_start'] > $odometer_end) {
                flash('error', 'Odometer end cannot be less than start.');
            } else {
                $sql = $isAdmin
                    ? 'UPDATE vehicle_reservations SET status="Completed", arrival_time=:arr, odometer_end=:oe, notes=:n WHERE id=:id'
                    : 'UPDATE vehicle_reservations SET status="Completed", arrival_time=:arr, odometer_end=:oe, notes=:n WHERE id=:id AND user_id=:uid';
                $params = [':arr' => $arrival_time, ':oe' => $odometer_end, ':n' => $notes, ':id' => $id];
                if (!$isAdmin) $params[':uid'] = $user_id;
                $ok = $dbh->prepare($sql)->execute($params);
                flash($ok ? 'success' : 'error', $ok ? 'Reservation completed.' : 'Failed to complete reservation.');
            }
            break;
    }
    header('Location: index.php'); exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') handle_post($dbh, $user_id, $isAdmin);

// --- Filters & Counts ---
$allowedFilters = ['All','Pending','Approved','Dispatched','Completed','Cancelled'];
$filter = in_array(q('status', 'All'), $allowedFilters, true) ? q('status', 'All') : 'All';
$viewParam = q('view', '');
$viewAll = ($viewParam === 'all') || $isAdmin;

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
$countsUser = fetch_counts($dbh, $user_id);
$countsAll = fetch_counts_all($dbh);
$sumUser = array_sum($countsUser);
$sumAll  = array_sum($countsAll);

// --- Reservations ---
function fetch_reservations($dbh, $user_id, $filter, $viewAll) {
    if ($filter === 'All') {
        if ($viewAll) {
            $stmt = $dbh->query('SELECT * FROM vehicle_reservations ORDER BY pickup_datetime DESC LIMIT 300');
        } else {
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
$reservations = fetch_reservations($dbh, $user_id, $filter, $viewAll);

// --- Flash Message ---
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vehicle Reservation & Dispatch</title>
    <link rel="stylesheet" href="../dispatchsystem/style.css" />
</head>
<body>
<header>
    <h1>Vehicle Dispatch</h1>
    <a href="../connections/auth/index.php">return</a>
</header>
<main>
    <?php if ($flash): ?>
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    
        <section class="panel">
            <h2 style="margin-top:0">Reservations</h2>
            <nav class="tabs">
                <?php foreach ($allowedFilters as $st):
                    $active = ($filter === $st) ? 'active' : '';
                    $cnt = ($st === 'All')
                        ? ($viewAll ? $sumAll : $sumUser)
                        : ($viewAll ? ($countsAll[$st] ?? 0) : ($countsUser[$st] ?? 0));
                    $qs = '?status=' . urlencode($st) . ($viewAll ? '&view=all' : '');
                ?>
                    <a class="<?= $active ?>" href="<?= e($qs) ?>"><?= e($st) ?> (<?= (int)$cnt ?>)</a>
                <?php endforeach; ?>
            </nav>
            <div style="max-height:520px;overflow:auto;border:1px solid var(--border);border-radius:8px">
            <table>
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Vehicle</th>
                        <th>When</th>
                        <th>Route</th>
                        <th>Req/Purpose</th>
                        <th>Pax</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reservations)): ?>
                    <tr><td colspan="8" class="muted">No reservations yet.</td></tr>
                <?php else: foreach ($reservations as $r): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;"><?= e($r['reservation_ref']) ?></div>
                            <div class="hint">#<?= (int)$r['id'] ?></div>
                        </td>
                        <td>
                            <div style="font-weight:600;"><?= e($r['vehicle_plate']) ?></div>
                            <div class="hint">Reg: <?= e($r['vehicle_registration_id']) ?></div>
                        </td>
                        <td>
                            <div><?= e(date('M d, Y', strtotime($r['trip_date']))) ?></div>
                            <div class="hint"><?= e(date('H:i', strtotime($r['pickup_datetime']))) ?> → <?= e(date('H:i', strtotime($r['dropoff_datetime']))) ?></div>
                        </td>
                        <td><?= e($r['pickup_location']) ?> → <?= e($r['dropoff_location']) ?></td>
                        <td>
                            <div><?= e($r['requester_name']) ?></div>
                            <div class="hint"><?= e($r['purpose']) ?></div>
                        </td>
                        <td><?= (int)$r['passengers_count'] ?></td>
                        <td><span class="status s-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                        <td>
                            <div class="actions">
                                <?php if ($r['status'] === 'Pending'): ?>
                                    <form method="post" onsubmit="return confirm('Approve this reservation?')">
                                        <input type="hidden" name="action" value="approve" />
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
                                        <button class="btn btn-blue" type="submit">Approve</button>
                                    </form>
                                    <form method="post" onsubmit="return confirm('Cancel this reservation?')">
                                        <input type="hidden" name="action" value="cancel" />
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
                                        <button class="btn btn-danger" type="submit">Cancel</button>
                                    </form>
                                <?php endif; ?>

                                <?php if (in_array($r['status'], ['Approved','Pending'], true)): ?>
                                    <button class="btn btn-primary" onclick="openDispatchModal(<?= (int)$r['id'] ?>)">Dispatch</button>
                                <?php endif; ?>

                                <?php if ($r['status'] === 'Dispatched'): ?>
                                    <button class="btn btn-warn" onclick="openCompleteModal(<?= (int)$r['id'] ?>)">Complete</button>
                                <?php endif; ?>
                                
                            </div>
                            <?php if (!empty($r['assigned_driver'])): ?>
                                <div class="hint" style="margin-top:6px;">Driver: <?= e($r['assigned_driver']) ?> <?= $r['driver_contact'] ? '(' . e($r['driver_contact']) . ')' : '' ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </section>
</main>

<div id="dispatchModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
    <div id="dispatchModalContent" style="background:#0b1022;padding:24px;border-radius:12px;max-width:400px;width:90%;position:relative;">
        <button onclick="closeDispatchModal()" style="position:absolute;top:8px;right:8px;">&times;</button>
        <div id="dispatchModalBody"></div>
    </div>
</div>

<div id="completeModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
    <div id="completeModalContent" style="background:#0b1022;padding:24px;border-radius:12px;max-width:400px;width:90%;position:relative;">
        <button onclick="closeCompleteModal()" style="position:absolute;top:8px;right:8px;">&times;</button>
        <div id="completeModalBody"></div>
    </div>
</div>

<script>
let ws = new WebSocket('ws://localhost:8080');
function reloadTable() {
    fetch(window.location.pathname + window.location.search)
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('table');
            if (newTable) {
                const oldTable = document.querySelector('table');
                if (oldTable) oldTable.parentNode.replaceChild(newTable, oldTable);
            }
        });
}
ws.onmessage = reloadTable;
setInterval(reloadTable, 1000);

function openDispatchModal(resId) {
    fetch('dispatch_form.php?id=' + resId)
        .then(res => res.text())
        .then(html => {
            document.getElementById('dispatchModalBody').innerHTML = html;
            document.getElementById('dispatchModal').style.display = 'flex';
        });
}
function closeDispatchModal() {
    document.getElementById('dispatchModal').style.display = 'none';
}

function openCompleteModal(resId) {
    fetch('complete_form.php?id=' + resId)
        .then(res => res.text())
        .then(html => {
            document.getElementById('completeModalBody').innerHTML = html;
            document.getElementById('completeModal').style.display = 'flex';
        });
}
function closeCompleteModal() {
    document.getElementById('completeModal').style.display = 'none';
}
function openCompleteModal(resId) {
    fetch('complete_form.php?id=' + resId)
        .then(res => res.text())
        .then(html => {
            document.getElementById('completeModalBody').innerHTML = html;
            document.getElementById('completeModal').style.display = 'flex';
        });
}
function closeCompleteModal() {
    document.getElementById('completeModal').style.display = 'none';
}
</script>
</body>
</html>
