<?php
session_start();
include('../../database/connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
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
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
     <?php if (!empty($flash)): ?>
    <div style="padding:10px;border:1px solid #999;margin:8px 0;background:#f7f7f7;">
      <strong><?= e(strtoupper($flash['type'])) ?>:</strong> <?= e($flash['msg']) ?>
    </div>
  <?php endif; ?>
    <section class="panel">
            <a href="../index.php">return</a>
            <h2 style="margin-top:0">Create Reservation</h2>
            <?php if ($active_reservation): ?>
            <div class="muted" style="margin-bottom:16px;">
                <strong>Note:</strong> You have an active reservation (<b><?= e($active_reservation['reservation_ref']) ?></b>) with status <span class="status s-<?= e($active_reservation['status']) ?>"><?= e($active_reservation['status']) ?></span>.<br>
                Please complete or cancel your current reservation before creating a new one.
            </div>
            <?php elseif (empty($vehicles)): ?>
                <p class="muted">No vehicles found in the system.</p>
                <div class="actions"><a class="btn btn-ghost" href="../fleetvehiclemanagement/index.php">Open Fleet Vehicle Management</a></div>
            <?php else: ?>
            <?php if (!empty($showing_all) && $showing_all): ?>
                <p class="muted">No vehicles are linked to your account. Showing all vehicles.</p>
            <?php endif; ?>
            <form method="post" action="../connections/vehiclereservationdispatchsystemdb/create_reservation.php">
                <input type="hidden" name="action" value="create" />
                <div class="row">
                    <div>
                        <label for="vehicle_registration_id">Vehicle</label>
                        <select id="vehicle_registration_id" name="vehicle_registration_id" required>
                            <option value="">-- Select Vehicle --</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= e($v['registration_id']) ?>"><?= e($v['vehicle_plate']) ?> — <?= e($v['car_brand'] . ' ' . $v['model']) ?> (<?= e($v['vehicle_type']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                       
                    </div>
                    <div>
                        <label for="passengers_count">Passengers</label>
                        <input type="number" id="passengers_count" name="passengers_count" min="1" step="1" required />
                        <div class="hint">Must not exceed the vehicle capacity.</div>
                    </div>
                </div>

                <div class="row-3">
                    <div>
                        <label for="trip_date">Trip Date</label>
                        <input type="date" id="trip_date" name="trip_date" required value="<?= e(date('Y-m-d')) ?>" />
                    </div>
                    <div>
                        <label for="pickup_time">Pick-up Time</label>
                        <input type="time" id="pickup_datetime" name="pickup_datetime" required />
                    </div>
                    <div>
                        <label for="dropoff_time">Drop-off Time</label>
                        <input type="time" id="dropoff_datetime" name="dropoff_datetime" required />
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label for="pickup_location">Pick-up Location</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="text" id="pickup_location" name="pickup_location" placeholder="e.g., Main Office" required />
                            <button class="inline-btn" id="openPickupMap" type="button">Pick on map</button>
                        </div>
                        <input type="hidden" id="pickup_lat" name="pickup_lat" />
                        <input type="hidden" id="pickup_lng" name="pickup_lng" />
                        <input type="hidden" id="pickup_address" name="pickup_address" />
                        <input type="hidden" id="pickup_location_id" name="pickup_location_id" />
                    </div>
                    <div>
                        <label for="dropoff_location">Drop-off Location</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="text" id="dropoff_location" name="dropoff_location" placeholder="e.g., Client Site" required />
                            <button class="inline-btn" id="openDropoffMap" type="button">Pick on map</button>
                        </div>
                        <input type="hidden" id="dropoff_lat" name="dropoff_lat" />
                        <input type="hidden" id="dropoff_lng" name="dropoff_lng" />
                        <input type="hidden" id="dropoff_address" name="dropoff_address" />
                        <input type="hidden" id="dropoff_location_id" name="dropoff_location_id" />
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label for="requester_name">Requester</label>
                        <input type="text" id="requester_name" name="requester_name" placeholder="Who requested?" required value="<?= e($requester_name) ?>" />
                    </div>
                    <div>
                        <label for="purpose">Purpose</label>
                        <input type="text" id="purpose" name="purpose" placeholder="Trip purpose" />
                    </div>
                </div>

                <div style="margin-top:12px" class="actions">
                    <button type="button" class="btn btn-primary" onclick="showFareModal()">Create Reservation</button>
                </div>
                <input type="hidden" name="distance_km" id="distance_km" />
<input type="hidden" name="estimated_time" id="estimated_time" />
<input type="hidden" name="driver_earnings" id="driver_earnings" />
<input type="hidden" name="passenger_fare" id="passenger_fare" />
<input type="hidden" name="incentives" id="incentives" />
            </form>
            <?php endif; ?>
        </section>
        
<!-- Map Modal -->
<div id="mapModal" class="map-modal-backdrop" aria-hidden="true">
  <div class="map-modal">
    <div class="map-modal-header">
      <strong id="mapModalTitle">Select Location</strong>
      <button type="button" id="closeMapModal">✕</button>
    </div>
    <div class="map-modal-body">
      <input type="text" id="mapSearch" class="search-box" placeholder="Search location and press Enter">
      <div id="map"></div>
      <div id="selected-info" class="address-preview"><strong>Selected:</strong> <span id="address-text">Drag marker, click map, or search.</span></div>
    </div>
    <div class="map-actions">
      <button type="button" id="useLocationBtn">Use this location</button>
    </div>
  </div>
</div>

<!-- Transport Cost Analysis Modal -->
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

<section class="panel" style="margin-top:32px;">
    <h2>Reservation Progress</h2>
    <?php if (empty($user_reservations)): ?>
        <div class="muted">You have no reservations yet.</div>
    <?php else: ?>
        <table class="styled-table" style="width:100%;margin-top:12px;">
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Date</th>
                    <th>Pick-up</th>
                    <th>Drop-off</th>
                    <th>Status</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($user_reservations as $res): ?>
                <tr>
                    <td><?= e($res['reservation_ref']) ?></td>
                    <td><?= e(date('M d, Y', strtotime($res['trip_date']))) ?></td>
                    <td><?= e(date('H:i', strtotime($res['pickup_datetime']))) ?></td>
                    <td><?= e(date('H:i', strtotime($res['dropoff_datetime']))) ?></td>
                    <td><span class="status s-<?= e($res['status']) ?>"><?= e($res['status']) ?></span></td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:<?= get_progress_percent($res['status']) ?>%"></div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

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
<script>
function haversine(lat1, lon1, lat2, lon2) {
    function toRad(x) { return x * Math.PI / 180; }
    var R = 6371;
    var dLat = toRad(lat2-lat1);
    var dLon = toRad(lon2-lon1);
    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function showFareModal() {
    // Get values from form
    var pickupLat = parseFloat(document.getElementById('pickup_lat').value) || 0;
    var pickupLng = parseFloat(document.getElementById('pickup_lng').value) || 0;
    var dropoffLat = parseFloat(document.getElementById('dropoff_lat').value) || 0;
    var dropoffLng = parseFloat(document.getElementById('dropoff_lng').value) || 0;

    var baseFare = 50;
    var perKmRate = 12;
    var perMinuteRate = 2;
    var platformMargin = 30;
    var incentives = 20;

    var distance = haversine(pickupLat, pickupLng, dropoffLat, dropoffLng);
    var minutes = Math.round((distance / 40) * 60);

    // Format estimated time
    var estimatedTimeStr = '';
    if (minutes >= 60) {
        var hours = Math.floor(minutes / 60);
        var mins = minutes % 60;
        estimatedTimeStr = hours + ' hour' + (hours > 1 ? 's' : '');
        if (mins > 0) {
            estimatedTimeStr += ' ' + mins + ' minute' + (mins > 1 ? 's' : '');
        }
    } else {
        estimatedTimeStr = minutes + ' minute' + (minutes !== 1 ? 's' : '');
    }

    var distanceFare = Math.round(distance * perKmRate);
    var timeFare = Math.round(minutes * perMinuteRate);
    var subtotal = baseFare + distanceFare + timeFare;
    var driverEarnings = subtotal;
    var passengerFare = subtotal + platformMargin;

    var passengerFareDecimal = passengerFare.toFixed(2);

    // Set hidden fields for cost analysis
    document.getElementById('distance_km').value = distance.toFixed(2);
    document.getElementById('estimated_time').value = estimatedTimeStr;
    document.getElementById('driver_earnings').value = driverEarnings.toFixed(2);
    document.getElementById('passenger_fare').value = passengerFareDecimal;
    document.getElementById('incentives').value = incentives.toFixed(2);

    var html = `
        <h6>📍 Trip Details</h6>
        <p><strong>Distance:</strong> ${distance.toFixed(2)} km<br>
           <strong>Estimated Time:</strong> ${estimatedTimeStr}</p>
        <hr>
        <h6>💰 Fare Breakdown</h6>
        <ul>
          <li>Base Fare: ₱${baseFare}</li>
          <li>Distance (₱${perKmRate}/km × ${distance.toFixed(2)} km): ₱${distanceFare}</li>
          <li>Time (₱${perMinuteRate}/min × ${minutes} mins): ₱${timeFare}</li>
        </ul>
        <p><strong>Subtotal:</strong> ₱${subtotal.toFixed(2)}</p>
        <hr>
        <h6>🚖 Distribution</h6>
        <ul>
          <li><strong>Driver Earnings:</strong> ₱${driverEarnings.toFixed(2)} <small>(Base fare + per km + per minute)</small></li>
          <li><strong>Passenger Fare:</strong> ₱${passengerFareDecimal} <small>(Covers costs + platform margin)</small></li>
          <li><strong>Incentives:</strong> ₱${incentives.toFixed(2)} <small>(e.g., fuel cost support)</small></li>
        </ul>
        <div style="background:#e9f7fe;padding:8px;border-radius:8px;margin-top:8px;">
          💡 <strong>Transparency:</strong> Your fare covers driver pay, platform costs, and service sustainability.
        </div>
        <div style="margin-top:16px;padding:12px;background:#d1e7dd;border-radius:8px;font-size:1.2em;text-align:center;">
          <strong>Total Fare for this Trip:</strong> <span style="color:#198754;">₱${passengerFareDecimal}</span>
        </div>
    `;
    document.getElementById('fareModalBody').innerHTML = html;
    document.getElementById('fareModal').style.display = 'flex';
}

function closeFareModal() {
    document.getElementById('fareModal').style.display = 'none';
}

function submitReservation() {
    closeFareModal();
    document.querySelector('form[action*="create_reservation.php"]').submit();
}
</script>
<script>
(function(){
  var modal = document.getElementById('mapModal');
  var closeBtn = document.getElementById('closeMapModal');
  var useBtn = document.getElementById('useLocationBtn');
  var map, marker, currentMode = null; // 'pickup' or 'dropoff'
  var current = { lat: 14.5995, lng: 120.9842, address: '' };

  function openModal(mode) {
    if (!document.getElementById('vehicle_registration_id').value) {
      alert('Please select a vehicle first.');
      return;
    }
    currentMode = mode;
    document.getElementById('mapModalTitle').textContent = mode === 'pickup' ? 'Select Pick-up Location' : 'Select Drop-off Location';
    modal.style.display = 'flex';
    setTimeout(function(){ initMapIfNeeded(); setTimeout(function(){ map.invalidateSize(); }, 50); }, 0);
  }
  function closeModal() {
    modal.style.display = 'none';
  }

  function initMapIfNeeded() {
    if (!map) {
      map = L.map('map').setView([current.lat, current.lng], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
      marker = L.marker([current.lat, current.lng], { draggable: true }).addTo(map);

      marker.on('dragend', function() {
        var ll = marker.getLatLng();
        reverseGeocode(ll.lat, ll.lng);
      });
      map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
      });

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos){
          current.lat = pos.coords.latitude;
          current.lng = pos.coords.longitude;
          map.setView([current.lat, current.lng], 15);
          marker.setLatLng([current.lat, current.lng]);
          reverseGeocode(current.lat, current.lng);
        }, function(){
          reverseGeocode(current.lat, current.lng);
        });
      } else {
        reverseGeocode(current.lat, current.lng);
      }

      document.getElementById('mapSearch').addEventListener('keyup', function(e){
        if (e.keyCode === 13) {
          var query = e.target.value.trim();
          if (!query) return;
          fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
            .then(function(res){ return res.json(); })
            .then(function(data){
              if (data && data.length > 0) {
                var lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
                var address = data[0].display_name || '';
                map.setView([lat, lng], 15);
                marker.setLatLng([lat, lng]);
                updateLocation(lat, lng, address);
              } else {
                alert('Location not found');
              }
            });
        }
      });
    }
  }

  function updateLocation(lat, lng, address) {
    current.lat = lat; current.lng = lng; current.address = address || '';
    document.getElementById('address-text').textContent = current.address || (lat.toFixed(5) + ', ' + lng.toFixed(5));
  }

  function reverseGeocode(lat, lng) {
    fetch('https://nominatim.openstreetmap.org/reverse?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng) + '&format=json')
      .then(function(res){ return res.json(); })
      .then(function(data){
        updateLocation(lat, lng, data && data.display_name ? data.display_name : '');
      })
      .catch(function(){ updateLocation(lat, lng, ''); });
  }

  // Public triggers
  document.getElementById('openPickupMap').addEventListener('click', function(e){ e.preventDefault(); openModal('pickup'); });
  document.getElementById('openDropoffMap').addEventListener('click', function(e){ e.preventDefault(); openModal('dropoff'); });

  // Close handlers
  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });

  // Use button handler
  useBtn.addEventListener('click', function(){
    if (!currentMode) return;
    var veh = document.getElementById('vehicle_registration_id').value;
    if (!veh) { alert('Please select a vehicle first.'); return; }

    // Update reservation form fields only
    if (currentMode === 'pickup') {
      document.getElementById('pickup_location').value = current.address || (current.lat.toFixed(5) + ', ' + current.lng.toFixed(5));
      document.getElementById('pickup_lat').value = current.lat;
      document.getElementById('pickup_lng').value = current.lng;
      document.getElementById('pickup_address').value = current.address || '';
    } else {
      document.getElementById('dropoff_location').value = current.address || (current.lat.toFixed(5) + ', ' + current.lng.toFixed(5));
      document.getElementById('dropoff_lat').value = current.lat;
      document.getElementById('dropoff_lng').value = current.lng;
      document.getElementById('dropoff_address').value = current.address || '';
    }
    closeModal();
  });
})();
</script>
</body>
</html>
