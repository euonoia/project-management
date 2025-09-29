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
     href="../fleetvehiclemanagement/index.php">Open Fleet Vehicle Management</a>
<?php else: ?>
  <form id="reservationForm" method="post" action="../connections/vehiclereservationdispatchsystemdb/create_reservation.php" class="space-y-8">
    <input type="hidden" name="action" value="create" />
    <!-- STEP 1-4 -->
    <?php include 'form_steps.php'; ?>
  </form>
<?php endif; ?>
