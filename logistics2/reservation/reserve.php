<?php
session_start();
include('../../database/connect.php');
include('includes/helpers.php');
include('includes/queries.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$is_logged_in = true;
$user_id = (string)$_SESSION['user_id'];

// Fetch user info
$user = get_user($dbh, $user_id);
$requester_name = $user ? $user['firstname'] . ' ' . $user['lastname'] : '';

// Flash message
$flash = $_SESSION['flash'] ?? null; 
unset($_SESSION['flash']);

// Fetch vehicles, reservations
$vehicles = get_vehicles($dbh, $user_id);
$user_reservations = get_user_reservations($dbh, $user_id);
$active_reservation = get_active_reservation($dbh, $user_id);
$vehicles_grouped = get_vehicles_grouped($dbh, $user_id);

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
      <h1 class="text-3xl font-extrabold tracking-wide">Drive</h1>
      <nav class="flex items-center gap-8 text-sm uppercase">
        <a href="#progress" class="hover:text-gray-300">Progress</a>
        <?php if ($is_logged_in): ?>
          <a href="../logout.php" 
            class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded text-sm font-medium uppercase">
            Logout
          </a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

        <!-- Hero Section with Create Reservation Form -->
          <section id="work" class="bg-gradient-to-b from-gray-100 to-gray-200 py-20">
  <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-2 items-start gap-12">

    <!-- Left content -->
    <div class="text-center md:text-left">
      <h2 class="text-5xl md:text-6xl font-extrabold mb-6">Reservation</h2>
      <p class="max-w-xl text-lg text-gray-600 mb-6">
        A modern project management system built to connect drivers and users seamlessly.
      </p>

      <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
        <?php if ($is_logged_in): ?>
          <a href="../fleetvehiclemanagement/index.php" 
             class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded text-white font-medium">
            Become a Driver
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right content (Create Reservation Form) -->
    <div>
      <?php if (!empty($flash)): ?>
        <div class="p-3 border border-gray-300 bg-gray-100 rounded mb-6">
          <strong class="uppercase"><?= e(strtoupper($flash['type'])) ?>:</strong> <?= e($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <div class="bg-white rounded-xl shadow-lg p-8 relative min-h-[500px]">

        <?php if ($active_reservation): ?>
          <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded">
            <strong>Note:</strong> You have an active reservation 
            (<b><?= e($active_reservation['reservation_ref']) ?></b>) with status 
            <span class="font-semibold"><?= e($active_reservation['status']) ?></span>.<br>
            Please complete or cancel your current reservation before creating a new one.
          </div>

        <?php elseif (empty($vehicles)): ?>
          <p class="text-gray-500 mb-4">No vehicles found in the system.</p>
          <a class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700" 
             href="../fleetvehiclemanagement/index.php">
            Open Fleet Vehicle Management
          </a>

        <?php else: ?>
          <form id="reservationForm" method="post" action="../connections/vehiclereservationdispatchsystemdb/create_reservation.php" class="space-y-8">
            <input type="hidden" name="action" value="create" />

            <!-- STEP 1 -->
            <div class="form-step space-y-4" data-step="1">
              <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Trip Locations</h2>
              <div>
                <br>
                <input type="text" id="trip_locations" name="trip_locations" placeholder="Click to select pick-up & drop-off" readonly class="w-full border rounded-lg px-3 py-2 cursor-pointer bg-gray-50" />
              </div>
             
            </div>

      
          <!-- STEP 2 -->
    <div class="form-step space-y-4 hidden" data-step="2">
      <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Vehicle Selection</h2>
      <br>

      <div>
        <div class="flex flex-wrap gap-4 justify-between">
          <?php foreach (['sedan','suv','hatchback','mpv','van','others'] as $type): ?>
            <button 
              type="button" 
              class="vehicle-btn border rounded-lg p-4 flex flex-col items-center hover:bg-blue-50 flex-1 min-w-[100px]"
              data-type="<?= $type ?>"
              data-vehicles='<?= json_encode($availableVehicles[$type] ?? []) ?>'>
              <?php if ($type === 'sedan'): ?>
                <i class="fas fa-car-side text-2xl mb-2 icon text-gray-700"></i>
              <?php elseif ($type === 'suv'): ?>
                <i class="fas fa-truck-monster text-2xl mb-2 icon text-gray-700"></i>
              <?php elseif ($type === 'hatchback'): ?>
                <i class="fas fa-car text-2xl mb-2 icon text-gray-700"></i>
              <?php elseif ($type === 'mpv'): ?>
                <i class="fas fa-van-shuttle text-2xl mb-2 icon text-gray-700"></i>
              <?php elseif ($type === 'van'): ?>
                <i class="fas fa-shuttle-van text-2xl mb-2 icon text-gray-700"></i>
              <?php else: ?>
                <i class="fas fa-car-rear text-2xl mb-2 icon text-gray-700"></i>
              <?php endif; ?>
              <span class="label text-sm capitalize text-gray-700"><?= ucfirst($type) ?></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Hidden fields -->
      <input type="hidden" id="vehicle_type" name="vehicle_type">
      <input type="hidden" id="vehicle_registration_id" name="vehicle_registration_id">
      <input type="hidden" id="passengers_count" name="passengers_count">
    </div>
                      

            <!-- STEP 3 -->
            <div class="form-step space-y-4 hidden" data-step="3">
              <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Schedule</h2>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label for="trip_date" class="block font-medium mb-1">Trip Date</label>
                  <input type="date" id="trip_date" name="trip_date" required value="<?= e(date('Y-m-d')) ?>" class="w-full border rounded-lg px-3 py-2" />
                </div>
                <div>
                  <label for="pickup_time" class="block font-medium mb-1">Pick-up Time <span class="text-gray-400">(optional)</span></label>
                  <input type="time" id="pickup_datetime" name="pickup_datetime" class="w-full border rounded-lg px-3 py-2" />
                </div>
                <div>
                  <label for="dropoff_time" class="block font-medium mb-1">Drop-off Time <span class="text-gray-400">(optional)</span></label>
                  <input type="time" id="dropoff_datetime" name="dropoff_datetime" class="w-full border rounded-lg px-3 py-2" />
                </div>
              </div>
              
            </div>

            <!-- STEP 4 -->
            <div class="form-step space-y-4 hidden" data-step="4">
              <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Additional Info</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="requester_name" class="block font-medium mb-1">Requester</label>
                  <input type="text" id="requester_name" name="requester_name" placeholder="Who requested?" required value="<?= e($requester_name) ?>" class="w-full border rounded-lg px-3 py-2" />
                </div>
                <div>
                  <label for="purpose" class="block font-medium mb-1">Trip Description</label>
                  <input type="text" id="purpose" name="purpose" placeholder="Trip purpose" class="w-full border rounded-lg px-3 py-2" />
                </div>
              </div>

              <!-- Hidden fields -->
              <input type="hidden" id="pickup_lat" name="pickup_lat">
              <input type="hidden" id="pickup_lng" name="pickup_lng">
              <input type="hidden" id="pickup_location" name="pickup_location">
              <input type="hidden" id="dropoff_lat" name="dropoff_lat">
              <input type="hidden" id="dropoff_lng" name="dropoff_lng">
              <input type="hidden" id="dropoff_location" name="dropoff_location">
              <input type="hidden" id="distance_km" name="distance_km">
              <input type="hidden" id="estimated_time" name="estimated_time">
              <input type="hidden" id="driver_earnings" name="driver_earnings">
              <input type="hidden" id="passenger_fare" name="passenger_fare">
              <input type="hidden" id="incentives" name="incentives">

              <div class="pt-6 flex justify-between">
                <button type="button" onclick="showFareModal()" class="px-6 py-3 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">Create Reservation</button>
              </div>
            </div>
          </form>
        <?php endif; ?>

        <!-- Step Dots: always render -->
       

      </div>
      <br>
       <div class="flex justify-center mt-6 space-x-3 absolute bottom-4 left-0 w-full z-10">
        <div class="step-indicator" style="width:20px; height:20px; border-radius:50%; background-color:#d1d5db; border:1px solid #9ca3af;"></div>
        <div class="step-indicator" style="width:20px; height:20px; border-radius:50%; background-color:#d1d5db; border:1px solid #9ca3af;"></div>
        <div class="step-indicator" style="width:20px; height:20px; border-radius:50%; background-color:#d1d5db; border:1px solid #9ca3af;"></div>
        <div class="step-indicator" style="width:20px; height:20px; border-radius:50%; background-color:#d1d5db; border:1px solid #9ca3af;"></div>
        </div>
    </div>

  </div>
</section>

  <section id="progress" class="py-16 bg-white">
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
        <strong id="mapModalTitle" class="text-lg font-semibold text-blue-700">Select Pick-up Location</strong>
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

<script>
  const vehiclesData = <?= json_encode($vehicles_grouped) ?>;
</script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="javascripts/reservation.js" defer></script>
<script src="javascripts/multi-step.js" defer></script>
<script src="javascripts/stop.js" defer></script>
<script src="javascripts/Auto-capacity.js" defer></script>
<script src="javascripts/Vehicle_select.js" defer></script>

</body>
</html>
