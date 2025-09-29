<?php
session_start();
include('../../database/connect.php');
include('includes/helpers.php');
include('includes/queries.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user($dbh, $user_id);
$requester_name = $user ? $user['firstname'] . ' ' . $user['lastname'] : '';

$flash = $_SESSION['flash'] ?? null; 
unset($_SESSION['flash']);

$vehicles = get_vehicles($dbh, $user_id);
$user_reservations = get_user_reservations($dbh, $user_id);
$active_reservation = get_active_reservation($dbh, $user_id);
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

<?php include 'includes/header.php'; ?>

<section id="work" class="bg-gradient-to-b from-gray-100 to-gray-200 py-20">
  <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-12">
    <div class="text-center md:text-left">
      <h2 class="text-5xl md:text-6xl font-extrabold mb-6">Reservation</h2>
      <p class="max-w-xl text-lg text-gray-600 mb-6">
        A modern project management system built to connect drivers and users seamlessly.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
        <a href="../fleetvehiclemanagement/index.php" class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded text-white font-medium">
          Become a Driver
        </a>
      </div>
    </div>

    <div>
      <?php if (!empty($flash)): ?>
        <div class="p-3 border border-gray-300 bg-gray-100 rounded mb-6">
          <strong class="uppercase"><?= e(strtoupper($flash['type'])) ?>:</strong> <?= e($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <?php include 'includes/reservation_form.php'; ?>
    </div>
  </div>
</section>

<?php include 'includes/modals.php'; ?>

<section id="progress" class="py-16 bg-white">
  <div class="max-w-3xl mx-auto mt-12 bg-white rounded-xl shadow-lg p-8">
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
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="javascripts/reservation.js" defer></script>
<script src="javascripts/multi-step.js" defer></script>
<script src="javascripts/stop.js" defer></script>
</body>
</html>
