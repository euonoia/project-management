<?php
include_once __DIR__ . '/../includes/reservations.php';
include_once __DIR__ . '/../includes/helpers.php';

function handle_post($dbh, $user_id, $isAdmin) {
    $action = p('action');
    $id = (int)p('id');
    $res = get_reservation($dbh, $user_id, $id, $isAdmin);

    $redirect = '/project-management/logistics2/dispatchsystem/index.php';

    if (!$res) {
        flash('error', 'Reservation not found.');
        header('Location: ' . $redirect);
        exit();
    }

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
                $params = [
                    ':ad' => $assigned_driver,
                    ':dc' => $driver_contact,
                    ':dt' => $dispatch_time,
                    ':os' => $odometer_start,
                    ':id' => $id
                ];
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
                try {
                    $dbh->beginTransaction();

                    // Insert into history table
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
                    $deleteSql = $isAdmin
                        ? "DELETE FROM vehicle_reservations WHERE id=:id"
                        : "DELETE FROM vehicle_reservations WHERE id=:id AND user_id=:uid";

                    $deleteParams = $isAdmin ? [':id' => $id] : [':id' => $id, ':uid' => $user_id];
                    $dbh->prepare($deleteSql)->execute($deleteParams);

                    $dbh->commit();
                    flash('success', 'Reservation completed and archived successfully.');
                } catch (Throwable $e) {
                    $dbh->rollBack();
                    flash('error', 'Failed to complete reservation: ' . $e->getMessage());
                }
            }
            break;
    }

    header('Location: ' . $redirect);
    exit();
}
?>
