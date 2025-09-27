<?php
session_start();
include('../../database/connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
$is_logged_in = isset($_SESSION['user_id']);

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
    // Support both 'Y-m-d H:i:s' and 'Y-m-dTH:i' inputs
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
    <link rel="stylesheet" href="style.css">
    <link href="../../public/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body class="bg-gray-50 text-gray-900">

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
  <a href="logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded text-sm font-medium">
    Logout
  </a>
<?php endif; ?>

    </div>
  </header>

  <!-- Hero -->
  <section class="bg-gradient-to-b from-gray-100 to-gray-200 py-20">
    <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-2 items-center gap-12">

     <!-- Left content (headline + description) -->
<div class="text-center md:text-left">
  <h2 class="text-5xl md:text-6xl font-extrabold mb-6">
    Drive
  </h2>
  <p class="max-w-xl text-lg text-gray-600 mb-6">
    A modern project management system built to connect drivers and users seamlessly.
  </p>

  <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
      <?php if ($is_logged_in): ?>
    <!-- Logged-in: direct link -->
    <a href="fleetvehiclemanagement/index.php" 
       class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded text-white font-medium">
      Become a Driver
    </a>
  <?php else: ?>
    <!-- Not logged-in: open auth modal -->
    
  <?php endif; ?>

    <!-- Make a Reservation Button (only if logged in) -->
    <?php if ($is_logged_in): ?>
      <a href="reservation/reserve.php" 
         class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded text-white font-medium">
        Make a Reservation
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!$is_logged_in): ?>
<!-- Right content (Sign In button only if not logged in) -->
<div class="text-center md:text-right mt-6 md:mt-0">
  <div class="flex justify-center md:justify-end">
    <button id="heroSignInBtn" 
      class="px-6 py-3 bg-orange-600 hover:bg-orange-500 rounded text-white font-medium">
      Sign In to Continue
    </button>
  </div>
</div>
<?php endif; ?>


      </div>

    </div>
  </section>

  <section class="min-h-screen bg-gray-50">
  <?php if (!empty($flash)): ?>
    <div class="p-3 border border-gray-300 bg-gray-100 rounded my-4 max-w-xl mx-auto">
      <strong class="uppercase"><?= e(strtoupper($flash['type'])) ?>:</strong> <?= e($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <section class="max-w-3xl mx-auto mt-8 bg-white rounded-xl shadow-lg p-8">
    <a href="../index.php" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Return</a>
    <h2 class="text-2xl font-bold mb-6">Create Reservation</h2>
    <?php if ($active_reservation): ?>
      <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded">
        <strong>Note:</strong> You have an active reservation (<b><?= e($active_reservation['reservation_ref']) ?></b>) with status 
        <span class="font-semibold"><?= e($active_reservation['status']) ?></span>.<br>
        Please complete or cancel your current reservation before creating a new one.
      </div>
    <?php elseif (empty($vehicles)): ?>
      <p class="text-gray-500 mb-4">No vehicles found in the system.</p>
      <div class="flex">
        <a class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700" href="../fleetvehiclemanagement/index.php">Open Fleet Vehicle Management</a>
      </div>
    <?php else: ?>
      <?php if (!empty($showing_all) && $showing_all): ?>
        <p class="text-gray-500 mb-4">No vehicles are linked to your account. Showing all vehicles.</p>
      <?php endif; ?>
      <form method="post" action="../connections/vehiclereservationdispatchsystemdb/create_reservation.php" class="space-y-6">
        <input type="hidden" name="action" value="create" />
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="vehicle_registration_id" class="block font-medium mb-1">Vehicle</label>
            <select id="vehicle_registration_id" name="vehicle_registration_id" required class="w-full border rounded px-3 py-2">
              <option value="">-- Select Vehicle --</option>
              <?php foreach ($vehicles as $v): ?>
                <option value="<?= e($v['registration_id']) ?>">
                  <?= e($v['vehicle_plate']) ?> — <?= e($v['car_brand'] . ' ' . $v['model']) ?> (<?= e($v['vehicle_type']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="passengers_count" class="block font-medium mb-1">Passengers</label>
            <input type="number" id="passengers_count" name="passengers_count" min="1" step="1" required class="w-full border rounded px-3 py-2" />
            <div class="text-xs text-gray-500 mt-1">Must not exceed the vehicle capacity.</div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label for="trip_date" class="block font-medium mb-1">Trip Date</label>
            <input type="date" id="trip_date" name="trip_date" required value="<?= e(date('Y-m-d')) ?>" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label for="pickup_time" class="block font-medium mb-1">Pick-up Time</label>
            <input type="time" id="pickup_datetime" name="pickup_datetime" required class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label for="dropoff_time" class="block font-medium mb-1">Drop-off Time</label>
            <input type="time" id="dropoff_datetime" name="dropoff_datetime" required class="w-full border rounded px-3 py-2" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="pickup_location" class="block font-medium mb-1">Pick-up Location</label>
            <div class="flex gap-2">
              <input type="text" id="pickup_location" name="pickup_location" placeholder="e.g., Main Office" required class="flex-1 border rounded px-3 py-2" />
              <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" id="openPickupMap" type="button">Pick on map</button>
            </div>
            <input type="hidden" id="pickup_lat" name="pickup_lat" />
            <input type="hidden" id="pickup_lng" name="pickup_lng" />
            <input type="hidden" id="pickup_address" name="pickup_address" />
            <input type="hidden" id="pickup_location_id" name="pickup_location_id" />
          </div>
          <div>
            <label for="dropoff_location" class="block font-medium mb-1">Drop-off Location</label>
            <div class="flex gap-2">
              <input type="text" id="dropoff_location" name="dropoff_location" placeholder="e.g., Client Site" required class="flex-1 border rounded px-3 py-2" />
              <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" id="openDropoffMap" type="button">Pick on map</button>
            </div>
            <input type="hidden" id="dropoff_lat" name="dropoff_lat" />
            <input type="hidden" id="dropoff_lng" name="dropoff_lng" />
            <input type="hidden" id="dropoff_address" name="dropoff_address" />
            <input type="hidden" id="dropoff_location_id" name="dropoff_location_id" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="requester_name" class="block font-medium mb-1">Requester</label>
            <input type="text" id="requester_name" name="requester_name" placeholder="Who requested?" required value="<?= e($requester_name) ?>" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label for="purpose" class="block font-medium mb-1">Purpose</label>
            <input type="text" id="purpose" name="purpose" placeholder="Trip purpose" class="w-full border rounded px-3 py-2" />
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button type="button" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="showFareModal()">Create Reservation</button>
        </div>
        <input type="hidden" name="distance_km" id="distance_km" />
        <input type="hidden" name="estimated_time" id="estimated_time" />
        <input type="hidden" name="driver_earnings" id="driver_earnings" />
        <input type="hidden" name="passenger_fare" id="passenger_fare" />
        <input type="hidden" name="incentives" id="incentives" />
      </form>
    <?php endif; ?>
  </section>
  
  <section class="max-w-3xl mx-auto mt-12 bg-white rounded-xl shadow-lg p-8">
    <h2 class="text-2xl font-bold mb-6">Reservation Progress</h2>
    <?php if (empty($user_reservations)): ?>
      <div class="text-gray-500">You have no reservations yet.</div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="min-w-full border rounded-lg overflow-hidden">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left font-semibold">Ref</th>
              <th class="px-4 py-2 text-left font-semibold">Date</th>
              <th class="px-4 py-2 text-left font-semibold">Pick-up</th>
              <th class="px-4 py-2 text-left font-semibold">Drop-off</th>
              <th class="px-4 py-2 text-left font-semibold">Status</th>
              <th class="px-4 py-2 text-left font-semibold">Progress</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($user_reservations as $res): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= e($res['reservation_ref']) ?></td>
              <td class="px-4 py-2"><?= e(date('M d, Y', strtotime($res['trip_date']))) ?></td>
              <td class="px-4 py-2"><?= e(date('H:i', strtotime($res['pickup_datetime']))) ?></td>
              <td class="px-4 py-2"><?= e(date('H:i', strtotime($res['dropoff_datetime']))) ?></td>
              <td class="px-4 py-2">
                <span class="inline-block px-2 py-1 rounded bg-blue-100 text-blue-700"><?= e($res['status']) ?></span>
              </td>
              <td class="px-4 py-2">
                <div class="w-full bg-gray-200 rounded h-3">
                  <div class="bg-blue-500 h-3 rounded" style="width:<?= get_progress_percent($res['status']) ?>%"></div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</section>

  <!-- About -->
  <section id="about" class="py-16 bg-neutral-100">
    <div class="container mx-auto px-6 text-center">
      <h3 class="text-3xl font-bold mb-6">About Us</h3>
      <p class="max-w-3xl mx-auto text-gray-700">
        We are your modern logistics and project management partner. Whether you're a user looking to book 
        or a driver wanting opportunities, this system bridges the gap.
      </p>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact" class="bg-black text-white py-8">
    <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
      <p class="text-sm">© 2025 Project Management System. All Rights Reserved.</p>
      <div class="flex gap-6 text-sm">
        <a href="#" class="hover:text-gray-400">WhatsApp</a>
        <a href="#" class="hover:text-gray-400">Instagram</a>
      </div>
    </div>
  </footer>


 <!-- Map Modal -->
  <div id="mapModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 sm:mx-auto overflow-hidden relative">
      <div class="flex justify-between items-center bg-gray-100 px-6 py-4 border-b">
        <strong id="mapModalTitle" class="text-lg font-semibold text-blue-700">Select Location</strong>
        <button type="button" id="closeMapModal" class="text-gray-600 hover:text-red-500 text-2xl font-bold">&times;</button>
      </div>
      <div class="p-6 space-y-4">
        <form id="mapSearchForm" class="flex gap-2" onsubmit="return false;">
          <input type="search" id="mapSearch" class="flex-1 border rounded px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
            placeholder="Search PH address (e.g., dominguez st, malibay, pasay city, metro manila)" autocomplete="off">
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" id="mapSearchBtn">Search</button>
        </form>
        <div id="map" class="w-full h-96 rounded-lg border"></div>
        <div id="selected-info" class="text-gray-700 text-sm">
          <strong>Selected:</strong> <span id="address-text">Drag marker, click map, or search.</span>
        </div>
      </div>
      <div class="px-6 py-4 border-t flex justify-end bg-gray-50">
        <button type="button" id="useLocationBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Use this location</button>
      </div>
    </div>
  </div>

  <!-- Transport Cost Analysis Modal -->
  <div id="fareModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 sm:mx-auto overflow-hidden relative">
      <button onclick="closeFareModal()" class="absolute top-4 right-6 text-2xl text-gray-400 hover:text-red-500">&times;</button>
      <h3 class="text-xl font-bold text-blue-700 px-6 pt-6">Transport Cost Analysis</h3>
      <div id="fareModalBody" class="px-6 py-4"></div>
      <div class="px-6 py-4 border-t flex justify-end bg-gray-50 gap-2">
        <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300" type="button" onclick="closeFareModal()">Cancel</button>
        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" type="button" onclick="submitReservation()">Confirm Reservation</button>
      </div>
    </div>
  </div>

<?php
// Helper function for progress percent
function get_progress_percent($status) {
    switch ($status) {
        case 'Pending': return 20;
        case 'Approved': return 40;
        case 'Dispatched': return 70;
        case 'Completed': return 100;
        case 'Cancelled': return 100;
        default: return 0;
    }
}
?>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="reservation.js" defer></script>
</body>
</html>
