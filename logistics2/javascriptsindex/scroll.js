// Smooth scrolling with header offset
const header = document.querySelector("#mainHeader");
const headerHeight = header.offsetHeight;

document.querySelectorAll("header a").forEach(link => {
  link.addEventListener("click", e => {
    const href = link.getAttribute("href");

    // only handle same-page anchors (start with "#")
    if (href && href.startsWith("#")) {
      e.preventDefault();
      const target = document.querySelector(href);

      if (target) {
        const elementPosition = target.offsetTop;
        const offsetPosition = elementPosition - headerHeight;

        window.scrollTo({
          top: offsetPosition,
          behavior: "smooth"
        });
      }
    }
  });
});
