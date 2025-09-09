<?php
session_start();
include('../../../database/connect.php');

// Require login (optionally enforce admin role if available)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$session_role = $_SESSION['role'] ?? 'user';
$user_name = trim((($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? '')));
if ($user_name === '') $user_name = 'User';

// Helper: HTML escape
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Resolve current schema name
try {
    $dbName = $dbh->query('SELECT DATABASE()')->fetchColumn();
} catch (Throwable $e) {
    $dbName = '';
}

// Fetch all base tables in the current schema
$tables = [];
try {
    $tables = $dbh->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {}

// Ensure selected table is valid
$selected = isset($_GET['t']) ? (string)$_GET['t'] : '';
if (!$selected || !in_array($selected, $tables, true)) {
    $selected = $tables[0] ?? '';
}

// Pagination & search
$page = max(1, (int)($_GET['p'] ?? 1));
$pageSize = max(1, min(200, (int)($_GET['ps'] ?? 25)));
$offset = ($page - 1) * $pageSize;
$kw = trim((string)($_GET['q'] ?? ''));
$export = isset($_GET['export']) && $_GET['export'] === '1';

// Load column metadata for selected table
$columns = [];
$searchableCols = [];
if ($selected) {
    try {
        $columns = $dbh->query('DESCRIBE `'.$selected.'`')->fetchAll(PDO::FETCH_ASSOC);
        // Determine searchable text-like columns
        foreach ($columns as $col) {
            $type = strtolower((string)$col['Type']);
            if (strpos($type, 'char') !== false || strpos($type, 'text') !== false || strpos($type, 'enum') !== false) {
                $searchableCols[] = $col['Field'];
            }
        }
    } catch (Throwable $e) {}
}

// Build SQL for list & count
$totalRows = 0;
$rows = [];
if ($selected) {
    try {
        $where = '';
        $params = [];
        if ($kw !== '' && !empty($searchableCols)) {
            $likes = [];
            foreach ($searchableCols as $c) { $likes[] = '`'.$c.'` LIKE :kw'; }
            $where = ' WHERE ' . implode(' OR ', $likes);
            $params[':kw'] = '%'.$kw.'%';
        }
        // Total count
        $stmt = $dbh->prepare('SELECT COUNT(*) FROM `'.$selected.'`' . $where);
        $stmt->execute($params);
        $totalRows = (int)$stmt->fetchColumn();

        // Data
        $sql = 'SELECT * FROM `'.$selected.'`' . $where . ' LIMIT :lim OFFSET :off';
        $stmt = $dbh->prepare($sql);
        foreach ($params as $k=>$v) { $stmt->bindValue($k, $v, PDO::PARAM_STR); }
        $stmt->bindValue(':lim', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // CSV export
        if ($export) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="'.$selected.'_export.csv"');
            $out = fopen('php://output', 'w');
            // header row
            if (!empty($columns)) {
                fputcsv($out, array_map(function($c) { return $c['Field']; }, $columns));
            } elseif (!empty($rows)) {
                fputcsv($out, array_keys($rows[0]));
            } else {
                fputcsv($out, []);
            }
            foreach ($rows as $r) { fputcsv($out, array_values($r)); }
            fclose($out);
            exit();
        }
    } catch (Throwable $e) {
        // Ignore for UI, show error panel
    }
}

// Quick entity counts for summary
function safe_count(PDO $dbh, $table) {
    try { return (int)$dbh->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn(); } catch (Throwable $e) { return 0; }
}
$cntUsers = safe_count($dbh, 'users');
$cntVehicles = safe_count($dbh, 'vehicles');
$cntDocs = safe_count($dbh, 'documents');
$cntIns = safe_count($dbh, 'vehicle_insurance');
$cntRes = safe_count($dbh, 'vehicle_reservations');

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
  <aside class="sidebar">
    <div class="brand">
      <div class="logo">AD</div>
      <div>
        <h1>Admin Panel</h1>
        <div class="subtitle"><?php echo e($dbName ?: 'Database'); ?></div>
      </div>
    </div>
    <nav class="sidenav">
      <a href="../../dispatchsystem/index.php">Reservations</a>
      <a href="../auth/logout.php">Logout</a>
      <hr style="border-color:var(--border)">
      <?php foreach ($tables as $t): $active = ($t === $selected) ? 'active' : ''; ?>
        <a class="<?php echo $active; ?>" href="?t=<?php echo e($t); ?>"><?php echo e($t); ?></a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <header class="topbar">
    <div>
      <div class="muted" style="font-size:14px">Welcome, <?php echo e($user_name); ?></div>
      <div style="font-weight:800; font-size:18px; letter-spacing:.3px">System Overview</div>
    </div>
    <div class="userbox">
      <span class="pill"><?php echo e(strtoupper($session_role)); ?></span>
      <div class="avatar"><?php echo e(strtoupper(substr($user_name,0,1))); ?></div>
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
       <form id="signUpForm">
        <div id="signUpMessage"></div>
        <div class="form-group">
            <label for="signUpEmail">Email:</label>
            <input type="email" id="signUpEmail" name="email" required>
        </div>
        <div class="form-group">
            <label for="signUpPassword">Password:</label>
            <input type="password" id="signUpPassword" name="password" required>
        </div>
        <div class="form-group">
            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" name="firstName" required>
        </div>
        <div class="form-group">
            <label for="lastName">Last Name:</label>
            <input type="text" id="lastName" name="lastName" required>
        </div>
        <button type="submit">Sign Up</button>
    </form>
    <?php if ($selected): ?>
    <section class="panel card">
      <h2 style="margin:0 0 6px">Table: <?php echo e($selected); ?></h2>
      <div class="toolbar">
        <form class="search" method="get">
          <input type="hidden" name="t" value="<?php echo e($selected); ?>" />
          <input type="text" name="q" value="<?php echo e($kw); ?>" placeholder="Search text columns..." />
          <select name="ps">
            <?php foreach ([25,50,100,200] as $ps): ?>
              <option value="<?php echo (int)$ps; ?>" <?php echo ($pageSize===$ps?'selected':''); ?>>Show <?php echo (int)$ps; ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn" type="submit">Apply</button>
        </form>
        <div>
          <a class="btn-ghost" href="?t=<?php echo e($selected); ?>&q=<?php echo urlencode($kw); ?>&ps=<?php echo (int)$pageSize; ?>&export=1">Export CSV</a>
        </div>
      </div>

      <div style="max-height:560px; overflow:auto; border:1px solid var(--border); border-radius:10px">
        <table>
          <thead>
            <tr>
              <?php foreach ($columns as $c): ?>
                <th><?php echo e($c['Field']); ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr><td class="muted" colspan="<?php echo max(1, count($columns)); ?>">No rows found.</td></tr>
            <?php else: foreach ($rows as $r): ?>
              <tr>
                <?php foreach ($columns as $c): $f=$c['Field']; ?>
                  <td><?php echo e(is_scalar($r[$f] ?? '') ? (string)$r[$f] : json_encode($r[$f])); ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <?php $maxPage = ($totalRows>0) ? (int)ceil($totalRows/$pageSize) : 1; ?>
      <div class="toolbar" style="margin-top:10px">
        <div class="muted">Showing <?php echo (int)min($page*$pageSize, max(0,$totalRows)); ?> of <?php echo (int)$totalRows; ?> rows</div>
        <div>
          <?php if ($page>1): ?>
            <a class="btn-ghost" href="?t=<?php echo e($selected); ?>&q=<?php echo urlencode($kw); ?>&ps=<?php echo (int)$pageSize; ?>&p=<?php echo (int)($page-1); ?>">Prev</a>
          <?php endif; ?>
          <?php if ($page<$maxPage): ?>
            <a class="btn-ghost" href="?t=<?php echo e($selected); ?>&q=<?php echo urlencode($kw); ?>&ps=<?php echo (int)$pageSize; ?>&p=<?php echo (int)($page+1); ?>">Next</a>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($session_role !== 'admin'): ?>
      <p class="muted" style="margin-top:14px">Tip: Elevate your account to admin to enable full management actions.</p>
    <?php endif; ?>
  </main>
</div>
<script type="module">
  import { initializeApp, getApps, getApp } from 'https://www.gstatic.com/firebasejs/10.12.1/firebase-app.js';
  import { getAuth, createUserWithEmailAndPassword, signOut } from 'https://www.gstatic.com/firebasejs/10.12.1/firebase-auth.js';

  const signUpForm = document.getElementById('signUpForm');
  const signUpMsg = document.getElementById('signUpMessage');

  const cfg = (typeof window !== 'undefined') ? (window.FIREBASE_CONFIG || null) : null;
  let secondary = null;
  if (cfg) {
    try {
      secondary = getApps().some(a => a.name === 'secondary') ? getApp('secondary') : initializeApp(cfg, 'secondary');
    } catch (e) {
      console.error('Failed to init secondary app', e);
      secondary = null;
    }
  }

  if (signUpForm) {
    signUpForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (signUpMsg) { signUpMsg.textContent=''; signUpMsg.style.color=''; }
      const email = document.getElementById('signUpEmail').value.trim();
      const password = document.getElementById('signUpPassword').value;
      const firstName = (document.getElementById('firstName')?.value || '').trim();
      const lastName = (document.getElementById('lastName')?.value || '').trim();
      if (!secondary) { if (signUpMsg) { signUpMsg.textContent='Config error. Cannot init secondary app.'; signUpMsg.style.color='red'; } return; }
      try {
        const auth2 = getAuth(secondary);
        const cred = await createUserWithEmailAndPassword(auth2, email, password);
        const uid = cred.user?.uid || '';
        try { await signOut(auth2); } catch {}
        const resp = await fetch('./admin_login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'upsert_admin', uid, email, firstName, lastName })
        });
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok || !data.ok) {
          throw new Error(data.error || 'Failed to save admin record');
        }
        if (signUpMsg) { signUpMsg.textContent = 'Admin account created successfully.'; signUpMsg.style.color = 'green'; }
        signUpForm.reset();
      } catch (err) {
        if (signUpMsg) {
          signUpMsg.textContent = (err && err.message) ? err.message : 'Failed to sign up.';
          signUpMsg.style.color = 'red';
        }
      }
    });
  }
</script>
</body>
</html>
