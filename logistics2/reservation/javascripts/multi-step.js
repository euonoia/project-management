document.addEventListener("DOMContentLoaded", () => {
  let currentStep = 1;

  const steps = Array.from(document.querySelectorAll(".form-step"));
  const dots = Array.from(document.querySelectorAll(".step-indicator"));
  const mapModal = document.getElementById("mapModal");

  // --- Show a specific step with smooth horizontal slide ---
  function showStep(step) {
    steps.forEach((el, idx) => {
      if (idx === step - 1) {
        el.style.display = "block";
        el.style.zIndex = "2"; // active step on top
        el.style.opacity = "0";
        el.style.transform = "translateX(50px)";
        requestAnimationFrame(() => {
          el.style.transition = "all 0.5s cubic-bezier(.4,0,.2,1)";
          el.style.opacity = "1";
          el.style.transform = "translateX(0)";
          el.style.pointerEvents = "auto";
        });
      } else {
        el.style.transition = "all 0.5s cubic-bezier(.4,0,.2,1)";
        el.style.opacity = "0";
        el.style.transform = "translateX(50px)";
        el.style.pointerEvents = "none";
        el.style.zIndex = "1";
        setTimeout(() => {
          if (idx !== step - 1) el.style.display = "none";
        }, 500);
      }
    });

    // Update dots (active vs inactive)
    dots.forEach((dot, idx) => {
      if (idx === step - 1) {
        dot.style.backgroundColor = "#dd6b20";
        dot.style.borderColor = "#dd6b20";
      } else {
        dot.style.backgroundColor = "#d1d5db";
        dot.style.borderColor = "#9ca3af";
      }
    });

    currentStep = step;

    if (!document.body.classList.contains("initial-load")) {
      steps[step - 1].scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
  }

  // --- Validate current step ---
  function validateStep(step) {
    const inputs = steps[step - 1].querySelectorAll("input, select, textarea");
    let valid = true;

    for (let input of inputs) {
      if (input.hasAttribute("required") && !input.value.trim()) {
        input.classList.add("border-red-500");
        input.focus();

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

  // --- Dot navigation ---
  dots.forEach((dot, idx) => {
    dot.style.cursor = "pointer"; // show clickable cursor
    dot.addEventListener("click", () => {
      // Allow backward always, forward only if validation passes
      if (idx + 1 <= currentStep || validateStep(currentStep)) {
        showStep(idx + 1);
      }
    });
  });

  // --- Keyboard handling (Enter = Next, Esc = close modal) ---
  document.addEventListener("keydown", e => {
    const isMapModalOpen = mapModal && mapModal.style.display === "flex";

    if (isMapModalOpen && e.key === "Enter") {
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    if (!isMapModalOpen && e.key === "Enter") {
      e.preventDefault();
      if (currentStep < steps.length && validateStep(currentStep)) {
        showStep(currentStep + 1);
      }
    }

    if (isMapModalOpen && e.key === "Escape") {
      e.preventDefault();
      mapModal.style.display = "none";
    }
  });

  // --- Initial setup ---
  steps.forEach(el => {
    el.style.transition = "all 0.5s cubic-bezier(.4,0,.2,1)";
    el.style.opacity = "0";
    el.style.transform = "translateX(50px)";
    el.style.display = "none";

    // 🔑 Keep steps stacked in same card
    el.style.position = "absolute";
    el.style.top = "0";
    el.style.left = "0";
    el.style.width = "100%";
  });

  // make sure parent container is relative so absolute steps stay inside
  if (steps[0] && steps[0].parentElement) {
    steps[0].parentElement.style.position = "relative";
    steps[0].parentElement.style.minHeight = "120px"; // adjust for content
  }

  document.body.classList.add("initial-load");
  showStep(currentStep);
  setTimeout(() => document.body.classList.remove("initial-load"), 600);
});
