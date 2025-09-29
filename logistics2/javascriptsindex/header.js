const header = document.querySelector("header");

window.addEventListener("scroll", () => {
  if (window.scrollY > 50) {
    header.classList.add("shadow-lg", "bg-black/95");
  } else {
    header.classList.remove("shadow-lg", "bg-black/95");
  }
});
