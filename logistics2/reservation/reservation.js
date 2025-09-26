
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

