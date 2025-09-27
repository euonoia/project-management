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
      <h1 class="text-3xl font-extrabold tracking-wide">Drive</h1>
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
    Logistics 2
  </h2>
  <p class="max-w-xl text-lg text-gray-600 mb-6">
     A modern fleet and transportation management platform.  
  <br> It seamlessly connects drivers and users while powering vehicle reservations,  
  dispatching, trip performance monitoring, <br>and cost optimization all in one system.
  </p>

  <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
      <?php if ($is_logged_in): ?>
    <!-- Logged-in: direct link -->
    <a href="fleetvehiclemanagement/index.php" 
       class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded text-white font-medium">
      Become a Driver
    </a>
  <?php else: ?>
    <!-- Not logged-in: open auth modal -->
    
  <?php endif; ?>

    <!-- Make a Reservation Button (only if logged in) -->
    <?php if ($is_logged_in): ?>
      <a href="reservation/reserve.php" 
         class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded text-white font-medium">
        Make a Reservation
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!$is_logged_in): ?>
<!-- Right content (Sign In button only if not logged in) -->
<div class="text-center md:text-right mt-6 md:mt-0">
  <div class="flex justify-center md:justify-end">
    <button id="heroSignInBtn" 
      class="px-6 py-3 bg-orange-600 hover:bg-orange-500 rounded text-white font-medium">
      Sign In to Continue
    </button>
  </div>
</div>
<?php endif; ?>


      </div>

    </div>
  </section>


<!-- Balanced "Our Work" section: left-aligned title, pyramid layout, consistent card size, better spacing -->
<section id="work" class="py-16 bg-gradient-to-br from-gray-50 to-gray-100">
  <div class="container mx-auto px-4">
    <h3 class="text-3xl md:text-4xl font-extrabold mb-8 text-left text-gray-900 tracking-tight">
      Our Work
    </h3>
    <!-- Pyramid layout -->
<div class="flex flex-col items-start gap-6">
  <!-- Top row: 1 card -->
  <div class="w-full max-w-2xl mx-auto">
    <div class="bg-white px-6 py-5 rounded-xl shadow-md border border-gray-100 flex items-start gap-3 h-full">
      <span class="inline-block bg-blue-100 text-blue-600 rounded-full p-3 shrink-0">
        <i class="fa-solid fa-car-side text-lg"></i>
      </span>
      <div>
        <h4 class="text-lg font-semibold text-gray-800 mb-1">Fleet & Vehicle Management</h4>
        <p class="text-gray-600 text-sm">
          Organize and oversee your vehicles with ease, ensuring smooth operations and efficient utilization.
        </p>
      </div>
    </div>
  </div>

  <!-- Middle row: 2 cards -->
<div class="flex flex-col md:flex-row gap-6 w-full max-w-3xl mx-auto items-stretch">
  <div class="flex-1 flex">
    <div class="bg-white px-6 py-5 rounded-xl shadow-md border border-gray-100 flex items-start gap-3 h-full w-full">
      <span class="inline-block bg-green-100 text-green-600 rounded-full p-3 shrink-0">
        <i class="fa-solid fa-calendar-check text-lg"></i>
      </span>
      <div>
        <h4 class="text-lg font-semibold text-gray-800 mb-1">Vehicle Reservation & Dispatch</h4>
        <p class="text-gray-600 text-sm">
          Simplify bookings, dispatch scheduling, and real-time trip coordination for hassle-free transport.
        </p>
      </div>
    </div>
  </div>
  <div class="flex-1 flex">
    <div class="bg-white px-6 py-5 rounded-xl shadow-md border border-gray-100 flex items-start gap-3 h-full w-full">
      <span class="inline-block bg-orange-100 text-orange-600 rounded-full p-3 shrink-0">
        <i class="fa-solid fa-chart-line text-lg"></i>
      </span>
      <div>
        <h4 class="text-lg font-semibold text-gray-800 mb-1">Cost Analysis & Optimization</h4>
        <p class="text-gray-600 text-sm">
          Monitor expenses, optimize routes, and reduce operational costs with smart analytics.
        </p>
      </div>
    </div>
  </div>
</div>


  <!-- Bottom row: 3 cards -->
  <div class="flex flex-col md:flex-row gap-6 w-full max-w-4xl mx-auto items-stretch">
    <div class="flex-1">
      <div class="bg-white px-6 py-5 rounded-xl shadow-md border border-gray-100 flex items-start gap-3 h-full">
        <span class="inline-block bg-purple-100 text-purple-600 rounded-full p-3 shrink-0">
          <i class="fa-solid fa-gauge-high text-lg"></i>
        </span>
        <div>
          <h4 class="text-lg font-semibold text-gray-800 mb-1">Driver & Trip Performance</h4>
          <p class="text-gray-600 text-sm">
            Track driver performance and trip progress with actionable insights for accountability and safety.
          </p>
        </div>
      </div>
    </div>
    <div class="flex-1">
      <div class="bg-white px-6 py-5 rounded-xl shadow-md border border-gray-100 flex items-start gap-3 h-full">
        <span class="inline-block bg-cyan-100 text-cyan-600 rounded-full p-3 shrink-0">
          <i class="fa-solid fa-mobile-screen-button text-lg"></i>
        </span>
        <div>
          <h4 class="text-lg font-semibold text-gray-800 mb-1">Mobile Fleet Command</h4>
          <p class="text-gray-600 text-sm">
            Manage and monitor fleet operations anytime, anywhere with our mobile-first command app.
          </p>
        </div>
      </div>
    </div>
    <div class="flex-1">
      <div class="bg-white px-6 py-5 rounded-xl shadow-md border border-gray-100 flex items-start gap-3 h-full">
        <span class="inline-block bg-pink-100 text-pink-600 rounded-full p-3 shrink-0">
          <i class="fa-solid fa-shield-halved text-lg"></i>
        </span>
        <div>
          <h4 class="text-lg font-semibold text-gray-800 mb-1">Safety & Compliance</h4>
          <p class="text-gray-600 text-sm">
            Ensure all trips and drivers meet safety standards and compliance requirements for peace of mind.
          </p>
        </div>
      </div>
    </div>
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

          <div id="authModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 px-4">
  <!-- Modal container -->
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl flex flex-col md:flex-row overflow-hidden relative">

    <!-- Left side (Branding / Title) -->
    <div class="w-full md:w-3/5 flex flex-col justify-center items-center bg-black text-white p-8">
      <div class="flex items-center mb-6">
        <i class="fab fa-google text-4xl text-blue-500"></i>
      </div>
      <h2 class="text-2xl font-semibold mb-2">Sign in</h2>
      <p class="text-sm text-gray-300">Use your account</p>
    </div>

    <!-- Right side (Form) -->
    <div class="w-full md:w-2/5 p-8 flex flex-col justify-center relative">

      <!-- Close Button Wrapper with justify-between -->
      <div class="flex justify-between items-center mb-4">
        <!-- Optional left space (empty div) -->
        <div></div>
        <!-- Close button -->
        <button id="closeAuthModal" 
                class="text-gray-500 hover:text-black text-2xl font-bold">
          &times;
        </button>
      </div>

      <!-- Sign In Form -->
      <div id="signInForm" class="flex flex-col gap-4">
        <form method="post" action="connections/auth/bawalpumasok.php" class="flex flex-col gap-4">
          <input type="email" name="email" placeholder="Email or phone" required
                 class="w-full px-4 py-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
          <input type="password" name="password" placeholder="Password" required
                 class="w-full px-4 py-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">

          <a href="#" class="text-sm text-blue-600 hover:underline">Forgot email?</a>

          <div class="flex justify-between items-center">
            <button type="button" id="goToSignUp" class="text-sm text-gray-700 font-medium hover:underline">
              Create account
            </button>
            <input type="submit" name="signIn" value="Login"
                   class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-500 transition cursor-pointer">
          </div>
        </form>
      </div>

      <!-- Sign Up Form -->
      <div id="signUpForm" class="hidden flex flex-col gap-4 mt-4">
        <form method="post" action="connections/auth/bawalpumasok.php" class="flex flex-col gap-4">

          <!-- First & Last Name -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col">
              <label class="text-sm text-gray-600 mb-1">First Name</label>
              <input type="text" name="firstname" required
                     class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
            <div class="flex flex-col">
              <label class="text-sm text-gray-600 mb-1">Last Name</label>
              <input type="text" name="lastname" required
                     class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
          </div>

          <!-- Email & Password -->
          <div class="flex flex-col">
            <label class="text-sm text-gray-600 mb-1">Email</label>
            <input type="email" name="email" required
                   class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400">
          </div>

          <div class="flex flex-col">
            <label class="text-sm text-gray-600 mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400">
          </div>

          <!-- Age, Gender, Contact -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex flex-col">
              <label class="text-sm text-gray-600 mb-1">Age</label>
              <input type="number" name="age" min="1" max="120" required
                     class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
            <div class="flex flex-col">
              <label class="text-sm text-gray-600 mb-1">Gender</label>
              <select name="gender" required
                      class="w-full px-3 py-2 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-green-400">
                <option value="" disabled selected>Select</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="flex flex-col">
              <label class="text-sm text-gray-600 mb-1">Contact</label>
              <input type="tel" name="contact" required pattern="[0-9]{10,15}"
                     class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400"
                     title="Enter a valid phone number (10-15 digits)">
            </div>
          </div>

          <input type="hidden" name="role" value="user">

          <input type="submit" name="signUp" value="Sign Up"
                 class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-500 transition cursor-pointer">

        </form>
        <button type="button" id="goToSignIn" class="text-sm text-gray-700 font-medium hover:underline mt-2 self-end">
          Already have an account? Sign in
        </button>
      </div>

    </div>
  </div>
</div>

<script src="index.js" defer></script>
<!-- JS for modal -->
<script>
  const modal = document.getElementById("authModal");
  const heroSignInBtn = document.getElementById("heroSignInBtn");
  const closeBtn = document.getElementById("closeAuthModal");
  const signInForm = document.getElementById("signInForm");
  const signUpForm = document.getElementById("signUpForm");
  const goToSignUp = document.getElementById("goToSignUp");
  const goToSignIn = document.getElementById("goToSignIn");

  if (heroSignInBtn) heroSignInBtn.addEventListener("click", () => modal.classList.remove("hidden"));
  if (closeBtn) closeBtn.addEventListener("click", () => modal.classList.add("hidden"));

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

const becomeDriverBtn = document.getElementById("becomeDriverBtn");

if (becomeDriverBtn) {
  becomeDriverBtn.addEventListener("click", () => {
    // Show the Sign Up form by default
    document.getElementById("signInForm").classList.add("hidden");
    document.getElementById("signUpForm").classList.remove("hidden");
    modal.classList.remove("hidden");
  });
}

</script>

</body>
</html>
