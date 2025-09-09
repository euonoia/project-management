<?php
include('../../database/connect.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { echo '<div class="muted">Reservation not found.</div>'; exit; }
?>
<form method="post" style="margin-top:8px;background:#0b1022;padding:8px;border-radius:8px">
    <input type="hidden" name="action" value="complete" />
    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
    <div class="row">
        <div>
            <label>Arrival Time</label>
            <input type="datetime-local" name="arrival_time" value="<?= htmlspecialchars(date('Y-m-d\TH:i')) ?>" required />
        </div>
        <div>
            <label>Odometer End</label>
            <input type="number" name="odometer_end" min="0" step="1" required />
        </div>
    </div>
    <div>
        <label>Notes</label>
        <textarea name="notes" placeholder="Observations, issues, etc."></textarea>
    </div>
    <div class="actions" style="margin-top:8px">
        <button class="btn btn-warn" type="submit">Mark as Completed</button>
    </div>
</form>