document.addEventListener("DOMContentLoaded", () => {
  let currentStep = 1;
  const steps = Array.from(document.querySelectorAll(".form-step"));
  const indicators = document.querySelectorAll(".step-indicator");
  const totalSteps = steps.length;
  const mapModal = document.getElementById("mapModal");

  function showStep(step) {
    steps.forEach((s, i) => {
      if (i === step - 1) {
        s.classList.remove("hidden");
        s.classList.add("opacity-100", "translate-x-0", "transition-all", "duration-500", "ease-out");
        s.classList.remove("opacity-0", "translate-x-4");
      } else {
        s.classList.add("hidden"); // hide immediately to prevent double form
        s.classList.remove("opacity-100", "translate-x-0");
        s.classList.add("opacity-0", "translate-x-4");
      }
    });

    // Update dots
    indicators.forEach((dot, i) => {
      dot.classList.remove("bg-blue-600");
      dot.classList.add("bg-gray-300");
      if (i === step - 1) dot.classList.add("bg-blue-600");
    });

    // Scroll into view only if not initial load
    if (!document.body.classList.contains("initial-load")) {
      steps[step - 1].scrollIntoView({ behavior: "smooth", block: "nearest", inline: "nearest" });
    }
  }

  function validateStep(step) {
    const inputs = steps[step - 1].querySelectorAll("input, select, textarea");
    let valid = true;

    for (let input of inputs) {
      if (input.hasAttribute("required") && !input.value.trim()) {
        input.classList.add("border-red-500");
        input.focus();

        // Shake animation
        steps[step - 1].classList.add("animate-shake");
        setTimeout(() => steps[step - 1].classList.remove("animate-shake"), 500);

        valid = false;
        break;
      } else {
        input.classList.remove("border-red-500");
      }
    }

    return valid;
  }

  function goNext() {
    if (currentStep < totalSteps && validateStep(currentStep)) {
      currentStep++;
      showStep(currentStep);
    }
  }

  function goBack() {
    if (currentStep > 1) {
      currentStep--;
      showStep(currentStep);
    }
  }

  // Button handlers
  document.querySelectorAll(".next-step").forEach(btn => btn.addEventListener("click", goNext));
  document.querySelectorAll(".prev-step").forEach(btn => btn.addEventListener("click", goBack));

  // Keyboard handling
  document.addEventListener("keydown", e => {
    const isMapModalOpen = mapModal && mapModal.style.display === "flex";

    if (isMapModalOpen && e.key === "Enter") {
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    if (!isMapModalOpen && e.key === "Enter") {
      e.preventDefault();
      goNext();
    }

    if (isMapModalOpen && e.key === "Escape") {
      e.preventDefault();
      mapModal.style.display = "none"; // close modal
    }
  });

  // Init
  document.body.classList.add("initial-load"); // mark first load
  showStep(currentStep);
  setTimeout(() => document.body.classList.remove("initial-load"), 600); // allow scroll after init
});
