const header = document.getElementById("mainHeader");

// Sticky effect
window.addEventListener("scroll", () => {
  if (window.scrollY > 50) {
    header.classList.add("shadow-lg", "bg-black/90", "backdrop-blur-md", "py-3");
    header.classList.remove("py-5");
  } else {
    header.classList.remove("shadow-lg", "bg-black/90", "backdrop-blur-md", "py-3");
    header.classList.add("py-5");
  }
});

// Smooth scroll with offset for sticky header
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();

    const targetId = this.getAttribute("href").substring(1);
    const target = document.getElementById(targetId);

    if (target) {
      const headerHeight = header.offsetHeight; // dynamically detect height
      const elementPosition = target.offsetTop - headerHeight;

      window.scrollTo({
        top: elementPosition,
        behavior: "smooth"
      });
    }
  });
});
