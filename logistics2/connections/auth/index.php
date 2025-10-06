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

// Optional: set session role explicitly
$_SESSION['role'] = 'admin';

// HTML escape helper
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Get DB name
try {
    $dbName = $dbh->query('SELECT DATABASE()')->fetchColumn();
} catch (Throwable $e) {
    $dbName = '';
}

// Quick entity counts
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
    <!-- Display the role in uppercase -->
    <span class="pill"><?php echo strtoupper($_SESSION['role'] ?? 'USER'); ?></span>

    <!-- Display first letter of the name in uppercase -->
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
    </section>
      
  </main>
</div>
</body>
<script>
// Modern dropdown: click, hover, keyboard accessible
document.addEventListener('DOMContentLoaded', function() {
  var btn = document.getElementById('dropdownBtn');
  var dropdown = btn && btn.closest('.modern-dropdown');
  var content = document.getElementById('dropdownContent');
  if (btn && dropdown && content) {
    // Toggle on click
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var isOpen = dropdown.classList.toggle('open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      if (isOpen) {
        content.querySelector('a')?.focus();
      }
    });
    // Keyboard navigation
    dropdown.addEventListener('keydown', function(e) {
      if (!dropdown.classList.contains('open')) return;
      var links = Array.from(content.querySelectorAll('a'));
      var idx = links.indexOf(document.activeElement);
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        links[(idx+1)%links.length]?.focus();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        links[(idx-1+links.length)%links.length]?.focus();
      } else if (e.key === 'Escape') {
        dropdown.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
        btn.focus();
      }
    });
    // Close on outside click
    document.addEventListener('mousedown', function(e) {
      if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
      }
    });
    // Open on hover
    dropdown.addEventListener('mouseenter', function() {
      dropdown.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
    });
    dropdown.addEventListener('mouseleave', function() {
      dropdown.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    });
  }
});
document.addEventListener('DOMContentLoaded', function() {
  var sidebar = document.getElementById('sidebar');
  var toggle = document.getElementById('sidebarToggle');
  var icon = document.getElementById('sidebarToggleIcon');
  // Restore state
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
</script>

</html>
