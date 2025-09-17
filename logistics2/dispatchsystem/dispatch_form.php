<?php
// filepath: c:\xampp\htdocs\PM-TNVS\logistics2\dispatchsystem\dispatch_form.php
include('../../database/connect.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { echo '<div class="muted">Reservation not found.</div>'; exit; }

// Fetch driver name (vehicle owner). Fallback to reservation user if not found.
$ownerName = '';
if (!empty($r['vehicle_registration_id'])) {
    $stmtOwner = $dbh->prepare('
       SELECT 
    u.firstname, 
    u.lastname, 
    vr.vehicle_registration_id, 
    v.vehicle_plate
FROM vehicle_reservations vr
INNER JOIN vehicles v 
    ON vr.vehicle_registration_id = v.registration_id
INNER JOIN users u 
    ON v.user_id = u.user_id
WHERE vr.vehicle_registration_id = :reg
LIMIT 1;

    ');
    $stmtOwner->execute([':reg' => $r['vehicle_registration_id']]);
    if ($rowOwner = $stmtOwner->fetch(PDO::FETCH_ASSOC)) {
        $ownerName = htmlspecialchars($rowOwner['firstname'] . ' ' . $rowOwner['lastname'], ENT_QUOTES, 'UTF-8');
    }
}

if ($ownerName === '' && !empty($r['user_id'])) {
    $stmtUser = $dbh->prepare('SELECT firstname, lastname FROM users WHERE user_id = :uid LIMIT 1');
    $stmtUser->execute([':uid' => $r['user_id']]);
    if ($rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC)) {
        $ownerName = htmlspecialchars($rowUser['firstname'] . ' ' . $rowUser['lastname'], ENT_QUOTES, 'UTF-8');
    }
}
?>
<form method="post" style="margin-top:8px;">
    <input type="hidden" name="action" value="dispatch" />
    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
    <div class="row">
        <div>
            <label>Driver</label>
            <?php if ($ownerName): ?>
                 <input type="text" name="assigned_driver" value="<?= $ownerName ?>" readonly required />
            <?php else: ?>
                <span class="muted">No driver assigned yet</span>
                  <input type="text" name="assigned_driver" value="" required />
            <?php endif; ?>
            
        </div>
        <div>
            <label>Driver Contact</label>
            <input type="text" name="driver_contact" placeholder="Contact number" />
        </div>
    </div>
    <div class="row">
        <div>
            <label>Dispatch Time</label>
            <input type="datetime-local" name="dispatch_time" value="<?= htmlspecialchars(date('Y-m-d\TH:i')) ?>" required />
        </div>
        <div>
            <label>Odometer Start</label>
            <input type="number" name="odometer_start" min="0" step="1" />
        </div>
    </div>
    <div class="actions" style="margin-top:8px">
        <button class="btn btn-primary" type="submit">Mark as Dispatched</button>
    </div>
</form>