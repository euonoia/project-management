<?php
// History: secure reservation and cost overview for the current session user
// - Session check; redirect to login if not authenticated
// - Joins: users (vehicle owner), vehicles, vehicle_reservations, cost_analysis
// - Shows: owner name, conduction_sticker, car_brand, model, color, vehicle_plate,
//          trip_date, pickup_datetime, dropoff_datetime, pickup_location,
//          dropoff_location, requester_name, status, assigned_driver, driver_contact,
//          arrival_time, total driver_earnings (per reservation)

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

session_start();
require_once('../../../../database/connect.php');

// Require login. Same session keys are established by admin_login.php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$session_uid  = (string)($_SESSION['user_id'] ?? ''); // firebase_uid or app user_id
$session_role = (string)($_SESSION['role'] ?? 'user');
$session_name = trim(((string)($_SESSION['firstname'] ?? '') . ' ' . (string)($_SESSION['lastname'] ?? '')));
if ($session_name === '') { $session_name = 'User'; }

// Helper: HTML escape
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Filters & pagination
$kw      = trim((string)($_GET['q'] ?? ''));
$page    = max(1, (int)($_GET['p'] ?? 1));
$pageSz  = max(1, min(100, (int)($_GET['ps'] ?? 25)));
$offset  = ($page - 1) * $pageSz;
$export  = isset($_GET['export']) && $_GET['export'] === '1';
// Non-admins can only see their own reservations. Admins can toggle mine=1 to filter to self.
$mine    = isset($_GET['mine']) ? ($_GET['mine'] === '1') : ($session_role !== 'admin');

// Build WHERE clause
$where  = [];
$params = [];
if ($mine) {
    // Important: enforce row-level filter to protect user privacy
    // Note: Some historical data may store firebase_uid in vr.user_id or ca.user_id. Support both.
    $where[] = '(vr.user_id = :uid OR ca.user_id = :uid)';
    $params[':uid'] = $session_uid;
}
if ($kw !== '') {
    $where[] = '(
        vr.reservation_ref LIKE :kw OR
        vr.vehicle_plate LIKE :kw OR
        v.registration_id LIKE :kw OR
        v.conduction_sticker LIKE :kw OR
        v.car_brand LIKE :kw OR
        v.model LIKE :kw OR
        v.color LIKE :kw OR
        uo.firstname LIKE :kw OR
        uo.lastname LIKE :kw OR
        vr.pickup_location LIKE :kw OR
        vr.dropoff_location LIKE :kw
    )';
    $params[':kw'] = '%'.$kw.'%';
}
$WSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Total distinct reservations (for pagination)
$totalRows = 0;
try {
    $sqlCount = 'SELECT COUNT(DISTINCT vr.reservation_ref) FROM vehicle_reservations vr
        JOIN vehicles v ON v.registration_id = vr.vehicle_registration_id
        LEFT JOIN users uo ON uo.user_id = v.user_id
        LEFT JOIN cost_analysis ca ON ca.reservation_ref = vr.reservation_ref
        ' . $WSQL;
    $stmt = $dbh->prepare($sqlCount);
    $stmt->execute($params);
    $totalRows = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
    $totalRows = 0;
}

// Core query: aggregate driver earnings per reservation
$qBase = 'SELECT
    vr.reservation_ref,
    vr.vehicle_registration_id,
    vr.vehicle_plate,
    vr.trip_date,
    vr.pickup_datetime,
    vr.dropoff_datetime,
    vr.pickup_location,
    vr.dropoff_location,
    vr.requester_name,
    vr.status,
    vr.assigned_driver,
    vr.driver_contact,
    vr.arrival_time,
    v.conduction_sticker,
    v.car_brand,
    v.model,
    v.color,
    COALESCE(uo.firstname, "") AS owner_firstname,
    COALESCE(uo.lastname,  "") AS owner_lastname,
    COALESCE(SUM(ca.driver_earnings), 0) AS total_driver_earnings
FROM vehicle_reservations vr
JOIN vehicles v ON v.registration_id = vr.vehicle_registration_id
LEFT JOIN users uo ON uo.user_id = v.user_id -- owner of the vehicle
LEFT JOIN cost_analysis ca ON ca.reservation_ref = vr.reservation_ref
' . $WSQL . '
GROUP BY
    vr.reservation_ref,
    vr.vehicle_registration_id,
    vr.vehicle_plate,
    vr.trip_date,
    vr.pickup_datetime,
    vr.dropoff_datetime,
    vr.pickup_location,
    vr.dropoff_location,
    vr.requester_name,
    vr.status,
    vr.assigned_driver,
    vr.driver_contact,
    vr.arrival_time,
    v.conduction_sticker,
    v.car_brand,
    v.model,
    v.color,
    uo.firstname,
    uo.lastname
ORDER BY vr.pickup_datetime DESC';

$rows = [];
try {
    $sql = $qBase . ($export ? '' : ' LIMIT :lim OFFSET :off');
    $stmt = $dbh->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    if (!$export) {
        $stmt->bindValue(':lim', $pageSz, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
}

// CSV export for transparency
if ($export) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="reservation_history.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, [
        'Reservation Ref', 'Owner Name', 'Registration ID', 'Vehicle Plate', 'Conduction Sticker', 'Brand', 'Model', 'Color',
        'Trip Date', 'Pickup Datetime', 'Dropoff Datetime', 'Pickup Location', 'Dropoff Location',
        'Requester Name', 'Status', 'Assigned Driver', 'Driver Contact', 'Arrival Time', 'Total Driver Earnings'
    ]);
    foreach ($rows as $r) {
        $owner = trim(($r['owner_firstname'] ?? '') . ' ' . ($r['owner_lastname'] ?? ''));
        fputcsv($out, [
            (string)$r['reservation_ref'],
            $owner,
            (string)$r['vehicle_registration_id'],
            (string)$r['vehicle_plate'],
            (string)$r['conduction_sticker'],
            (string)$r['car_brand'],
            (string)$r['model'],
            (string)$r['color'],
            (string)$r['trip_date'],
            (string)$r['pickup_datetime'],
            (string)$r['dropoff_datetime'],
            (string)$r['pickup_location'],
            (string)$r['dropoff_location'],
            (string)$r['requester_name'],
            (string)$r['status'],
            (string)$r['assigned_driver'],
            (string)$r['driver_contact'],
            (string)$r['arrival_time'],
            number_format((float)$r['total_driver_earnings'], 2, '.', ''),
        ]);
    }
    fclose($out);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reservation History</title>
  <link rel="stylesheet" href="../style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="layout">
  <div class="main-area">
    <aside class="sidebar" id="sidebar">
      <div class="brand">
        <button id="sidebarToggle" aria-label="Toggle sidebar" style="background:none;border:none;outline:none;cursor:pointer;padding:0 0 8px 0;display:flex;align-items:center;">
          <svg id="sidebarToggleIcon" width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="6" width="20" height="2.5" rx="1.25" fill="#007bff"/>
            <rect x="4" y="13" width="20" height="2.5" rx="1.25" fill="#007bff"/>
            <rect x="4" y="20" width="20" height="2.5" rx="1.25" fill="#007bff"/>
          </svg>
        </button>
        <div class="logo">AD</div>
        <div>
          <h1>History</h1>
          <div class="subtitle">Secure travel records</div>
        </div>
      </div>
      <nav class="sidenav">
        <a href="../index.php">Dashboard</a>
        <a href="../../../dispatchsystem/index.php">Reservations</a>
        <a href="history.php" class="active">Travel Records</a>
        <a href="users.php">Users</a>
        <a href="drivers.php">Drivers</a>
         <hr style="border-color:var(--border)">
        <a href="../logout.php">Logout</a>
      </nav>
    </aside>
    <div class="content-area" style="display:flex;flex-direction:column;flex:1 1 0%;min-width:0;">
      <header class="topbar">
        <div>
          <div class="muted" style="font-size:14px">Welcome, <?php echo e($session_name); ?></div>
          <div style="font-weight:800; font-size:18px; letter-spacing:.3px">Reservation History</div>
        </div>
        <div class="userbox">
          <span class="pill"><?php echo e(strtoupper($session_role)); ?></span>
          <div class="avatar"><?php echo e(strtoupper(substr($session_name,0,1))); ?></div>
        </div>
      </header>
      <main class="content">

        <section class="panel card">
          <h2 style="margin:0 0 6px">Travel Records</h2>
          <div class="muted" style="margin-bottom:8px">
            <?php if ($mine) : ?>
              Showing only your reservations to protect your privacy.
            <?php else: ?>
              Admin view: Showing all reservations.
            <?php endif; ?>
          </div>

          <div class="toolbar">
            <form class="search" method="get" action="">
              <input type="text" name="q" value="<?php echo e($kw); ?>" placeholder="Search ref, plate, owner, brand, location..." />
              <select name="ps">
                <?php foreach ([25,50,100] as $ps): ?>
                  <option value="<?php echo (int)$ps; ?>" <?php echo ($pageSz === $ps ? 'selected' : ''); ?>>Show <?php echo (int)$ps; ?></option>
                <?php endforeach; ?>
              </select>
              <?php if ($session_role === 'admin'): ?>
              <?php else: ?>

              <?php endif; ?>
              <button class="btn" type="submit">Apply</button>
            </form>
            <div>
              <a class="btn-ghost" href="?<?php echo http_build_query(array_merge($_GET, ['export' => '1', 'p' => 1])); ?>">Export CSV</a>
            </div>
          </div>

          <div class="table-responsive" style="max-height:560px; max-width:100%; overflow-x:auto; overflow-y:auto; border:1px solid var(--border); border-radius:10px">
            <table style="width:100%; min-width:900px;">
              <thead>
                <tr>
                  <th>Reservation Ref</th>
                  <th>Owner Name</th>
                  <th>Registration ID</th>
                  <th>Vehicle Plate</th>
                  <th>Conduction Sticker</th>
                  <th>Brand</th>
                  <th>Model</th>
                  <th>Color</th>
                  <th>Trip Date</th>
                  <th>Pickup Datetime</th>
                  <th>Dropoff Datetime</th>
                  <th>Pickup Location</th>
                  <th>Dropoff Location</th>
                  <th>Requester</th>
                  <th>Status</th>
                  <th>Assigned Driver</th>
                  <th>Driver Contact</th>
                  <th>Arrival Time</th>
                  <th>Total Driver Earnings</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($rows)) : ?>
                  <tr><td class="muted" colspan="19">No records found.</td></tr>
                <?php else: foreach ($rows as $r) :
                  $owner = trim(((string)($r['owner_firstname'] ?? '')) . ' ' . ((string)($r['owner_lastname'] ?? '')));
                ?>
                  <tr>
                    <td><?php echo e($r['reservation_ref'] ?? ''); ?></td>
                    <td><?php echo e($owner); ?></td>
                    <td><?php echo e($r['vehicle_registration_id'] ?? ''); ?></td>
                    <td><?php echo e($r['vehicle_plate'] ?? ''); ?></td>
                    <td><?php echo e($r['conduction_sticker'] ?? ''); ?></td>
                    <td><?php echo e($r['car_brand'] ?? ''); ?></td>
                    <td><?php echo e($r['model'] ?? ''); ?></td>
                    <td><?php echo e($r['color'] ?? ''); ?></td>
                    <td><?php echo e($r['trip_date'] ?? ''); ?></td>
                    <td><?php echo e($r['pickup_datetime'] ?? ''); ?></td>
                    <td><?php echo e($r['dropoff_datetime'] ?? ''); ?></td>
                    <td><?php echo e($r['pickup_location'] ?? ''); ?></td>
                    <td><?php echo e($r['dropoff_location'] ?? ''); ?></td>
                    <td><?php echo e($r['requester_name'] ?? ''); ?></td>
                    <td><?php echo e($r['status'] ?? ''); ?></td>
                    <td><?php echo e($r['assigned_driver'] ?? ''); ?></td>
                    <td><?php echo e($r['driver_contact'] ?? ''); ?></td>
                    <td><?php echo e($r['arrival_time'] ?? ''); ?></td>
                    <td><?php echo '₱ ' . number_format((float)($r['total_driver_earnings'] ?? 0), 2); ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>

          <?php $maxPage = ($totalRows > 0) ? (int)ceil($totalRows / $pageSz) : 1; ?>
          <div class="toolbar" style="margin-top:10px">
            <div class="muted">Showing <?php echo (int)min($page * $pageSz, max(0, $totalRows)); ?> of <?php echo (int)$totalRows; ?> reservations</div>
            <div>
              <?php if ($page > 1): ?>
                <a class="btn-ghost" href="?<?php echo http_build_query(array_merge($_GET, ['p' => $page - 1])); ?>">Prev</a>
              <?php endif; ?>
              <?php if ($page < $maxPage): ?>
                <a class="btn-ghost" href="?<?php echo http_build_query(array_merge($_GET, ['p' => $page + 1])); ?>">Next</a>
              <?php endif; ?>
            </div>
          </div>
        </section>

        <?php if ($session_role !== 'admin'): ?>
          <p class="muted" style="margin-top:14px">
            Privacy note: Only your own reservations are shown on this page. For safety, precise pickup/drop-off addresses are visible only to you and authorized admins.
          </p>
        <?php endif; ?>

      </main>
    </div>
  </div>
</div>
<script>
// Sidebar toggle persistence (reuse pattern from index.php)
document.addEventListener('DOMContentLoaded', function() {
  var sidebar = document.getElementById('sidebar');
  var toggle = document.getElementById('sidebarToggle');
  var icon = document.getElementById('sidebarToggleIcon');
  if (localStorage.getItem('sidebar-collapsed') === '1') {
    sidebar.classList.add('collapsed');
    if (icon) icon.style.transform = 'rotate(180deg)';
  }
  if (toggle && sidebar) {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      sidebar.classList.toggle('collapsed');
      var isCollapsed = sidebar.classList.contains('collapsed');
      if (icon) icon.style.transform = isCollapsed ? 'rotate(180deg)' : '';
      localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
    });
  }
});
</script>
</body>
</html>
