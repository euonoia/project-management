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

(function(){
  var modal = document.getElementById('mapModal');
  var closeBtn = document.getElementById('closeMapModal');
  var useBtn = document.getElementById('useLocationBtn');
  var map, marker, currentMode = null; // 'pickup' or 'dropoff'
  var current = { lat: 14.5995, lng: 120.9842, address: '' };
  var PH_BOUNDS = { south: 4.2158, west: 116.87, north: 21.3218, east: 126.60 };

  function isLatLngInPH(lat, lng) {
    return lat >= PH_BOUNDS.south && lat <= PH_BOUNDS.north && lng >= PH_BOUNDS.west && lng <= PH_BOUNDS.east;
  }

   function ensurePHQuery(q) {
    var hasPH = /philippines|ph\b/i.test(q);
    return hasPH ? q : (q + ', Philippines');
  }

    function setSelectedIfInPH(lat, lng, address) {
    if (!isLatLngInPH(lat, lng)) {
      document.getElementById('address-text').textContent = 'Selected point is outside the Philippines.';
      alert('Please choose a location within the Philippines.');
      return false;
    }
    map.setView([lat, lng], 15);
    marker.setLatLng([lat, lng]);
    updateLocation(lat, lng, address);
    map.invalidateSize();
    return true;
  }

  function openModal(mode) {
    currentMode = mode;
    modal.style.display = 'flex';

    // ✅ Proper cleanup of Leaflet map
    if (map) {
      map.off();    // remove event listeners
      map.remove(); // destroy map instance
      map = null;
      marker = null;
    }

    setTimeout(function(){
      initMapIfNeeded();
      setTimeout(function(){ if (map) map.invalidateSize(); }, 100);
    }, 0);
  }

  function closeModal() {
    modal.style.display = 'none';
  }

 
  function initMapIfNeeded() {
       if (!map) {
      map = L.map('map', {
        maxBounds: [[PH_BOUNDS.south, PH_BOUNDS.west],[PH_BOUNDS.north, PH_BOUNDS.east]],
        maxBoundsViscosity: 0.8
      }).setView([current.lat, current.lng], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
      marker = L.marker([current.lat, current.lng], { draggable: true }).addTo(map);

      marker.on('dragend', function() {
        var ll = marker.getLatLng();
        if (!isLatLngInPH(ll.lat, ll.lng)) {
          alert('Please stay within the Philippines.');
          marker.setLatLng([current.lat, current.lng]);
          map.setView([current.lat, current.lng], map.getZoom());
          return;
        }
        reverseGeocode(ll.lat, ll.lng);
      });
      map.on('click', function(e) {
        if (!isLatLngInPH(e.latlng.lat, e.latlng.lng)) {
          alert('Please choose a location within the Philippines.');
          return;
        }
        marker.setLatLng(e.latlng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
      });

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos){
          var plat = pos.coords.latitude;
          var plng = pos.coords.longitude;
          if (isLatLngInPH(plat, plng)) {
            current.lat = plat;
            current.lng = plng;
          } else {
            console.warn('Geolocation outside PH, defaulting to Manila.');
          }
          map.setView([current.lat, current.lng], 15);
          marker.setLatLng([current.lat, current.lng]);
          reverseGeocode(current.lat, current.lng);
        }, function(){
          reverseGeocode(current.lat, current.lng);
        });
      } else {
        reverseGeocode(current.lat, current.lng);
      }

      var mapSearchInput = document.getElementById('mapSearch');
      var mapSearchForm = document.getElementById('mapSearchForm');
      var mapSearchBtn = document.getElementById('mapSearchBtn');

      // Geocoding providers (all free/open-source backed)
      function providerNominatimPH(query) {
        if (!query) return Promise.reject(new Error('Empty query'));
        document.getElementById('address-text').textContent = 'Searching...';
        var q = ensurePHQuery(query);
        var url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&addressdetails=1&countrycodes=ph&q='
                  + encodeURIComponent(q) + '&accept-language=en&email=logistics2-app@example.com';
        return fetch(url)
          .then(function(res){ if (!res.ok) throw new Error('nominatim error'); return res.json(); })
          .then(function(data){
            if (data && data.length > 0) {
              var lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
              var address = data[0].display_name || '';
              if (!isLatLngInPH(lat, lng)) throw new Error('outside PH');
              return setSelectedIfInPH(lat, lng, address);
            }
            throw new Error('no results');
          });
      }

      function providerMapsCoPH(query) {
        var q = ensurePHQuery(query);
        var url = 'https://geocode.maps.co/search?country=ph&q=' + encodeURIComponent(q);
        return fetch(url)
          .then(function(res){ if (!res.ok) throw new Error('maps.co error'); return res.json(); })
          .then(function(data){
            if (data && data.length > 0) {
              var lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
              var address = data[0].display_name || '';
              if (!isLatLngInPH(lat, lng)) throw new Error('outside PH');
              return setSelectedIfInPH(lat, lng, address);
            }
            throw new Error('no results');
          });
      }

      function providerPhotonPH(query) {
        var q = ensurePHQuery(query);
        var bbox = '116.87,4.2158,126.60,21.3218'; // west,south,east,north
        var url = 'https://photon.komoot.io/api/?q=' + encodeURIComponent(q) + '&limit=5&lang=en&bbox=' + bbox;
        return fetch(url)
          .then(function(res){ if (!res.ok) throw new Error('photon error'); return res.json(); })
          .then(function(data){
            if (data && data.features && data.features.length > 0) {
              var f = data.features[0];
              var lng = f.geometry && f.geometry.coordinates ? parseFloat(f.geometry.coordinates[0]) : NaN;
              var lat = f.geometry && f.geometry.coordinates ? parseFloat(f.geometry.coordinates[1]) : NaN;
              var props = f.properties || {};
              var parts = [];
              if (props.name) parts.push(props.name);
              if (props.street && parts.indexOf(props.street) === -1) parts.push(props.street);
              if (props.city) parts.push(props.city);
              if (props.state) parts.push(props.state);
              parts.push('Philippines');
              var address = parts.filter(Boolean).join(', ');
              if (!isLatLngInPH(lat, lng)) throw new Error('outside PH');
              return setSelectedIfInPH(lat, lng, address);
            }
            throw new Error('no results');
          });
      }

      function providerOpenMeteoPH(query) {
        var q = ensurePHQuery(query);
        var url = 'https://geocoding-api.open-meteo.com/v1/search?name=' + encodeURIComponent(q) + '&count=5&language=en&country=PH';
        return fetch(url)
          .then(function(res){ if (!res.ok) throw new Error('open-meteo error'); return res.json(); })
          .then(function(data){
            if (data && data.results && data.results.length > 0) {
              var r = data.results[0];
              var lat = parseFloat(r.latitude), lng = parseFloat(r.longitude);
              var parts = [r.name, r.admin2, r.admin1, 'Philippines'];
              var address = parts.filter(Boolean).join(', ');
              if (!isLatLngInPH(lat, lng)) throw new Error('outside PH');
              return setSelectedIfInPH(lat, lng, address);
            }
            throw new Error('no results');
          });
      }

      function doSearch(query) {
        return providerNominatimPH(query)
          .catch(function(){ return providerMapsCoPH(query); })
          .catch(function(){ return providerPhotonPH(query); })
          .catch(function(){ return providerOpenMeteoPH(query); })
          .catch(function(){
            document.getElementById('address-text').textContent = 'Location not found within the Philippines.';
            alert('Search failed or is outside the Philippines. Please refine your query, e.g., "dominguez st, malibay, pasay city, metro manila".');
          });
      }

      mapSearchForm.addEventListener('submit', function(e){
        e.preventDefault();
        var query = mapSearchInput.value.trim();
        doSearch(query);
      });

      mapSearchInput.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.keyCode === 13) {
          e.preventDefault();
          mapSearchForm.dispatchEvent(new Event('submit', {cancelable:true}));
        }
      });

      mapSearchInput.addEventListener('search', function(){
        var q = mapSearchInput.value.trim();
        if (q) doSearch(q);
      });
    }
  }

  function updateLocation(lat, lng, address) {
    current.lat = lat; current.lng = lng; current.address = address || '';
    document.getElementById('address-text').textContent = current.address || (lat.toFixed(5) + ', ' + lng.toFixed(5));
  }

  function reverseGeocode(lat, lng) {
    if (!isLatLngInPH(lat, lng)) {
      document.getElementById('address-text').textContent = 'Selected point is outside the Philippines.';
      return;
    }
    var nominatimUrl = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=18&addressdetails=1&lat='
      + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng);
    var used = false;

    function setAddr(addr) {
      used = true;
      updateLocation(lat, lng, addr || '');
    }

    // Primary reverse: Nominatim
    fetch(nominatimUrl)
      .then(function(res){ if (!res.ok) throw new Error('nominatim reverse error'); return res.json(); })
      .then(function(data){
        if (data && data.address && data.address.country_code && data.address.country_code.toLowerCase() !== 'ph') {
          throw new Error('outside PH');
        }
        var addr = (data && data.display_name) || '';
        setAddr(addr);
      })
      .catch(function(){
        // Fallback 1: Photon reverse
        var photonUrl = 'https://photon.komoot.io/reverse?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng);
        return fetch(photonUrl)
          .then(function(res){ if (!res.ok) throw new Error('photon reverse error'); return res.json(); })
          .then(function(data){
            if (used) return;
            if (data && data.features && data.features.length > 0) {
              var p = data.features[0].properties || {};
              var parts = [p.name || p.street, p.city, p.state, 'Philippines'];
              setAddr(parts.filter(Boolean).join(', '));
            } else {
              throw new Error('no photon reverse');
            }
          })
          .catch(function(){
            // Fallback 2: Open-Meteo reverse
            var omUrl = 'https://geocoding-api.open-meteo.com/v1/reverse?latitude=' + encodeURIComponent(lat) + '&longitude=' + encodeURIComponent(lng) + '&language=en';
            return fetch(omUrl)
              .then(function(res){ if (!res.ok) throw new Error('open-meteo reverse error'); return res.json(); })
              .then(function(data){
                if (used) return;
                if (data && data.results && data.results.length > 0) {
                  var r = data.results[0];
                  var parts = [r.name, r.admin2, r.admin1, 'Philippines'];
                  setAddr(parts.filter(Boolean).join(', '));
                } else {
                  setAddr('');
                }
              })
              .catch(function(){
                if (!used) setAddr('');
              });
          });
      });
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
    if (!isLatLngInPH(current.lat, current.lng)) { alert('Selected location must be within the Philippines.'); return; }

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
