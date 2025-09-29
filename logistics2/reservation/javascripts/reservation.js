document.getElementById("mapSearchForm").addEventListener("keydown", function(e) {
  if (e.key === "Enter") {
    e.preventDefault(); // stop form submit
    e.stopPropagation(); // stop bubbling to multi-step handler
    document.getElementById("mapSearchBtn").click(); // trigger your search button instead
  }
});

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



(function() {
  const modal = document.getElementById('mapModal');
  const closeBtn = document.getElementById('closeMapModal');
  const useBtn = document.getElementById('useLocationBtn');
  const tripInput = document.getElementById('trip_locations');

  let map = null, marker = null, currentMode = null;
  let current = { lat: 14.5995, lng: 120.9842, address: '' };
  let pickupAddress = '';
  let dropoffAddress = '';
  const PH_BOUNDS = { south: 4.2158, west: 116.87, north: 21.3218, east: 126.60 };

  let tripStep = 'pickup'; // first click = pickup

  function isLatLngInPH(lat, lng) {
    return lat >= PH_BOUNDS.south && lat <= PH_BOUNDS.north &&
           lng >= PH_BOUNDS.west && lng <= PH_BOUNDS.east;
  }

  function ensurePHQuery(q) {
    return /philippines|ph\b/i.test(q) ? q : q + ', Philippines';
  }

  function updateLocation(lat, lng, address) {
    current.lat = lat;
    current.lng = lng;
    current.address = address || '';
    document.getElementById('address-text').textContent = current.address || (lat.toFixed(5) + ', ' + lng.toFixed(5));
  }

  function setSelectedIfInPH(lat, lng, address) {
    if (!isLatLngInPH(lat, lng)) {
      alert('Please choose a location within the Philippines.');
      document.getElementById('address-text').textContent = 'Selected point is outside the Philippines.';
      return false;
    }
    map.setView([lat, lng], 15);
    marker.setLatLng([lat, lng]);
    updateLocation(lat, lng, address);
    map.invalidateSize();
    return true;
  }

  function initMapIfNeeded() {
    if (map) return;

    map = L.map('map', {
      maxBounds: [[PH_BOUNDS.south, PH_BOUNDS.west],[PH_BOUNDS.north, PH_BOUNDS.east]],
      maxBoundsViscosity: 0.8
    }).setView([current.lat, current.lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    marker = L.marker([current.lat, current.lng], { draggable: true }).addTo(map);

    marker.on('dragend', () => {
      const ll = marker.getLatLng();
      if (!isLatLngInPH(ll.lat, ll.lng)) {
        alert('Please stay within the Philippines.');
        marker.setLatLng([current.lat, current.lng]);
        map.setView([current.lat, current.lng], map.getZoom());
        return;
      }
      reverseGeocode(ll.lat, ll.lng);
    });

    map.on('click', e => {
      if (!isLatLngInPH(e.latlng.lat, e.latlng.lng)) {
        alert('Please choose a location within the Philippines.');
        return;
      }
      marker.setLatLng(e.latlng);
      reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(pos => {
        const plat = pos.coords.latitude;
        const plng = pos.coords.longitude;
        if (isLatLngInPH(plat, plng)) {
          current.lat = plat;
          current.lng = plng;
        }
        map.setView([current.lat, current.lng], 15);
        marker.setLatLng([current.lat, current.lng]);
        reverseGeocode(current.lat, current.lng);
      }, () => reverseGeocode(current.lat, current.lng));
    } else reverseGeocode(current.lat, current.lng);

    const mapSearchInput = document.getElementById('mapSearch');
    const mapSearchForm = document.getElementById('mapSearchForm');

    // --- Providers ---
    function providerNominatimPH(query) {
      if (!query) return Promise.reject('Empty query');
      const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&addressdetails=1&countrycodes=ph&q=${encodeURIComponent(ensurePHQuery(query))}&accept-language=en`;
      document.getElementById('address-text').textContent = 'Searching...';
      return fetch(url)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          if (data.length === 0) return Promise.reject();
          const lat = parseFloat(data[0].lat);
          const lng = parseFloat(data[0].lon);
          return setSelectedIfInPH(lat, lng, data[0].display_name);
        });
    }

    function providerMapsCoPH(query) {
      const url = `https://geocode.maps.co/search?country=ph&q=${encodeURIComponent(ensurePHQuery(query))}`;
      return fetch(url)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          if (data.length === 0) return Promise.reject();
          const lat = parseFloat(data[0].lat);
          const lng = parseFloat(data[0].lon);
          return setSelectedIfInPH(lat, lng, data[0].display_name);
        });
    }

    function providerPhotonPH(query) {
      const bbox = '116.87,4.2158,126.60,21.3218';
      const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(ensurePHQuery(query))}&limit=5&lang=en&bbox=${bbox}`;
      return fetch(url)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          if (!data.features || data.features.length === 0) return Promise.reject();
          const f = data.features[0];
          const lat = f.geometry.coordinates[1];
          const lng = f.geometry.coordinates[0];
          const props = f.properties || {};
          const address = [props.name, props.street, props.city, props.state, 'Philippines'].filter(Boolean).join(', ');
          return setSelectedIfInPH(lat, lng, address);
        });
    }

    function providerOpenMeteoPH(query) {
      const url = `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(ensurePHQuery(query))}&count=5&language=en&country=PH`;
      return fetch(url)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          if (!data.results || data.results.length === 0) return Promise.reject();
          const r = data.results[0];
          const lat = r.latitude;
          const lng = r.longitude;
          const address = [r.name, r.admin2, r.admin1, 'Philippines'].filter(Boolean).join(', ');
          return setSelectedIfInPH(lat, lng, address);
        });
    }

    function doSearch(query) {
      return providerNominatimPH(query)
        .catch(() => providerMapsCoPH(query))
        .catch(() => providerPhotonPH(query))
        .catch(() => providerOpenMeteoPH(query))
        .catch(() => {
          alert('Location not found within the Philippines.');
          document.getElementById('address-text').textContent = 'Location not found.';
        });
    }

    // 🔹 Manual submit (Enter key)
    mapSearchForm.addEventListener('submit', e => {
      e.preventDefault();
      const query = mapSearchInput.value.trim();
      if (query) doSearch(query);
    });

    mapSearchInput.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        mapSearchForm.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });

    // 🔹 Live search on typing (debounced)
    let debounceTimeout;
    mapSearchInput.addEventListener('input', () => {
      clearTimeout(debounceTimeout);
      const query = mapSearchInput.value.trim();
      if (!query) {
        document.getElementById('address-text').textContent = 'Drag marker, click map, or search.';
        return;
      }

      debounceTimeout = setTimeout(() => {
        doSearch(query);
      }, 300); // adjust debounce delay as needed
    });
}


  function reverseGeocode(lat, lng) {
    if (!isLatLngInPH(lat, lng)) {
      document.getElementById('address-text').textContent = 'Outside PH';
      return;
    }
    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=18&addressdetails=1&lat=${lat}&lon=${lng}`)
      .then(r => r.ok ? r.json() : Promise.reject())
      .then(data => updateLocation(lat, lng, data.display_name))
      .catch(() => updateLocation(lat, lng, `${lat.toFixed(5)}, ${lng.toFixed(5)}`));
  }

    function openModal(step) {
  currentMode = step; // pickup or dropoff
  modal.style.display = 'flex';

  // Update modal title dynamically
  const modalTitle = document.getElementById('mapModalTitle');
  modalTitle.textContent = currentMode === 'pickup' 
      ? 'Select Pick-up Location' 
      : 'Select Drop-off Location';

  // Remove previous map if exists
  if (map) { 
    map.off(); 
    map.remove(); 
    map = null; 
    marker = null; 
  }

  // Initialize the map after modal is visible
  setTimeout(() => { 
    initMapIfNeeded(); 
    map.invalidateSize(); 
  }, 0);
}

// Click listener for the input
tripInput.addEventListener('click', () => openModal(tripStep));

          function closeModal() { modal.style.display = 'none'; }
          
          tripInput.addEventListener('click', () => openModal(tripStep)); 
          closeBtn.addEventListener('click', closeModal);
          modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
          
          useBtn.addEventListener('click', () => {
          if (!currentMode || !isLatLngInPH(current.lat, current.lng)) {
            return alert('Select a valid PH location.');
          }

          if (currentMode === 'pickup') {
            // Save pickup hidden fields
            document.getElementById('pickup_lat').value = current.lat;
            document.getElementById('pickup_lng').value = current.lng;
            document.getElementById('pickup_location').value = current.address;
            pickupAddress = current.address;

            // Show progress in readonly input
            tripInput.value = `Pick-up: ${pickupAddress} → `;

            // Switch to dropoff mode automatically (keep modal open)
            currentMode = 'dropoff';
            tripStep = 'dropoff';

            // Optionally, update modal header/instructions
            document.getElementById('mapModalTitle').textContent = "Select Drop-off Location";

            return; // stop here so modal doesn’t close yet
          }

          if (currentMode === 'dropoff') {
            // Prevent same as pickup
            if (current.address === pickupAddress) {
              return alert("Drop-off location cannot be the same as pick-up location. Please choose a different location.");
            }

            // Save dropoff hidden fields
            document.getElementById('dropoff_lat').value = current.lat;
            document.getElementById('dropoff_lng').value = current.lng;
            document.getElementById('dropoff_location').value = current.address;
            dropoffAddress = current.address;

            // Show both in readonly input
            tripInput.value = `Pick-up: ${pickupAddress} → Drop-off: ${dropoffAddress}`;

            // Reset for future trips
            tripStep = 'pickup';
            currentMode = null;

            // Now we can close the modal
            closeModal();
          }
        });



})();

