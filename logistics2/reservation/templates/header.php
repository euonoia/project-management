<header class="bg-black text-white">
  <div class="container mx-auto flex justify-between items-center px-6 py-5">
    <h1 class="text-3xl font-extrabold tracking-wide">Drive</h1>
    <nav class="flex items-center gap-8 text-sm uppercase">
      <a href="#progress" class="hover:text-gray-300">Progress</a>
      <?php if ($is_logged_in): ?>
        <a href="../logout.php" 
           class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded text-sm font-medium uppercase">
           Logout
        </a>
      <?php endif; ?>
    </nav>
  </div>
</header>
