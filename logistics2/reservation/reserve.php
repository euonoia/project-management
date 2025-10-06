<?php
session_name('user_session');
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

// Fetch vehicles
$vehicles = get_vehicles($dbh, $user_id);
$vehicles_grouped = get_vehicles_grouped($dbh, $user_id);

// Fetch reservations
$active_reservations = get_user_reservations($dbh, $user_id, ['Pending', 'Dispatched']); // Active reservations
$travel_history = get_user_reservations($dbh, $user_id, ['Completed', 'Cancelled']);      // Completed or cancelled

// If you need a single active reservation (for checking form restrictions)
$active_reservation = $active_reservations[0] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="icon" href="../../logo/logo.png">
    <title>Reservation</title>
    <link rel="stylesheet" href="style.css">
    <link href="../../public/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body class="bg-gray-50 text-gray-900">

<!-- Header -->
<?php include 'templates/header.php'; ?>

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
        <?php include 'templates/reservation_form.php'; ?>
      </div>
        <!-- Step Indicators -->
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

    <!-- Reservation Progress Section -->
          <section id="progress" class="py-16 bg-white">
          <div class="container mx-auto px-6">

              <!-- Reservation Progress -->
              <div>
                  <?php include 'templates/progress.php'; ?>
                  <br>
                  <!-- Travel History Button aligned slightly to the right -->
                  <div class="mt-6 flex justify-start">
                      <button onclick="document.getElementById('travelHistoryModal').classList.remove('hidden');"
                              class="px-6 py-3 bg-gray-800 text-white rounded hover:bg-gray-700 transition ml-6">
                          View Travel History
                      </button>
                  </div>
              </div>
          </div>
      </section>




  <!-- Footer -->
  <?php include 'templates/footer.php'; ?>
  <!-- Modals -->
  <?php include 'templates/modals/fare_modal.php'; ?>
  <?php include 'templates/modals/map_modal.php'; ?>
  <?php include 'templates/modals/travel_history.php'; ?>
  
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
