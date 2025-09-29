<?php
// Safely get POST value
function p($key, $default = '') { 
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default; 
}

// Safely get GET value
function q($key, $default = '') { 
    return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default; 
}

// Escape output for HTML
function e($str) { 
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
}

// Convert datetime string to MySQL format
function to_mysql_dt($val) {
    if ($val === '' || $val === null) return null;
    $val = str_replace('T', ' ', $val);
    $ts = strtotime($val);
    return $ts ? date('Y-m-d H:i:s', $ts) : null;
}

// Set a flash message
function flash($type, $msg) { 
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; 
}

// Get and clear the flash message
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); // remove after reading
        return $flash;
    }
    return null;
}

// Get reservation progress percentage
function get_progress_percent($status) {
    switch ($status) {
        case 'Pending': return 20;
        case 'Approved': return 40;
        case 'Dispatched': return 70;
        case 'Completed': 
        case 'Cancelled': return 100;
        default: return 0;
    }
}

// Fetch a specific reservation
function get_reservation(PDO $dbh, $user_id, $id) {
    $stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE id = :id AND user_id = :uid');
    $stmt->execute([':id' => (int)$id, ':uid' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
?>
