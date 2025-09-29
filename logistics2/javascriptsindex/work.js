// Animate cards in Our Work section
document.querySelectorAll("#work .bg-white").forEach(card => {
  card.addEventListener("mouseenter", () => {
    card.classList.add("scale-105", "shadow-xl", "transition", "duration-300");
  });
  card.addEventListener("mouseleave", () => {
    card.classList.remove("scale-105", "shadow-xl");
  });
});
