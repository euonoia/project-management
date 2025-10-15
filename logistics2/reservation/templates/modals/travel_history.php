<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div id="travelHistoryModal" 
     class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">

  <!-- Modal Box -->
  <div 
  class="bg-white shadow-2xl w-full max-w-3xl mx-4 sm:mx-auto flex flex-col max-h-[80vh]" 
  style="border-radius: 1rem; overflow: hidden;" >
   
  <!-- Header -->
       <div class="flex justify-between items-center bg-gray-50 px-6 py-4 border-b flex-shrink-0">
        <strong class="text-xl font-semibold text-blue-700">Travel History</strong>
        <button type="button" 
                onclick="document.getElementById('travelHistoryModal').classList.add('hidden')" 
                class="text-gray-600 hover:text-red-500 text-2xl font-bold transition-colors">&times;</button>
        </div>
    <!-- Search & Filter Row -->
    <div class="px-6 py-3 border-b bg-white flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
      <!-- Search -->
      <input type="text" 
             id="searchHistory" 
             placeholder="Search trips..." 
             class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">

      <!-- Preset Filter -->
      <select id="filterHistory" 
              class="px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <option value="all">All</option>
        <option value="today">Today</option>
        <option value="last7">Last 7 Days</option>
        <option value="last30">Last 30 Days</option>
        <option value="month">This Month</option>
        <option value="custom">Custom Range</option>
      </select>

      <!-- Custom Date Range (hidden until selected) -->
      <div id="customRange" class="hidden">
        <input 
            type="text" 
            id="dateRange" 
            placeholder="Select date range..." 
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
        >
      </div>

      <!-- Clear -->
      <button id="clearFilters" 
        class="px-4 py-2 text-sm font-medium text-gray-700 
                bg-gray-100 border border-gray-300 rounded-lg 
                hover:bg-gray-200 hover:text-gray-900 
                transition-colors duration-200">
        Clear 
        </button>
    </div>

    <!-- Scrollable Content -->
    <div id="travelHistoryContent" class="flex-1 overflow-y-auto p-6 space-y-4" style="height:100%; max-height:40vh; overflow-y:auto;">
      <?php if (empty($travel_history)): ?>
        <p class="text-gray-500 text-center py-20">No completed or canceled trips yet.</p>
      <?php else: ?>
        <?php foreach ($travel_history as $res): ?>
          <?php
            $status = strtolower($res['status']);
            $tripDate = date('Y-m-d', strtotime($res['trip_date'])); 
            $badgeColor = $status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            $statusIcon = $status === 'completed' ? 'fas fa-flag-checkered' : 'fas fa-times-circle';
          ?>

          <!--this is the reservation card-->
        <div 
          class="trip-card relative bg-white border border-gray-200 p-6 
                  shadow-md hover:shadow-xl transition transform hover:-translate-y-1 mb-6"
          style="border-radius: 1.25rem; box-shadow: 0 4px 10px rgba(0,0,0,0.08);"
          data-date="<?= $tripDate ?>"
          data-text="<?= strtolower($res['reservation_ref'].' '.$res['pickup_location'].' '.$res['dropoff_location']) ?>"
      >
          <!-- Status Badge -->
          <span class="absolute top-4 right-4 inline-flex items-center px-3 py-1 
                      rounded-full text-xs font-medium tracking-wide <?= $badgeColor ?>">
              <i class="<?= $statusIcon ?> mr-1"></i>
              <?= ucfirst($res['status']) ?>
          </span>

          <!-- Reservation Info -->
          <div class="mb-4">
              <p class="font-semibold text-lg text-gray-900"><?= e($res['reservation_ref']) ?></p>
              <p class="text-gray-500 text-sm"><?= e(date('M d, Y', strtotime($res['trip_date']))) ?></p>
          </div>
          <div class="flex items-center space-x-3">
              <i class="fas fa-user text-blue-500 w-5 text-center"></i>
              <span class="text-sm">
                  <strong>Driver:</strong> <?= e($res['driver_name'] ?? 'Not assigned') ?>
              </span>
          </div>
          <!-- Trip Details -->
          <div class="space-y-3">
              <div class="flex items-center space-x-3">
                  <i class="fas fa-map-marker-alt text-red-500 w-5 text-center"></i>
                  <span class="text-sm"><strong>Pick-up:</strong> <?= e($res['pickup_location']) ?></span>
              </div>
              <div class="flex items-center space-x-3">
                  <i class="fas fa-flag-checkered text-green-500 w-5 text-center"></i>
                  <span class="text-sm"><strong>Drop-off:</strong> <?= e($res['dropoff_location']) ?></span>
              </div>
              <div class="flex items-center space-x-3">
                  <i class="fas fa-clock text-gray-400 w-5 text-center"></i>
                  <span class="text-sm"><strong>Time:</strong> <?= e(date('H:i', strtotime($res['pickup_datetime']))) ?> - <?= e(date('H:i', strtotime($res['dropoff_datetime']))) ?></span>
              </div>
          </div>

          <!-- Action Buttons -->
          <div class="mt-4 flex gap-2 justify-end">
              <?php if ($res['status'] === 'Completed'): ?>
                  <button 
                      type="button"
                      class="px-4 py-2 bg-green-500 text-black rounded-lg shadow hover:bg-yellow-600 transition w-auto max-w-[200px]"
                      onclick="openRateDriverModal(<?= (int)$res['id'] ?>)"
                  >
                      Rate Driver
                  </button>
              <?php endif; ?>
          </div>
      </div>


        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>


<!-- Rate Driver Modal -->
<div id="rateDriverModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden" style="z-index:9999;">
    <div class="bg-white rounded-lg p-6 w-96 relative shadow-2xl">
        <h2 class="text-xl font-semibold mb-4">Rate Driver</h2>
        <!-- 5 Star Rating -->
        <div id="starRating" class="flex space-x-2 text-2xl cursor-pointer">
            <span data-value="1">&#9733;</span>
            <span data-value="2">&#9733;</span>
            <span data-value="3">&#9733;</span>
            <span data-value="4">&#9733;</span>
            <span data-value="5">&#9733;</span>
        </div>
        <textarea id="ratingNotes" placeholder="Optional feedback..." class="w-full mt-4 border p-2 rounded"></textarea>
        <div class="mt-4 flex justify-end gap-2">
            <button onclick="submitRating()" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Submit</button>
            <button onclick="closeRateDriverModal()" class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400">Cancel</button>
        </div>
    </div>
</div>


<!-- Filtering Script -->
<script>
let selectedReservationId = null;
let selectedRating = 0;
let customRangePicker;
let selectedRange = null;

// Init Flatpickr for custom range
document.addEventListener("DOMContentLoaded", () => {
  customRangePicker = flatpickr("#dateRange", {
    mode: "range",
    dateFormat: "Y-m-d",
    onClose: function(dates) {
      if (dates.length === 2) {
        selectedRange = { start: dates[0], end: dates[1] };
        filterTrips();
      }
    }
  });
});

function filterTrips() {
  let query = document.getElementById("searchHistory").value.toLowerCase();
  let filter = document.getElementById("filterHistory").value;
  let cards = document.querySelectorAll("#travelHistoryContent .trip-card");
  let today = new Date();
  let hasResults = false;

  cards.forEach(card => {
    let text = card.dataset.text;
    let tripDate = new Date(card.dataset.date);
    let show = true;

    // Text search
    if (query && !text.includes(query)) show = false;

    // Date filters
    if (filter === "today") {
      show = tripDate.toDateString() === today.toDateString();
    } else if (filter === "last7") {
      let past7 = new Date();
      past7.setDate(today.getDate() - 7);
      if (!(tripDate >= past7 && tripDate <= today)) show = false;
    } else if (filter === "last30") {
      let past30 = new Date();
      past30.setDate(today.getDate() - 30);
      if (!(tripDate >= past30 && tripDate <= today)) show = false;
    } else if (filter === "month") {
      if (!(tripDate.getMonth() === today.getMonth() && tripDate.getFullYear() === today.getFullYear())) {
        show = false;
      }
    } else if (filter === "custom" && selectedRange) {
      if (!(tripDate >= selectedRange.start && tripDate <= selectedRange.end)) show = false;
    }

    card.style.display = show ? "" : "none";
    if (show) hasResults = true;
  });

  // No results message
  let msg = document.getElementById("noResults");
  if (!hasResults) {
    if (!msg) {
      msg = document.createElement("p");
      msg.id = "noResults";
      msg.className = "text-gray-500 text-center py-10";
      msg.textContent = "No trips found for this filter.";
      document.getElementById("travelHistoryContent").appendChild(msg);
    }
  } else {
    if (msg) msg.remove();
  }
}

// Events
document.getElementById("searchHistory").addEventListener("input", filterTrips);
document.getElementById("filterHistory").addEventListener("change", (e) => {
  let customRange = document.getElementById("customRange");
  customRange.classList.toggle("hidden", e.target.value !== "custom");
  filterTrips();
});
document.getElementById("clearFilters").addEventListener("click", () => {
  document.getElementById("searchHistory").value = "";
  document.getElementById("filterHistory").value = "all";
  document.getElementById("customRange").classList.add("hidden");
  if (customRangePicker) customRangePicker.clear();
  selectedRange = null;
  filterTrips();
});
function openRateDriverModal(reservationId) {
    selectedReservationId = reservationId;
    selectedRating = 0;
    const modal = document.getElementById('rateDriverModal');
    modal.classList.remove('hidden');
    modal.style.zIndex = 9999; // ensure it's on top
    updateStars(0);
}

function closeRateDriverModal() {
    document.getElementById('rateDriverModal').classList.add('hidden');
}


const stars = document.querySelectorAll('#starRating span');
stars.forEach(star => {
    star.addEventListener('mouseover', () => updateStars(star.dataset.value));
    star.addEventListener('click', () => selectedRating = star.dataset.value);
});

function updateStars(rating) {
    stars.forEach(star => {
        star.style.color = star.dataset.value <= rating ? '#fbbf24' : '#d1d5db'; // yellow or gray
    });
}

function submitRating() {
    const notes = document.getElementById('ratingNotes').value;
    if (!selectedRating) {
        alert('Please select a star rating.');
        return;
    }

    // Example: submit via fetch/AJAX
    fetch('rate_driver.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            reservation_id: selectedReservationId,
            rating: selectedRating,
            notes: notes
        })
    }).then(res => res.json())
      .then(data => {
          if (data.success) {
              alert('Driver rated successfully!');
              closeRateDriverModal();
          } else {
              alert(data.message || 'Failed to rate driver.');
          }
      });
}
</script>
