<?php
// Admin session bootstrap using Firebase UID
// - Creates `admin` table if not exists
// - Upserts admin by firebase_uid
// - Establishes PHP session used by mwhehe.php (admin panel)

header('Content-Type: application/json');
session_name('admin_session');
session_start();

try {
    // Include DB connection
    // Expected to define either $conn (mysqli) or $dbh (PDO)
    include('../../../database/connect.php');
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB connection include failed']);
    exit;
}

function json_error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

// Read input JSON or form data
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) { $body = $_POST; }

$uid = isset($body['uid']) ? trim((string)$body['uid']) : '';
$idToken = isset($body['idToken']) ? trim((string)$body['idToken']) : '';
$email = isset($body['email']) ? trim((string)$body['email']) : '';
$firstname = isset($body['firstName']) ? trim((string)$body['firstName']) : '';
$lastname = isset($body['lastName']) ? trim((string)$body['lastName']) : '';

if ($uid === '' && $idToken === '') {
    json_error('Missing uid or idToken');
}

// TODO (security): Verify Firebase ID token server-side and extract UID from claims.
if ($uid === '') { $uid = 'FIREBASE_UID_UNKNOWN'; }




$usedDriver = '';

// Helper functions abstracting DB operations for mysqli/PDO
$exec = function($sql) use (&$conn, &$dbh, &$usedDriver) {
    if (isset($conn) && $conn instanceof mysqli) {
        $usedDriver = 'mysqli';
        return $conn->query($sql);
    } elseif (isset($dbh) && $dbh instanceof PDO) {
        $usedDriver = 'pdo';
        return $dbh->exec($sql) !== false;
    }
    return false;
};

$selectAdmin = function($uid) use (&$conn, &$dbh, &$usedDriver) {
    if ($usedDriver === 'mysqli' && isset($conn) && $conn instanceof mysqli) {
        $stmt = $conn->prepare('SELECT id, firebase_uid, email, firstname, lastname, role FROM admin WHERE firebase_uid = ? LIMIT 1');
        if (!$stmt) return null;
        $stmt->bind_param('s', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    } elseif ($usedDriver === 'pdo' && isset($dbh) && $dbh instanceof PDO) {
        $stmt = $dbh->prepare('SELECT id, firebase_uid, email, firstname, lastname, role FROM admin WHERE firebase_uid = ? LIMIT 1');
        if (!$stmt) return null;
        $stmt->execute([$uid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    return null;
};

$updateAdmin = function($email, $firstname, $lastname, $uid) use (&$conn, &$dbh, &$usedDriver) {
    if ($email === '' && $firstname === '' && $lastname === '') return true;
    if ($usedDriver === 'mysqli' && isset($conn) && $conn instanceof mysqli) {
        $sql = "UPDATE admin SET email = COALESCE(NULLIF(?, ''), email), firstname = COALESCE(NULLIF(?, ''), firstname), lastname = COALESCE(NULLIF(?, ''), lastname) WHERE firebase_uid = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('ssss', $email, $firstname, $lastname, $uid);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    } elseif ($usedDriver === 'pdo' && isset($dbh) && $dbh instanceof PDO) {
        $sql = "UPDATE admin SET email = COALESCE(NULLIF(?, ''), email), firstname = COALESCE(NULLIF(?, ''), firstname), lastname = COALESCE(NULLIF(?, ''), lastname) WHERE firebase_uid = ?";
        $stmt = $dbh->prepare($sql);
        if (!$stmt) return false;
        return $stmt->execute([$email, $firstname, $lastname, $uid]);
    }
    return false;
};

$insertAdmin = function($uid, $email, $firstname, $lastname) use (&$conn, &$dbh, &$usedDriver) {
    if ($usedDriver === 'mysqli' && isset($conn) && $conn instanceof mysqli) {
        $stmt = $conn->prepare('INSERT INTO admin (firebase_uid, email, firstname, lastname, role) VALUES (?, ?, ?, ?, "admin")');
        if (!$stmt) return [false, 'prepare failed'];
        $stmt->bind_param('ssss', $uid, $email, $firstname, $lastname);
        $ok = $stmt->execute();
        $err = $stmt->error;
        $stmt->close();
        return [$ok, $err];
    } elseif ($usedDriver === 'pdo' && isset($dbh) && $dbh instanceof PDO) {
        $stmt = $dbh->prepare('INSERT INTO admin (firebase_uid, email, firstname, lastname, role) VALUES (?, ?, ?, ?, "admin")');
        if (!$stmt) return [false, 'prepare failed'];
        $ok = $stmt->execute([$uid, $email, $firstname, $lastname]);
        $err = '';
        return [$ok, $err];
    }
    return [false, 'no driver'];
};

// Ensure we have a usable driver
if (!(isset($conn) && $conn instanceof mysqli) && !(isset($dbh) && $dbh instanceof PDO)) {
    json_error('Database connection not available', 500);
}
// Select driver based on available connection
if (isset($conn) && $conn instanceof mysqli) {
    $usedDriver = 'mysqli';
} elseif (isset($dbh) && $dbh instanceof PDO) {
    $usedDriver = 'pdo';
}

// Upsert by firebase_uid
$admin = $selectAdmin($uid);
if ($admin) {
    // update details if provided
    $updateAdmin($email, $firstname, $lastname, $uid);
    $admin = $selectAdmin($uid) ?: $admin;
} else {
    [$ok, $err] = $insertAdmin($uid, $email, $firstname, $lastname);
    if (!$ok) {
        json_error('Insert failed: ' . $err, 500);
    }
    $admin = $selectAdmin($uid);
}

if (!$admin) {
    json_error('Admin record not found/created', 500);
}

// Establish session for admin panel
session_regenerate_id(true);

// ensure the rest of the app sees the same keys used elsewhere
$_SESSION['firebase_uid'] = $admin['firebase_uid'];
$_SESSION['user_id']     = $admin['firebase_uid']; // keep legacy key used in some pages
$_SESSION['firstname']   = $admin['firstname'];
$_SESSION['lastname']    = $admin['lastname'];
$_SESSION['email']       = $admin['email'];
$_SESSION['role']        = $admin['role'] ?? 'admin';

http_response_code(200);
echo json_encode([
    'ok' => true,
    'redirect' => '/project-management/logistics2/connections/auth/index.php',
]);
