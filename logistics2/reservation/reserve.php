<?php
session_start();
include('../../database/connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = (string)$_SESSION['user_id'];

// Fetch current user's name
$stmt = $dbh->prepare('SELECT firstname, lastname FROM users WHERE user_id = :uid LIMIT 1');
$stmt->execute([':uid' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$requester_name = $user ? $user['firstname'] . ' ' . $user['lastname'] : '';

// Helpers
function p($key, $default = '') { return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default; }
function q($key, $default = '') { return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default; }
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function to_mysql_dt($val) {
    if ($val === '' || $val === null) return null;
    $val = str_replace('T', ' ', $val);
    $ts = strtotime($val);
    return $ts ? date('Y-m-d H:i:s', $ts) : null;
}

// Flash message helpers
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
function flash($type, $msg) { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

//fetches the other vehicles but not the current user logged in vehicle
$vehicles = [];
$showing_all = false;
$stmt = $dbh->prepare("
    SELECT registration_id,
           vehicle_plate,
           car_brand,
           model,
           vehicle_type,
           COALESCE(NULLIF(passenger_capacity,''), NULL) AS passenger_capacity
    FROM vehicles
    WHERE user_id != :uid
    ORDER BY vehicle_plate ASC
");
$stmt->execute([':uid' => $user_id]);
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Common: fetch reservation by id and user
function get_reservation(PDO $dbh, $user_id, $id) {
    $stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE id = :id AND user_id = :uid');
    $stmt->execute([':id' => (int)$id, ':uid' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

// Fetch current user's reservations
$stmt = $dbh->prepare('SELECT id, reservation_ref, status, trip_date, pickup_datetime, dropoff_datetime FROM vehicle_reservations WHERE user_id = :uid ORDER BY pickup_datetime DESC LIMIT 10');
$stmt->execute([':uid' => $user_id]);
$user_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for active reservation
$stmt = $dbh->prepare('SELECT * FROM vehicle_reservations WHERE user_id = :uid AND status NOT IN ("Completed", "Cancelled") ORDER BY pickup_datetime DESC LIMIT 1');
$stmt->execute([':uid' => $user_id]);
$active_reservation = $stmt->fetch(PDO::FETCH_ASSOC);

// Logged-in check for header
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservation - Logistics 2</title>
  <link href="../../public/css/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="./style.css">
</head>
<body class="bg-neutral-50 text-gray-900">

  <!-- Header -->
  <header class="bg-black text-white">
    <div class="container mx-auto flex justify-between items-center px-6 py-5">
      <h1 class="text-3xl font-extrabold tracking-wide">LOGISTICS 2</h1>
      <nav class="flex gap-8 text-sm uppercase">
        <a href="#work" class="hover:text-gray-300">Work</a>
        <a href="#about" class="hover:text-gray-300">About</a>
        <a href="#contact" class="hover:text-gray-300">Contact</a>
      </nav>
      <?php if ($is_logged_in): ?>
        <a href="../logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded text-sm font-medium">Logout</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Hero -->
  <section class="bg-gradient-to-b from-gray-100 to-gray-200 py-20">
    <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-2 items-center gap-12">
      <div class="text-center md:text-left">
        <h2 class="text-5xl md:text-6xl font-extrabold mb-6">Drive</h2>
        <p class="max-w-xl text-lg text-gray-600 mb-6">
          A modern project management system built to connect drivers and users seamlessly.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
          <a href="fleetvehiclemanagement/index.php" class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded text-white font-medium">Become a Driver</a>
          <a href="reservation/reserve.php" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded text-white font-medium">Make a Reservation</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Reservation Section -->
  <section class="container mx-auto px-6 py-16 bg-white rounded shadow mt-8">
    <a href="../index.php" class="text-blue-600 hover:underline">&larr; Return</a>
    <h2 class="text-2xl font-bold mt-4 mb-4">Create Reservation</h2>

    <?php if ($active_reservation): ?>
      <div class="bg-yellow-100 text-yellow-800 p-4 rounded mb-6">
        <strong>Note:</strong> You have an active reservation (<b><?= e($active_reservation['reservation_ref']) ?></b>) with status <span class="status s-<?= e($active_reservation['status']) ?>"><?= e($active_reservation['status']) ?></span>.<br>
        Please complete or cancel your current reservation before creating a new one.
      </div>
    <?php elseif (empty($vehicles)): ?>
      <p class="text-gray-500">No vehicles found in the system.</p>
      <div class="mt-4"><a class="btn btn-ghost text-blue-600 hover:underline" href="../fleetvehiclemanagement/index.php">Open Fleet Vehicle Management</a></div>
    <?php else: ?>
      <form method="post" action="../connections/vehiclereservationdispatchsystemdb/create_reservation.php" class="space-y-4">
        <input type="hidden" name="action" value="create" />

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1">Vehicle</label>
            <select name="vehicle_registration_id" class="w-full border rounded p-2" required>
              <option value="">-- Select Vehicle --</option>
              <?php foreach ($vehicles as $v): ?>
                <option value="<?= e($v['registration_id']) ?>"><?= e($v['vehicle_plate']) ?> — <?= e($v['car_brand'] . ' ' . $v['model']) ?> (<?= e($v['vehicle_type']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block mb-1">Passengers</label>
            <input type="number" name="passengers_count" min="1" step="1" required class="w-full border rounded p-2" />
          </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1">Trip Date</label>
            <input type="date" name="trip_date" value="<?= e(date('Y-m-d')) ?>" class="w-full border rounded p-2" required />
          </div>
          <div>
            <label class="block mb-1">Pick-up Time</label>
            <input type="time" name="pickup_datetime" class="w-full border rounded p-2" required />
          </div>
          <div>
            <label class="block mb-1">Drop-off Time</label>
            <input type="time" name="dropoff_datetime" class="w-full border rounded p-2" required />
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1">Pick-up Location</label>
            <div class="flex gap-2">
              <input type="text" name="pickup_location" placeholder="e.g., Main Office" class="w-full border rounded p-2" required />
              <button type="button" id="openPickupMap" class="px-3 py-2 bg-gray-300 rounded">Pick on map</button>
            </div>
            <input type="hidden" name="pickup_lat" />
            <input type="hidden" name="pickup_lng" />
            <input type="hidden" name="pickup_address" />
            <input type="hidden" name="pickup_location_id" />
          </div>
          <div>
            <label class="block mb-1">Drop-off Location</label>
            <div class="flex gap-2">
              <input type="text" name="dropoff_location" placeholder="e.g., Client Site" class="w-full border rounded p-2" required />
              <button type="button" id="openDropoffMap" class="px-3 py-2 bg-gray-300 rounded">Pick on map</button>
            </div>
            <input type="hidden" name="dropoff_lat" />
            <input type="hidden" name="dropoff_lng" />
            <input type="hidden" name="dropoff_address" />
            <input type="hidden" name="dropoff_location_id" />
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1">Requester</label>
            <input type="text" name="requester_name" value="<?= e($requester_name) ?>" class="w-full border rounded p-2" required />
          </div>
          <div>
            <label class="block mb-1">Purpose</label>
            <input type="text" name="purpose" placeholder="Trip purpose" class="w-full border rounded p-2" />
          </div>
        </div>

        <div class="flex justify-end mt-4 gap-2">
          <button type="button" class="bg-gray-300 px-4 py-2 rounded" onclick="closeFareModal()">Cancel</button>
          <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded" onclick="showFareModal()">Create Reservation</button>
        </div>

        <!-- Hidden fare/distance inputs -->
        <input type="hidden" name="distance_km" id="distance_km" />
        <input type="hidden" name="estimated_time" id="estimated_time" />
        <input type="hidden" name="driver_earnings" id="driver_earnings" />
        <input type="hidden" name="passenger_fare" id="passenger_fare" />
        <input type="hidden" name="incentives" id="incentives" />
      </form>
    <?php endif; ?>
  </section>

  <!-- Reservation Progress -->
  <section class="container mx-auto px-6 py-8">
    <h2 class="text-xl font-bold mb-4">Reservation Progress</h2>
    <?php if (empty($user_reservations)): ?>
      <p class="text-gray-500">You have no reservations yet.</p>
    <?php else: ?>
      <table class="min-w-full bg-white rounded shadow">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2">Ref</th>
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Pick-up</th>
            <th class="px-4 py-2">Drop-off</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2">Progress</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($user_reservations as $res): ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= e($res['reservation_ref']) ?></td>
              <td class="px-4 py-2"><?= e(date('M d, Y', strtotime($res['trip_date']))) ?></td>
              <td class="px-4 py-2"><?= e(date('H:i', strtotime($res['pickup_datetime']))) ?></td>
              <td class="px-4 py-2"><?= e(date('H:i', strtotime($res['dropoff_datetime']))) ?></td>
              <td class="px-4 py-2"><span class="status s-<?= e($res['status']) ?>"><?= e($res['status']) ?></span></td>
              <td class="px-4 py-2">
                <div class="w-full bg-gray-200 rounded h-3">
                  <div class="bg-blue-600 h-3 rounded" style="width:<?= get_progress_percent($res['status']) ?>%"></div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <!-- Footer -->
  <footer id="contact" class="bg-black text-white py-8 mt-8">
    <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
      <p class="text-sm">© 2025 Project Management System. All Rights Reserved.</p>
      <div class="flex gap-6 text-sm">
        <a href="#" class="hover:text-gray-400">WhatsApp</a>
        <a href="#" class="hover:text-gray-400">Instagram</a>
      </div>
    </div>
  </footer>

<div id="mapModal" class="map-modal-backdrop" aria-hidden="true">
  <div class="map-modal">
    <div class="map-modal-header">
      <strong id="mapModalTitle">Select Location</strong>
      <button type="button" id="closeMapModal">✕</button>
    </div>
    <div class="map-modal-body">
      <form id="mapSearchForm" class="search-row" onsubmit="return false;">
        <input type="search" id="mapSearch" class="search-box" placeholder="Search PH address (e.g., dominguez st, malibay, pasay city, metro manila)" autocomplete="off" enterkeyhint="search" />
        <button type="submit" class="inline-btn" id="mapSearchBtn">Search</button>
      </form>
      <div id="map"></div>
      <div id="selected-info" class="address-preview"><strong>Selected:</strong> <span id="address-text">Drag marker, click map, or search.</span></div>
    </div>
    <div class="map-actions">
      <button type="button" id="useLocationBtn">Use this location</button>
    </div>
  </div>
</div>

<div id="fareModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
  <div class="modal-content" style="background:#fff;padding:32px 24px;border-radius:16px;max-width:400px;width:90%;position:relative;">
    <button onclick="closeFareModal()" style="position:absolute;top:12px;right:16px;font-size:2rem;background:none;border:none;">&times;</button>
    <h3>Transport Cost Analysis</h3>
    <div id="fareModalBody"></div>
    <div style="margin-top:24px;text-align:right;">
      <button class="btn btn-secondary" type="button" onclick="closeFareModal()">Cancel</button>
      <button class="btn btn-success" type="button" onclick="submitReservation()">Confirm Reservation</button>
    </div>
  </div>
</div>

<?php include('helpers.php'); ?>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="reservation.js"></script>
<script>
// Modal logic (if needed) from Code 1
</script>
</body>
</html>
