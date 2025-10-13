<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $reservation_id = (int)($_POST['reservation_id'] ?? 0);

    if ($action === 'cancel' && $reservation_id > 0) {
        // Cancel/Delete reservation
        $stmt = $dbh->prepare("DELETE FROM vehicle_reservations WHERE id = :id");
        $stmt->execute([':id' => $reservation_id]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Reservation deleted successfully.'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'complete' && $reservation_id > 0) {
        // Fetch reservation
        $stmt = $dbh->prepare("SELECT * FROM vehicle_reservations WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $reservation_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res || $res['status'] !== 'Dispatched') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Only dispatched reservations can be completed.'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        $arrival_time = $_POST['arrival_time'] ?? date('Y-m-d H:i');
        $odometer_end = (int)($_POST['odometer_end'] ?? 0);
        $notes = $_POST['notes'] ?? null;

        if ($res['odometer_start'] !== null && $res['odometer_start'] > $odometer_end) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Odometer end cannot be less than start.'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        try {
            $dbh->beginTransaction();

            // Insert into history
            $historySql = "INSERT INTO vehicle_reservations_history
                (id, user_id, reservation_ref, vehicle_registration_id, vehicle_plate, passengers_count, trip_date, pickup_datetime, dropoff_datetime, pickup_location, dropoff_location, status, assigned_driver, driver_contact, dispatch_time, arrival_time, odometer_start, odometer_end, requester_name, purpose, notes, created_at)
                VALUES
                (:id, :uid, :ref, :vid, :plate, :passengers, :trip, :pickup, :dropoff, :pickup_loc, :dropoff_loc, 'Completed', :ad, :dc, :dt, :arr, :os, :oe, :requester, :purpose, :notes, :created)";

            $historyParams = [
                ':id' => $res['id'],
                ':uid' => $res['user_id'],
                ':ref' => $res['reservation_ref'],
                ':vid' => $res['vehicle_registration_id'],
                ':plate' => $res['vehicle_plate'],
                ':passengers' => $res['passengers_count'],
                ':trip' => $res['trip_date'],
                ':pickup' => $res['pickup_datetime'],
                ':dropoff' => $res['dropoff_datetime'],
                ':pickup_loc' => $res['pickup_location'],
                ':dropoff_loc' => $res['dropoff_location'],
                ':ad' => $res['assigned_driver'],
                ':dc' => $res['driver_contact'],
                ':dt' => $res['dispatch_time'],
                ':arr' => $arrival_time,
                ':os' => $res['odometer_start'],
                ':oe' => $odometer_end,
                ':requester' => $res['requester_name'],
                ':purpose' => $res['purpose'],
                ':notes' => $notes,
                ':created' => $res['created_at']
            ];

            $dbh->prepare($historySql)->execute($historyParams);

            // Delete from active reservations
            $dbh->prepare("DELETE FROM vehicle_reservations WHERE id = :id")->execute([':id' => $res['id']]);

            $dbh->commit();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Reservation completed and archived successfully.'];
        } catch (Throwable $e) {
            $dbh->rollBack();
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Failed to complete reservation: ' . $e->getMessage()];
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<div class="max-w-4xl mx-auto px-4">

    <!-- Heading row -->
    <div class="flex justify-start mb-8">
        <h3 class="text-2xl font-bold text-gray-800">Active Reservations</h3>
    </div>

    <?php if (empty($active_reservations)): ?>
        <div class="text-gray-500 text-center py-20">You have no active reservations.</div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($active_reservations as $res): ?>
                <?php
                    $status = strtolower($res['status']);
                    $statusData = [
                        'pending' => ['color' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fas fa-hourglass-half'],
                        'dispatched' => ['color' => 'bg-blue-100 text-blue-800', 'icon' => 'fas fa-truck']
                    ];
                    $badgeColor = $statusData[$status]['color'] ?? 'bg-gray-100 text-gray-700';
                    $statusIcon = $statusData[$status]['icon'] ?? 'fas fa-circle';
                ?>

                <div class="w-full bg-white rounded-xl shadow-lg hover:shadow-2xl transition-transform transform hover:-translate-y-1 p-6 flex flex-col justify-between">

                    <!-- Reservation Info -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-lg text-gray-900"><?= e($res['reservation_ref']) ?></h4>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?= $badgeColor ?>">
                                <i class="<?= $statusIcon ?> mr-2"></i>
                                <?= ucfirst($res['status']) ?>
                            </span>
                        </div>

                        <p class="text-gray-500 text-sm mb-2"><?= e(date('M d, Y', strtotime($res['trip_date']))) ?></p>

                        <div class="text-gray-600 text-sm space-y-2">
                          <div class="flex items-center gap-3">
                              <i class="fas fa-map-marker-alt text-red-500"></i>
                              <span><strong>Pick-up:</strong> <?= e($res['pickup_location']) ?></span>
                          </div>
                          <div class="flex items-center gap-3">
                              <i class="fas fa-flag-checkered text-green-500"></i>
                              <span><strong>Drop-off:</strong> <?= e($res['dropoff_location']) ?></span>
                          </div>
                          <div class="flex items-center gap-3">
                              <i class="fas fa-clock text-gray-400"></i>
                              <span><strong>Time:</strong> <?= e(date('H:i', strtotime($res['pickup_datetime']))) ?> - <?= e(date('H:i', strtotime($res['dropoff_datetime']))) ?></span>
                          </div>
                      </div>
                    </div>

                  
                        <!-- Action Buttons -->
                        <div class="mt-4 self-end flex gap-2">
                            <?php if ($status === 'pending'): ?>
                                <form method="post" class="inline-block">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="reservation_id" value="<?= (int)$res['id'] ?>">
                                    <button 
                                        type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition w-auto max-w-[200px]"
                                        onclick="return confirm('Are you sure you want to cancel this reservation?');"
                                    >
                                        Cancel
                                    </button>
                                </form>
                            <?php elseif ($status === 'dispatched'): ?>
                                <form method="post" class="inline-block">
                                    <input type="hidden" name="action" value="complete">
                                    <input type="hidden" name="reservation_id" value="<?= (int)$res['id'] ?>">
                                    <input type="hidden" name="arrival_time" value="<?= date('Y-m-d H:i') ?>">
                                    <input type="hidden" name="odometer_end" value="<?= $res['odometer_start'] ?? 0 ?>"> 
                                    <button 
                                        type="submit"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition w-auto max-w-[200px]"
                                        onclick="return confirm('Mark this reservation as complete?');"
                                    >
                                        Complete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- AJAX Script -->
<script>
document.querySelectorAll('.cancel-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;

        if (!confirm('Are you sure you want to cancel this reservation?')) return;

        fetch('cancel_reservation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Reservation cancelled');
                location.reload(); // refresh page to update table
            } else {
                alert(data.message || 'Failed to cancel');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error cancelling reservation');
        });
    });
});
</script>
