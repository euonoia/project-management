<?php
function handle_post($dbh, $user_id, $isAdmin) {
    $action = p('action');
    $id = (int)p('id');
    $res = get_reservation($dbh, $user_id, $id, $isAdmin);

    if (!$res) { flash('error', 'Reservation not found.'); header('Location: index.php'); exit(); }

    switch ($action) {
        case 'approve':
        case 'cancel':
        case 'dispatch':
        case 'complete':
            include __DIR__ . "/actions/{$action}.php";
            break;
    }

    header('Location: index.php');
    exit();
}
