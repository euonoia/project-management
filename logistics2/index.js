// Professional scroll animations for "Our Work" cards and section

document.addEventListener("DOMContentLoaded", function () {
  // Animate section title
  const workSection = document.getElementById("work");
  const workTitle = workSection.querySelector("h3");
  workTitle.classList.add("opacity-0", "translate-x-[-40px]", "transition-all", "duration-700");

  // Animate cards
  const cards = workSection.querySelectorAll(".bg-white.rounded-xl.shadow-md");
  cards.forEach(card => {
    card.classList.add("opacity-0", "translate-y-8", "transition-all", "duration-700");
  });

  // Intersection Observer for fade-in effect
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        if (entry.target === workTitle) {
          workTitle.classList.remove("opacity-0", "translate-x-[-40px]");
          workTitle.classList.add("opacity-100", "translate-x-0");
        } else {
          entry.target.classList.remove("opacity-0", "translate-y-8");
          entry.target.classList.add("opacity-100", "translate-y-0");
        }
      }
    });
  }, { threshold: 0.2 });

  observer.observe(workTitle);
  cards.forEach(card => observer.observe(card));

  // Optional: highlight nav when section is in view
  const navLinks = document.querySelectorAll("nav a[href='#work']");
  const highlightNav = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      navLinks.forEach(link => {
        if (entry.isIntersecting) {
          link.classList.add("text-blue-600", "font-bold");
        } else {
          link.classList.remove("text-blue-600", "font-bold");
        }
      });
    });
  }, { threshold: 0.5 });
  highlightNav.observe(workSection);
});