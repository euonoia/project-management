<?php
session_start();
include('../database/connect.php');

$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project Management Landing Page</title>
  <link href="../public/css/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-neutral-50 text-gray-900">

  <!-- Header -->
  <header class="bg-black text-white">
    <div class="container mx-auto flex justify-between items-center px-6 py-5">
      <h1 class="text-3xl font-extrabold tracking-wide">TNVS</h1>
      <nav class="flex gap-8 text-sm uppercase">
        <a href="#work" class="hover:text-gray-300">Work</a>
        <a href="#about" class="hover:text-gray-300">About</a>
        <a href="#contact" class="hover:text-gray-300">Contact</a>
      </nav>
      <?php if ($is_logged_in): ?>
  <a href="logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded text-sm font-medium">
    Logout
  </a>
<?php endif; ?>

    </div>
  </header>

  <!-- Hero -->
  <section class="bg-gradient-to-b from-gray-100 to-gray-200 py-20">
    <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-2 items-center gap-12">

      <!-- Left content (headline + description) -->
      <div class="text-center md:text-left">
        <h2 class="text-5xl md:text-6xl font-extrabold mb-6">
          Drive
        </h2>
        <p class="max-w-xl text-lg text-gray-600 mb-6">
          A modern project management system built to connect drivers and users seamlessly.
        </p>
        <a href="fleetvehiclemanagement/index.php" 
           class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded text-white font-medium mt-4 inline-block">
          Become a Driver
        </a>
      </div>

      <!-- Right content (conditional buttons) -->
      <div class="text-center md:text-right">
        <?php if ($is_logged_in): ?>
          <div class="flex flex-col sm:flex-row justify-center md:justify-end gap-4">
            <a href="reservation/reserve.php" 
              class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded text-white font-medium">
              Make a Reservation
            </a>
          </div>
        <?php else: ?>
        <div class="flex justify-center md:justify-end">
          <button id="heroSignInBtn" 
            class="px-6 py-3 bg-orange-600 hover:bg-orange-500 rounded text-white font-medium">
            Sign In to Continue
          </button>
        </div>
      <?php endif; ?>

      </div>

    </div>
  </section>

  <!-- Work -->
  <section id="work" class="py-16">
    <div class="container mx-auto px-6">
      <h3 class="text-3xl font-bold mb-8">Our Work</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-white rounded shadow hover:shadow-lg transition">
          <h4 class="font-semibold mb-2">🚚 Logistics Flow</h4>
          <p class="text-gray-600">Streamlined driver and fleet management.</p>
        </div>
        <div class="p-6 bg-white rounded shadow hover:shadow-lg transition">
          <h4 class="font-semibold mb-2">📊 Project 1</h4>
          <p class="text-gray-600">A demo showcasing portal features.</p>
        </div>
        <div class="p-6 bg-white rounded shadow hover:shadow-lg transition">
          <h4 class="font-semibold mb-2">🗂 Reservation System</h4>
          <p class="text-gray-600">Effortless bookings and scheduling.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- About -->
  <section id="about" class="py-16 bg-neutral-100">
    <div class="container mx-auto px-6 text-center">
      <h3 class="text-3xl font-bold mb-6">About Us</h3>
      <p class="max-w-3xl mx-auto text-gray-700">
        We are your modern logistics and project management partner. Whether you're a user looking to book 
        or a driver wanting opportunities, this system bridges the gap.
      </p>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact" class="bg-black text-white py-8">
    <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
      <p class="text-sm">© 2025 Project Management System. All Rights Reserved.</p>
      <div class="flex gap-6 text-sm">
        <a href="#" class="hover:text-gray-400">WhatsApp</a>
        <a href="#" class="hover:text-gray-400">Instagram</a>
      </div>
    </div>
  </footer>

  <!-- Auth Modal -->
<div id="authModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 px-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl flex flex-row overflow-hidden">

    <!-- Left side (Branding / Title) -->
    <div class="flex flex-col justify-center items-center bg-black text-white min-w-[220px] p-6">
      <div class="flex items-center mb-6">
        <i class="fab fa-google text-3xl text-blue-500"></i>
      </div>
      <h2 class="text-2xl font-semibold mb-2">Sign in</h2>
      <p class="text-sm text-gray-300">Use your account</p>
    </div>

    <!-- Right side (Form) -->
    <div class="flex-1 p-8 relative bg-white">
      <!-- Close Button -->
      <button id="closeAuthModal" class="absolute top-5 right-8 text-gray-400 hover:text-black">
        <i class="fas fa-times text-lg"></i>
      </button>

      <!-- Sign In Form -->
      <div id="signInForm" class="flex flex-col gap-4">
        <form method="post" action="connections/auth/bawalpumasok.php" class="flex flex-col gap-4">
          <input type="email" name="email" placeholder="Email or phone" required
                 class="w-full px-4 py-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
          <input type="password" name="password" placeholder="Password" required
                 class="w-full px-4 py-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">

          <a href="#" class="text-sm text-blue-600 hover:underline">Forgot email?</a>

          <!-- Buttons row -->
          <div class="flex justify-between items-center">
            <button type="button" id="goToSignUp" class="text-sm text-gray-700 font-medium hover:underline">
              Create account
            </button>
            <input type="submit" name="signIn" value="Next"
                   class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-500 transition cursor-pointer">
          </div>
        </form>
      </div>

      <!-- Sign Up Form -->
      <div id="signUpForm" class="hidden flex flex-col gap-4">
        <form method="post" action="connections/auth/bawalpumasok.php" class="flex flex-col gap-4">
          <input type="text" name="firstname" placeholder="First Name" required
                 class="w-full px-4 py-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400">
          <input type="text" name="lastname" placeholder="Last Name" required
                 class="w-full px-4 py-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400">
          <input type="email" name="email" placeholder="Email" required
                 class="w-full px-4 py-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400">
          <input type="password" name="password" placeholder="Password" required
                 class="w-full px-4 py-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400">
          <input type="hidden" name="role" value="user">

          <!-- Buttons row -->
          <div class="flex justify-between items-center">
            <button type="button" id="goToSignIn" class="text-sm text-gray-700 font-medium hover:underline">
              Already have an account? Sign in
            </button>
            <input type="submit" name="signUp" value="Sign Up"
                   class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-500 transition cursor-pointer">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

  <!-- JS for modal -->
  <script>
   const modal = document.getElementById("authModal");

// Header Sign In button (may not exist)
const openBtn = document.getElementById("openAuthModal");
if (openBtn) {
  openBtn.addEventListener("click", () => modal.classList.remove("hidden"));
}

// Hero Sign In button
const heroSignInBtn = document.getElementById("heroSignInBtn");
if (heroSignInBtn) {
  heroSignInBtn.addEventListener("click", () => modal.classList.remove("hidden"));
}

// Close button
const closeBtn = document.getElementById("closeAuthModal");
if (closeBtn) {
  closeBtn.addEventListener("click", () => modal.classList.add("hidden"));
}

// Switch between Sign In / Sign Up forms
const signInForm = document.getElementById("signInForm");
const signUpForm = document.getElementById("signUpForm");
const goToSignUp = document.getElementById("goToSignUp");
const goToSignIn = document.getElementById("goToSignIn");

if (goToSignUp && goToSignIn) {
  goToSignUp.addEventListener("click", () => {
    signInForm.classList.add("hidden");
    signUpForm.classList.remove("hidden");
  });
  goToSignIn.addEventListener("click", () => {
    signUpForm.classList.add("hidden");
    signInForm.classList.remove("hidden");
  });
}

  

  </script>

</body>
</html>
