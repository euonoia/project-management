<?php
include_once 'includes/db.php';
include_once 'includes/auth.php';
include_once 'includes/helpers.php';
include_once 'includes/vehicles.php';
include_once 'includes/reservations.php';
include_once 'includes/counts.php';
include_once 'actions/reservation_actions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') handle_post($dbh, $user_id, $isAdmin);

// Filters, counts, reservations
$allowedFilters = ['All','Pending','Approved','Dispatched','Completed','Cancelled'];
$filter = in_array(q('status', 'All'), $allowedFilters, true) ? q('status', 'All') : 'All';
$viewAll = (q('view', '') === 'all') || $isAdmin;

$vehicles = fetch_vehicles($dbh, $user_id, $isAdmin);
$countsUser = fetch_counts($dbh, $user_id);
$countsAll  = fetch_counts_all($dbh);
$sumUser = array_sum($countsUser);
$sumAll  = array_sum($countsAll);
$reservations = fetch_reservations($dbh, $user_id, $filter, $viewAll);
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
