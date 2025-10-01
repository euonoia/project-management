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
  <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Vehicle & Passengers</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
      <label for="vehicle_registration_id" class="block font-medium mb-1">Vehicle</label>
      <select id="vehicle_registration_id" name="vehicle_registration_id" required class="w-full border rounded-lg px-3 py-2">
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
      <input type="number" id="passengers_count" name="passengers_count" min="1" step="1" required class="w-full border rounded-lg px-3 py-2" />
      
    </div>
  </div>
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

  <!-- Hidden fields for location, distance, fare etc. -->
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