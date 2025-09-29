const contactInput = document.querySelector("input[name='contact']");
const ageInput = document.querySelector("input[name='age']");

if (contactInput) {
  contactInput.addEventListener("input", () => {
    contactInput.setCustomValidity("");
    if (!/^[0-9]{10,15}$/.test(contactInput.value)) {
      contactInput.setCustomValidity("Phone must be 10-15 digits");
    }
  });
}

if (ageInput) {
  ageInput.addEventListener("input", () => {
    ageInput.setCustomValidity("");
    if (ageInput.value < 1 || ageInput.value > 120) {
      ageInput.setCustomValidity("Enter a valid age (1-120)");
    }
  });
}
