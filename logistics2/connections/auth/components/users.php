<?php
// Users listing page (role = 'user')
// - Starts session and redirects to login if not authenticated
// - Displays all users whose role is exactly 'user'
// - Search, pagination, CSV export
// - Excludes password from display for security

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

session_start();
require_once('../../../../database/connect.php');

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$session_role = (string)($_SESSION['role'] ?? 'user');
$session_name = trim(((string)($_SESSION['firstname'] ?? '') . ' ' . (string)($_SESSION['lastname'] ?? '')));
if ($session_name === '') { $session_name = 'User'; }

function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Filters & pagination
$kw      = trim((string)($_GET['q'] ?? ''));
$page    = max(1, (int)($_GET['p'] ?? 1));
$pageSz  = max(1, min(100, (int)($_GET['ps'] ?? 25)));
$offset  = ($page - 1) * $pageSz;
$export  = isset($_GET['export']) && $_GET['export'] === '1';

// FIX: use bound parameter for role filter to avoid SQL mode issues (ANSI_QUOTES)
$where   = ['u.role = :role'];
$params  = [':role' => 'user'];

if ($kw !== '') {
    $where[] = '(
        u.firstname LIKE :kw OR
        u.lastname LIKE :kw OR
        u.email LIKE :kw OR
        u.contact LIKE :kw OR
        u.user_id LIKE :kw OR
        u.gender LIKE :kw
    )';
    $params[':kw'] = '%'.$kw.'%';
}
$WSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Count rows
$totalRows = 0;
try {
    $stmt = $dbh->prepare('SELECT COUNT(*) FROM users u ' . $WSQL);
    $stmt->execute($params);
    $totalRows = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
    $totalRows = 0;
}

// Fetch rows (exclude password from selection)
$rows = [];
try {
    $sql = 'SELECT u.id, u.user_id, u.firstname, u.lastname, u.age, u.gender, u.contact, u.email, u.role, u.created_at, u.updated_at
            FROM users u ' . $WSQL . '
            ORDER BY u.lastname, u.firstname, u.user_id
            ' . ($export ? '' : 'LIMIT :lim OFFSET :off');
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

// CSV export
if ($export) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_role_user.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','user_id','firstname','lastname','age','gender','contact','email','role','created_at','updated_at']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'] ?? '', $r['user_id'] ?? '', $r['firstname'] ?? '', $r['lastname'] ?? '', $r['age'] ?? '', $r['gender'] ?? '', $r['contact'] ?? '', $r['email'] ?? '', $r['role'] ?? '', $r['created_at'] ?? '', $r['updated_at'] ?? ''
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
  <title>Users</title>
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
          <h1>Users</h1>
          <div class="subtitle">Showing only users with role = user</div>
        </div>
      </div>
      <nav class="sidenav">
        <a href="index.php">Dashboard</a>
        <a href="../../dispatchsystem/index.php">Reservations</a>
        <a href="history.php">Travel Records</a>
        <a href="users.php" class="active">Users</a>
        <a href="drivers.php">Drivers</a>
        <hr style="border-color:var(--border)">
        <a href="logout.php">Logout</a>
      </nav>
    </aside>
    <div class="content-area" style="display:flex;flex-direction:column;flex:1 1 0%;min-width:0;">
      <header class="topbar">
        <div>
          <div class="muted" style="font-size:14px">Welcome, <?php echo e($session_name); ?></div>
          <div style="font-weight:800; font-size:18px; letter-spacing:.3px">Users</div>
        </div>
        <div class="userbox">
          <span class="pill"><?php echo e(strtoupper($session_role)); ?></span>
          <div class="avatar"><?php echo e(strtoupper(substr($session_name,0,1))); ?></div>
        </div>
      </header>
      <main class="content">

        <section class="panel card">
          <h2 style="margin:0 0 6px">Users</h2>
          <div class="toolbar">
            <form class="search" method="get">
              <input type="text" name="q" value="<?php echo e($kw); ?>" placeholder="Search name, email, contact, user id..." />
              <select name="ps">
                <?php foreach ([25,50,100] as $ps): ?>
                  <option value="<?php echo (int)$ps; ?>" <?php echo ($pageSz === $ps ? 'selected' : ''); ?>>Show <?php echo (int)$ps; ?></option>
                <?php endforeach; ?>
              </select>
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
                  <th>ID</th>
                  <th>User ID</th>
                  <th>Firstname</th>
                  <th>Lastname</th>
                  <th>Age</th>
                  <th>Gender</th>
                  <th>Contact</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Created At</th>
                  <th>Updated At</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($rows)): ?>
                  <tr><td class="muted" colspan="11">No users found.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                  <tr>
                    <td><?php echo e($r['id'] ?? ''); ?></td>
                    <td><?php echo e($r['user_id'] ?? ''); ?></td>
                    <td><?php echo e($r['firstname'] ?? ''); ?></td>
                    <td><?php echo e($r['lastname'] ?? ''); ?></td>
                    <td><?php echo e($r['age'] ?? ''); ?></td>
                    <td><?php echo e($r['gender'] ?? ''); ?></td>
                    <td><?php echo e($r['contact'] ?? ''); ?></td>
                    <td><?php echo e($r['email'] ?? ''); ?></td>
                    <td><?php echo e($r['role'] ?? ''); ?></td>
                    <td><?php echo e($r['created_at'] ?? ''); ?></td>
                    <td><?php echo e($r['updated_at'] ?? ''); ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>

          <?php $maxPage = ($totalRows > 0) ? (int)ceil($totalRows / $pageSz) : 1; ?>
          <div class="toolbar" style="margin-top:10px">
            <div class="muted">Showing <?php echo (int)min($page * $pageSz, max(0, $totalRows)); ?> of <?php echo (int)$totalRows; ?> users</div>
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

        <p class="muted" style="margin-top:14px">
          For privacy and security, passwords are never displayed or exported.
        </p>

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
