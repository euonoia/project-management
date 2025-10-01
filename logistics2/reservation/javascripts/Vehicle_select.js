document.addEventListener("DOMContentLoaded", () => {
  const vehicleBtns = document.querySelectorAll(".vehicle-btn");
  const vehicleTypeInput = document.getElementById("vehicle_type");
  const regIdInput = document.getElementById("vehicle_registration_id");
  const passengersInput = document.getElementById("passengers_count");

  let selectedBtn = null; // Track currently selected button

  vehicleBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const type = btn.dataset.type;
      const vehicles = JSON.parse(btn.dataset.vehicles);

      // If no available vehicles → show alert & stop (do NOT select)
      if (!vehicles || vehicles.length === 0) {
        alert("No available " + type.toUpperCase() + " right now.");
        return;
      }

      // If clicked one is already selected → unselect
      if (selectedBtn === btn) {
        resetButton(btn);
        vehicleTypeInput.value = "";
        regIdInput.value = "";
        passengersInput.value = "";
        selectedBtn = null;
        return;
      }

      // Reset all buttons before applying new selection
      vehicleBtns.forEach(b => resetButton(b));

      // Highlight clicked one
      highlightButton(btn);

      // Update hidden fields
      const chosen = vehicles[0];
      vehicleTypeInput.value = type;
      regIdInput.value = chosen.registration_id;
      passengersInput.value = chosen.passenger_capacity;

      selectedBtn = btn; // track selected
    });
  });

  // Reset style helper
  function resetButton(btn) {
    const icon = btn.querySelector("i");
    const label = btn.querySelector("span");
    icon.style.color = "#374151"; // gray-700
    label.classList.remove("text-orange-600");
    label.classList.add("text-gray-700");
  }

  // Highlight style helper
  function highlightButton(btn) {
    const icon = btn.querySelector("i");
    const label = btn.querySelector("span");
    icon.style.color = "#dd6b20"; // orange
    label.classList.remove("text-gray-700");
    label.classList.add("text-orange-600");
  }
});
