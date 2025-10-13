<?php
session_name('admin_session');
session_start();
include('../../../database/connect.php');

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Verify admin in the admin table
$stmt = $conn->prepare("SELECT id, firstname, lastname FROM admin WHERE firebase_uid = ?");
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('HTTP/1.1 403 Forbidden');
    echo "You do not have permission to access this page.";
    exit();
}

$admin = $result->fetch_assoc();
$user_name = trim($admin['firstname'] . ' ' . $admin['lastname']);
if ($user_name === '') $user_name = 'Admin';

$_SESSION['role'] = 'admin';

// HTML escape helper
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Get DB name
try {
    $dbName = $dbh->query('SELECT DATABASE()')->fetchColumn();
} catch (Throwable $e) {
    $dbName = '';
}

// Safe counter
function safe_count(PDO $dbh, $table) {
    try { return (int)$dbh->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn(); } catch (Throwable $e) { return 0; }
}

$cntUsers = safe_count($dbh, 'users');
$cntVehicles = safe_count($dbh, 'vehicles');
$cntDocs = safe_count($dbh, 'documents');
$cntIns = safe_count($dbh, 'vehicle_insurance');
$cntRes = safe_count($dbh, 'vehicle_reservations');

// Reservation status counts
$stCounts = ['Pending'=>0,'Approved'=>0,'Dispatched'=>0,'Completed'=>0,'Cancelled'=>0];
try {
    foreach ($dbh->query('SELECT status, COUNT(*) c FROM vehicle_reservations GROUP BY status')->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $k = $r['status'] ?? '';
        if (isset($stCounts[$k])) $stCounts[$k] = (int)$r['c'];
    }
} catch (Throwable $e) {}

// ✅ Analytics: Completed Reservations from History
$cntCompletedHistory = 0;
try {
    $stmt = $dbh->query("SELECT COUNT(*) FROM vehicle_reservations_history WHERE status = 'Completed'");
    $cntCompletedHistory = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
    $cntCompletedHistory = 0;
}

// ✅ Compute completion rate
$totalTrips = $cntRes + $cntCompletedHistory;
$completionRate = $totalTrips > 0 ? round(($cntCompletedHistory / $totalTrips) * 100, 1) : 0;

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Panel</title>
  <script src="./config/firebase-config.php"></script>
  <script type="module" src="./config/firebase.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
          <h1>Admin Panel</h1>
          <div class="subtitle"><?php echo e($dbName ?: 'Database'); ?></div>
        </div>
      </div>
      <nav class="sidenav">
        <a href="index.php">Dashboard</a>
        <a href="../../dispatchsystem/index.php">Reservations</a>
        <a href="components/history.php">Travel Records</a>
        <a href="components/users.php">Users</a>
        <a href="components/drivers.php">Drivers</a>
        <hr style="border-color:var(--border)">
        <a href="../auth/logout.php">Logout</a>
      </nav>
    </aside>

    <div class="content-area" style="display:flex;flex-direction:column;flex:1 1 0%;min-width:0;">
      <header class="topbar">
        <div>
          <div class="muted" style="font-size:14px">Welcome, <?php echo e($user_name); ?></div>
          <div style="font-weight:800; font-size:18px; letter-spacing:.3px">System Overview</div>
        </div>
        <div class="userbox">
          <span class="pill"><?php echo strtoupper($_SESSION['role'] ?? 'USER'); ?></span>
          <div class="avatar"><?php echo strtoupper(substr($user_name ?? 'User', 0, 1)); ?></div>
        </div>
      </header>

      <main class="content">
        <section class="grid">
          <div class="card"><div class="stat"><div><div class="k"><?php echo (int)$cntUsers; ?></div><div class="label">Users</div></div><a class="pill" href="?t=users">View</a></div></div>
          <div class="card"><div class="stat"><div><div class="k"><?php echo (int)$cntVehicles; ?></div><div class="label">Vehicles</div></div><a class="pill" href="?t=vehicles">View</a></div></div>
          <div class="card"><div class="stat"><div><div class="k"><?php echo (int)$cntDocs; ?></div><div class="label">Documents</div></div><a class="pill" href="?t=documents">View</a></div></div>
          <div class="card"><div class="stat"><div><div class="k"><?php echo (int)$cntIns; ?></div><div class="label">Insurance</div></div><a class="pill" href="?t=vehicle_insurance">View</a></div></div>
          <div class="card"><div class="stat"><div><div class="k"><?php echo (int)array_sum($stCounts); ?></div><div class="label">Reservations</div></div><a class="pill" href="?t=vehicle_reservations">View</a></div></div>
            <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <!-- Completed Trips Analytics -->
              <div class="card small-card">
                <div class="stat">
                  <div>
                    <div class="k small"><?php echo (int)$cntCompletedHistory; ?></div>
                    <div class="label small-label">Completed Trips</div>
                  </div>
                  <a class="pill small-pill" href="components/history.php">View</a>
                </div>
              </div>

              <!-- Completion Rate -->
              <div class="card small-card">
                <div class="stat">
                  <div>
                    <div class="k small"><?php echo $completionRate; ?>%</div>
                    <div class="label small-label">Completion Rate</div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Optional: Analytics Donut Chart -->
            <section style="margin-top:2rem;">
              <canvas id="completionChart" width="250" height="250"></canvas>
            </section>

<script>
// Sidebar toggle
document.addEventListener('DOMContentLoaded', function() {
  var sidebar = document.getElementById('sidebar');
  var toggle = document.getElementById('sidebarToggle');
  var icon = document.getElementById('sidebarToggleIcon');
  if (localStorage.getItem('sidebar-collapsed') === '1') {
    sidebar.classList.add('collapsed');
    icon.style.transform = 'rotate(180deg)';
  }
  if (toggle && sidebar) {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      sidebar.classList.toggle('collapsed');
      var isCollapsed = sidebar.classList.contains('collapsed');
      icon.style.transform = isCollapsed ? 'rotate(180deg)' : '';
      localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
    });
  }
});

// ✅ Donut chart for Completed vs Others
const ctx = document.getElementById('completionChart');
if (ctx) {
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Completed', 'Others'],
      datasets: [{
        data: [<?php echo $cntCompletedHistory; ?>, <?php echo max($totalTrips - $cntCompletedHistory, 0); ?>],
        backgroundColor: ['#4CAF50', '#E0E0E0']
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        title: { display: true, text: 'Trip Completion Analytics' }
      }
    }
  });
}
</script>

</body>
</html>
