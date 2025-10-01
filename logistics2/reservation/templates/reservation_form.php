<?php if ($active_reservation): ?>
  <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-lg">
    <strong>Note:</strong> You have an active reservation 
    (<b><?= e($active_reservation['reservation_ref']) ?></b>) with status 
    <span class="font-semibold"><?= e($active_reservation['status']) ?></span>.<br>
    Please complete or cancel your current reservation before creating a new one.
   
  </div>

<?php elseif (empty($vehicles)): ?>
  <div class="bg-gray-50 border border-gray-200 p-6 rounded-lg mb-6 text-center">
    <p class="text-gray-600 mb-4">No vehicles found in the system.</p>
    <a class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" 
       href="../fleetvehiclemanagement/index.php">
       Open Fleet Vehicle Management
    </a>
  </div>

<?php else: ?>
  <form id="reservationForm" method="post"
        action="../connections/vehiclereservationdispatchsystemdb/create_reservation.php"
        class="space-y-8">
    <input type="hidden" name="action" value="create" />
    <!-- STEP 1-4 -->
    <?php include 'form_steps.php'; ?>
  </form>
<?php endif; ?>
