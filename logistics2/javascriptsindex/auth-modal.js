const modal = document.getElementById("authModal");
const modalContent = document.getElementById("authContent");
const heroSignInBtn = document.getElementById("heroSignInBtn");
const closeBtn = document.getElementById("closeAuthModal");
const signInForm = document.getElementById("signInForm");
const signUpForm = document.getElementById("signUpForm");
const goToSignUp = document.getElementById("goToSignUp");
const goToSignIn = document.getElementById("goToSignIn");
const authTitle = document.getElementById("authTitle");
const authSubtitle = document.getElementById("authSubtitle");
const becomeDriverBtn = document.getElementById("becomeDriverBtn");

// --- Helpers ---
function setAuthView(isSignup) {
  if (isSignup) {
    signInForm.classList.add("hidden");
    signUpForm.classList.remove("hidden");
    authTitle.textContent = "Sign up";
    authSubtitle.textContent = "Create your account";
  } else {
    signUpForm.classList.add("hidden");
    signInForm.classList.remove("hidden");
    authTitle.textContent = "Sign in";
    authSubtitle.textContent = "Use your account";
  }
}

// --- Open Modal ---
function openModal(defaultToSignup = false) {
  // Show overlay first
  modal.classList.remove("hidden");
  modal.classList.add("flex");

  // Lock background scroll
  document.body.style.overflow = "hidden";

  setAuthView(defaultToSignup);

  // Start hidden state
  modal.classList.remove("opacity-100");
  modal.classList.add("opacity-0");
  modalContent.classList.remove("opacity-100", "scale-100", "translate-y-0");
  modalContent.classList.add("opacity-0", "scale-95", "translate-y-6");

  // Animate with slight delay for smoothness
  requestAnimationFrame(() => {
    modal.classList.remove("opacity-0");
    modal.classList.add("opacity-100");

    setTimeout(() => {
      modalContent.classList.remove("opacity-0", "scale-95", "translate-y-6");
      modalContent.classList.add("opacity-100", "scale-100", "translate-y-0");
    }, 50); // delay makes it feel natural
  });
}


// --- Close Modal ---
function closeModal() {
  // Animate content out
  modalContent.classList.remove("opacity-100", "scale-100", "translate-y-0");
  modalContent.classList.add("opacity-0", "scale-95", "translate-y-6");

  // Animate overlay out
  modal.classList.remove("opacity-100");
  modal.classList.add("opacity-0");

  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    document.body.style.overflow = "";
  }, 200); // smoother & faster (matches Tailwind transition-200)
}

// --- Event Listeners ---
heroSignInBtn?.addEventListener("click", () => openModal(false));
becomeDriverBtn?.addEventListener("click", () => openModal(true));
closeBtn?.addEventListener("click", closeModal);

goToSignUp?.addEventListener("click", () => setAuthView(true));
goToSignIn?.addEventListener("click", () => setAuthView(false));

modal.addEventListener("click", e => { if (e.target === modal) closeModal(); });
document.addEventListener("keydown", e => { if (e.key === "Escape" && !modal.classList.contains("hidden")) closeModal(); });
