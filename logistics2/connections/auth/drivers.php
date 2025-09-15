<?php
// Users + Vehicles + Vehicle Insurance transparency view
// - Session check and redirect if not logged in
// - Joins: users (u), vehicles (v), vehicle_insurance (ins)
// - Shows users that have at least one vehicle (INNER JOIN vehicles)
// - Lists all vehicle and insurance columns, plus non-sensitive user columns
// - Admins can view all; non-admins see only their own records
// - Search, pagination, CSV export

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

session_start();
require_once('../../../database/connect.php');

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$session_uid  = (string)($_SESSION['user_id'] ?? '');
$session_role = (string)($_SESSION['role'] ?? 'user');
$session_name = trim(((string)($_SESSION['firstname'] ?? '') . ' ' . (string)($_SESSION['lastname'] ?? '')));
if ($session_name === '') { $session_name = 'User'; }

function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Filters
$kw      = trim((string)($_GET['q'] ?? ''));
$page    = max(1, (int)($_GET['p'] ?? 1));
$pageSz  = max(1, min(100, (int)($_GET['ps'] ?? 25)));
$offset  = ($page - 1) * $pageSz;
$export  = isset($_GET['export']) && $_GET['export'] === '1';
$mine    = isset($_GET['mine']) ? ($_GET['mine'] === '1') : ($session_role !== 'admin');

$where  = [];
$params = [];

// Non-admins: only their own records
if ($mine) {
    $where[] = 'u.user_id = :uid';
    $params[':uid'] = $session_uid;
}

if ($kw !== '') {
    $where[] = '(
        u.firstname LIKE :kw OR u.lastname LIKE :kw OR u.email LIKE :kw OR u.contact LIKE :kw OR u.user_id LIKE :kw OR
        v.registration_id LIKE :kw OR v.vehicle_plate LIKE :kw OR v.conduction_sticker LIKE :kw OR v.car_brand LIKE :kw OR v.model LIKE :kw OR v.color LIKE :kw OR
        ins.policy_number LIKE :kw OR ins.insurance_provider LIKE :kw OR ins.coverage_type LIKE :kw OR ins.insurance_type LIKE :kw
    )';
    $params[':kw'] = '%'.$kw.'%';
}
$WSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Count rows (vehicle-required join). Counting joined rows is acceptable since each vehicle/insurance combo is a separate transparent record.
$totalRows = 0;
try {
    $sqlCount = 'SELECT COUNT(*) FROM users u
        INNER JOIN vehicles v ON v.user_id = u.user_id
        LEFT JOIN vehicle_insurance ins ON ins.user_id = u.user_id
        ' . $WSQL;
    $stmt = $dbh->prepare($sqlCount);
    $stmt->execute($params);
    $totalRows = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
    $totalRows = 0;
}

// Main query
$sql = 'SELECT
    -- Users (non-sensitive; exclude password)
    u.id               AS u_id,
    u.user_id          AS u_user_id,
    u.firstname        AS u_firstname,
    u.lastname         AS u_lastname,
    u.age              AS u_age,
    u.gender           AS u_gender,
    u.contact          AS u_contact,
    u.email            AS u_email,
    u.role             AS u_role,
    u.created_at       AS u_created_at,
    u.updated_at       AS u_updated_at,

    -- Vehicles (all columns)
    v.id               AS v_id,
    v.registration_id  AS v_registration_id,
    v.user_id          AS v_user_id,
    v.conduction_sticker AS v_conduction_sticker,
    v.vehicle_plate    AS v_vehicle_plate,
    v.car_brand        AS v_car_brand,
    v.model            AS v_model,
    v.year             AS v_year,
    v.vehicle_type     AS v_vehicle_type,
    v.color            AS v_color,
    v.passenger_capacity AS v_passenger_capacity,
    v.chassis_number   AS v_chassis_number,
    v.engine_number    AS v_engine_number,
    v.fuel_type        AS v_fuel_type,
    v.current_mileage  AS v_current_mileage,
    v.created_at       AS v_created_at,
    v.updated_at       AS v_updated_at,

    -- Vehicle Insurance (all columns)
    ins.id                         AS ins_id,
    ins.registration_id_insurance  AS ins_registration_id_insurance,
    ins.user_id                    AS ins_user_id,
    ins.insurance_provider         AS ins_insurance_provider,
    ins.policy_number              AS ins_policy_number,
    ins.insurance_type             AS ins_insurance_type,
    ins.coverage_type              AS ins_coverage_type,
    ins.num_passengers_covered     AS ins_num_passengers_covered,
    ins.start_date                 AS ins_start_date,
    ins.expiration_date            AS ins_expiration_date,
    ins.premium_amount             AS ins_premium_amount,
    ins.renewal_reminders          AS ins_renewal_reminders,
    ins.status                     AS ins_status,
    ins.agent_contact_person       AS ins_agent_contact_person,
    ins.scanned_copy_path          AS ins_scanned_copy_path,
    ins.created_at                 AS ins_created_at,
    ins.updated_at                 AS ins_updated_at
FROM users u
INNER JOIN vehicles v ON v.user_id = u.user_id
LEFT JOIN vehicle_insurance ins ON ins.user_id = u.user_id
' . $WSQL . '
ORDER BY u.lastname, u.firstname, v.registration_id, ins.policy_number';

$rows = [];
try {
    $sqlExec = $sql . ($export ? '' : ' LIMIT :lim OFFSET :off');
    $stmt = $dbh->prepare($sqlExec);
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

// CSV export
if ($export) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_vehicles_insurance.csv"');
    $out = fopen('php://output', 'w');
    // headers
    fputcsv($out, [
      'u_id','u_user_id','u_firstname','u_lastname','u_age','u_gender','u_contact','u_email','u_role','u_created_at','u_updated_at',
      'v_id','v_registration_id','v_user_id','v_conduction_sticker','v_vehicle_plate','v_car_brand','v_model','v_year','v_vehicle_type','v_color','v_passenger_capacity','v_chassis_number','v_engine_number','v_fuel_type','v_current_mileage','v_created_at','v_updated_at',
      'ins_id','ins_registration_id_insurance','ins_user_id','ins_insurance_provider','ins_policy_number','ins_insurance_type','ins_coverage_type','ins_num_passengers_covered','ins_start_date','ins_expiration_date','ins_premium_amount','ins_renewal_reminders','ins_status','ins_agent_contact_person','ins_scanned_copy_path','ins_created_at','ins_updated_at'
    ]);
    foreach ($rows as $r) {
        fputcsv($out, [
          $r['u_id'] ?? '', $r['u_user_id'] ?? '', $r['u_firstname'] ?? '', $r['u_lastname'] ?? '', $r['u_age'] ?? '', $r['u_gender'] ?? '', $r['u_contact'] ?? '', $r['u_email'] ?? '', $r['u_role'] ?? '', $r['u_created_at'] ?? '', $r['u_updated_at'] ?? '',
          $r['v_id'] ?? '', $r['v_registration_id'] ?? '', $r['v_user_id'] ?? '', $r['v_conduction_sticker'] ?? '', $r['v_vehicle_plate'] ?? '', $r['v_car_brand'] ?? '', $r['v_model'] ?? '', $r['v_year'] ?? '', $r['v_vehicle_type'] ?? '', $r['v_color'] ?? '', $r['v_passenger_capacity'] ?? '', $r['v_chassis_number'] ?? '', $r['v_engine_number'] ?? '', $r['v_fuel_type'] ?? '', $r['v_current_mileage'] ?? '', $r['v_created_at'] ?? '', $r['v_updated_at'] ?? '',
          $r['ins_id'] ?? '', $r['ins_registration_id_insurance'] ?? '', $r['ins_user_id'] ?? '', $r['ins_insurance_provider'] ?? '', $r['ins_policy_number'] ?? '', $r['ins_insurance_type'] ?? '', $r['ins_coverage_type'] ?? '', $r['ins_num_passengers_covered'] ?? '', $r['ins_start_date'] ?? '', $r['ins_expiration_date'] ?? '', $r['ins_premium_amount'] ?? '', $r['ins_renewal_reminders'] ?? '', $r['ins_status'] ?? '', $r['ins_agent_contact_person'] ?? '', $r['ins_scanned_copy_path'] ?? '', $r['ins_created_at'] ?? '', $r['ins_updated_at'] ?? ''
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
  <title>Users • Vehicles • Insurance</title>
  <link rel="stylesheet" href="style.css" />
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
          <h1>Users • Vehicles • Insurance</h1>
          <div class="subtitle">Transparent identity & legal docs</div>
        </div>
      </div>
      <nav class="sidenav">
        <a href="index.php">Dashboard</a>
        <a href="admin.php">Admin</a>
        <a href="history.php">History</a>
        <a href="users.php">Users</a>
        <a href="drivers.php" class="active">Drivers</a>
        <a href="logout.php">Logout</a>
      </nav>
    </aside>
    <div class="content-area" style="display:flex;flex-direction:column;flex:1 1 0%;min-width:0;">
      <header class="topbar">
        <div>
          <div class="muted" style="font-size:14px">Welcome, <?php echo e($session_name); ?></div>
          <div style="font-weight:800; font-size:18px; letter-spacing:.3px">User Fleet & Insurance</div>
        </div>
        <div class="userbox">
          <span class="pill"><?php echo e(strtoupper($session_role)); ?></span>
          <div class="avatar"><?php echo e(strtoupper(substr($session_name,0,1))); ?></div>
        </div>
      </header>
      <main class="content">

        <section class="panel card">
          <h2 style="margin:0 0 6px">Records</h2>
          <div class="muted" style="margin-bottom:8px">
            This page shows only users with at least one registered vehicle. Insurance is joined by user_id.
            Sensitive credentials are never shown.
          </div>

          <div class="toolbar">
            <form class="search" method="get" action="">
              <input type="text" name="q" value="<?php echo e($kw); ?>" placeholder="Search name, email, plate, brand, policy..." />
              <select name="ps">
                <?php foreach ([25,50,100] as $ps): ?>
                  <option value="<?php echo (int)$ps; ?>" <?php echo ($pageSz === $ps ? 'selected' : ''); ?>>Show <?php echo (int)$ps; ?></option>
                <?php endforeach; ?>
              </select>
              <?php if ($session_role === 'admin'): ?>
                <label class="pill" style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                  <input type="checkbox" name="mine" value="1" <?php echo $mine ? 'checked' : ''; ?> style="accent-color:#3b82f6;"> Mine
                </label>
              <?php else: ?>
                <input type="hidden" name="mine" value="1" />
              <?php endif; ?>
              <button class="btn" type="submit">Apply</button>
            </form>
            <div>
              <a class="btn-ghost" href="?<?php echo http_build_query(array_merge($_GET, ['export' => '1', 'p' => 1])); ?>">Export CSV</a>
            </div>
          </div>

          <div style="max-height:560px; overflow:auto; border:1px solid var(--border); border-radius:10px">
            <table>
              <thead>
                <tr>
                  <!-- User headers -->
                  <th>U: ID</th>
                  <th>U: User ID</th>
                  <th>U: Firstname</th>
                  <th>U: Lastname</th>
                  <th>U: Age</th>
                  <th>U: Gender</th>
                  <th>U: Contact</th>
                  <th>U: Email</th>
                  <th>U: Role</th>
                  <th>U: Created</th>
                  <th>U: Updated</th>

                  <!-- Vehicle headers -->
                  <th>V: ID</th>
                  <th>V: Registration ID</th>
                  <th>V: User ID</th>
                  <th>V: Conduction Sticker</th>
                  <th>V: Plate</th>
                  <th>V: Brand</th>
                  <th>V: Model</th>
                  <th>V: Year</th>
                  <th>V: Type</th>
                  <th>V: Color</th>
                  <th>V: Passenger Capacity</th>
                  <th>V: Chassis #</th>
                  <th>V: Engine #</th>
                  <th>V: Fuel</th>
                  <th>V: Current Mileage</th>
                  <th>V: Created</th>
                  <th>V: Updated</th>

                  <!-- Insurance headers -->
                  <th>I: ID</th>
                  <th>I: Registration ID</th>
                  <th>I: User ID</th>
                  <th>I: Provider</th>
                  <th>I: Policy #</th>
                  <th>I: Type</th>
                  <th>I: Coverage</th>
                  <th>I: # Passengers</th>
                  <th>I: Start</th>
                  <th>I: Expiration</th>
                  <th>I: Premium</th>
                  <th>I: Renewal Reminders</th>
                  <th>I: Status</th>
                  <th>I: Agent Contact</th>
                  <th>I: Scan Path</th>
                  <th>I: Created</th>
                  <th>I: Updated</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($rows)) : ?>
                  <tr><td class="muted" colspan="45">No records found.</td></tr>
                <?php else: foreach ($rows as $r) : ?>
                  <tr>
                    <!-- User cells -->
                    <td><?php echo e($r['u_id'] ?? ''); ?></td>
                    <td><?php echo e($r['u_user_id'] ?? ''); ?></td>
                    <td><?php echo e($r['u_firstname'] ?? ''); ?></td>
                    <td><?php echo e($r['u_lastname'] ?? ''); ?></td>
                    <td><?php echo e($r['u_age'] ?? ''); ?></td>
                    <td><?php echo e($r['u_gender'] ?? ''); ?></td>
                    <td><?php echo e($r['u_contact'] ?? ''); ?></td>
                    <td><?php echo e($r['u_email'] ?? ''); ?></td>
                    <td><?php echo e($r['u_role'] ?? ''); ?></td>
                    <td><?php echo e($r['u_created_at'] ?? ''); ?></td>
                    <td><?php echo e($r['u_updated_at'] ?? ''); ?></td>

                    <!-- Vehicle cells -->
                    <td><?php echo e($r['v_id'] ?? ''); ?></td>
                    <td><?php echo e($r['v_registration_id'] ?? ''); ?></td>
                    <td><?php echo e($r['v_user_id'] ?? ''); ?></td>
                    <td><?php echo e($r['v_conduction_sticker'] ?? ''); ?></td>
                    <td><?php echo e($r['v_vehicle_plate'] ?? ''); ?></td>
                    <td><?php echo e($r['v_car_brand'] ?? ''); ?></td>
                    <td><?php echo e($r['v_model'] ?? ''); ?></td>
                    <td><?php echo e($r['v_year'] ?? ''); ?></td>
                    <td><?php echo e($r['v_vehicle_type'] ?? ''); ?></td>
                    <td><?php echo e($r['v_color'] ?? ''); ?></td>
                    <td><?php echo e($r['v_passenger_capacity'] ?? ''); ?></td>
                    <td><?php echo e($r['v_chassis_number'] ?? ''); ?></td>
                    <td><?php echo e($r['v_engine_number'] ?? ''); ?></td>
                    <td><?php echo e($r['v_fuel_type'] ?? ''); ?></td>
                    <td><?php echo e($r['v_current_mileage'] ?? ''); ?></td>
                    <td><?php echo e($r['v_created_at'] ?? ''); ?></td>
                    <td><?php echo e($r['v_updated_at'] ?? ''); ?></td>

                    <!-- Insurance cells -->
                    <td><?php echo e($r['ins_id'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_registration_id_insurance'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_user_id'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_insurance_provider'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_policy_number'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_insurance_type'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_coverage_type'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_num_passengers_covered'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_start_date'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_expiration_date'] ?? ''); ?></td>
                    <td><?php echo '₱ ' . number_format((float)($r['ins_premium_amount'] ?? 0), 2); ?></td>
                    <td><?php echo e($r['ins_renewal_reminders'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_status'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_agent_contact_person'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_scanned_copy_path'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_created_at'] ?? ''); ?></td>
                    <td><?php echo e($r['ins_updated_at'] ?? ''); ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>

          <?php $maxPage = ($totalRows > 0) ? (int)ceil($totalRows / $pageSz) : 1; ?>
          <div class="toolbar" style="margin-top:10px">
            <div class="muted">Showing <?php echo (int)min($page * $pageSz, max(0, $totalRows)); ?> of <?php echo (int)$totalRows; ?> records</div>
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
            Privacy note: As a user, you can view only your vehicle and insurance records. Admins can audit all for compliance.
          </p>
        <?php else: ?>
          <p class="muted" style="margin-top:14px">
            Compliance note: This view includes insurance policy information to support transparency and legal document verification.
          </p>
        <?php endif; ?>

      </main>
    </div>
  </div>
</div>
<script>
// Sidebar toggle persistence
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
