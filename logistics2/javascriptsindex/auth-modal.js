const modal = document.getElementById("authModal");
const heroSignInBtn = document.getElementById("heroSignInBtn");
const closeBtn = document.getElementById("closeAuthModal");
const signInForm = document.getElementById("signInForm");
const signUpForm = document.getElementById("signUpForm");
const goToSignUp = document.getElementById("goToSignUp");
const goToSignIn = document.getElementById("goToSignIn");

// NEW: Branding title + subtitle
const authTitle = document.getElementById("authTitle");
const authSubtitle = document.getElementById("authSubtitle");

if (heroSignInBtn) heroSignInBtn.addEventListener("click", () => modal.classList.remove("hidden"));
if (closeBtn) closeBtn.addEventListener("click", () => modal.classList.add("hidden"));

if (goToSignUp && goToSignIn) {
  goToSignUp.addEventListener("click", () => {
    signInForm.classList.add("hidden");
    signUpForm.classList.remove("hidden");

    // Update branding
    if (authTitle) authTitle.textContent = "Sign up";
    if (authSubtitle) authSubtitle.textContent = "Create your account";
  });

  goToSignIn.addEventListener("click", () => {
    signUpForm.classList.add("hidden");
    signInForm.classList.remove("hidden");

    // Update branding
    if (authTitle) authTitle.textContent = "Sign in";
    if (authSubtitle) authSubtitle.textContent = "Use your account";
  });
}

const becomeDriverBtn = document.getElementById("becomeDriverBtn");

if (becomeDriverBtn) {
  becomeDriverBtn.addEventListener("click", () => {
    // Show the Sign Up form by default
    signInForm.classList.add("hidden");
    signUpForm.classList.remove("hidden");

    // Update branding
    if (authTitle) authTitle.textContent = "Sign up";
    if (authSubtitle) authSubtitle.textContent = "Create your account";

    modal.classList.remove("hidden");
  });
}

// Close modal when clicking outside
modal.addEventListener("click", e => {
  if (e.target === modal) {
    modal.classList.add("hidden");
  }
});

// Close with ESC key
document.addEventListener("keydown", e => {
  if (e.key === "Escape" && !modal.classList.contains("hidden")) {
    modal.classList.add("hidden");
  }
});
