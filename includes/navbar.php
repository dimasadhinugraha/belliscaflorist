<!-- Navbar -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md shadow-md transition-all duration-300">
  <div class="max-w-container mx-auto px-6 lg:px-10">
    <div class="flex items-center justify-between h-14">
      <a href="index.php" class="font-serif text-2xl text-muted hover:text-accent transition">Bellisca Florist</a>
      <div class="hidden md:flex items-center gap-5 text-[0.9rem]">
        <?php 
          $current_page = basename($_SERVER['PHP_SELF']);
          $is_home = ($current_page == 'index.php') ? true : false;
          $is_products = ($current_page == 'allproduct.php' || $current_page == 'product-detail.php') ? true : false;
        ?>
        <a href="index.php" class="<?= $is_home ? 'text-accent font-medium bg-accent/15' : 'text-muted hover:text-accent hover:bg-accent/15' ?> px-3 py-1.5 rounded-md transition">Home</a>
        <a href="allproduct.php" class="<?= $is_products ? 'text-accent font-medium bg-accent/15' : 'text-muted hover:text-accent hover:bg-accent/15' ?> px-3 py-1.5 rounded-md transition">Products</a>
        <a href="index.php#about" class="text-muted hover:text-accent hover:bg-accent/15 px-3 py-1.5 rounded-md transition">About</a>
        <a href="index.php#howto" class="text-muted hover:text-accent hover:bg-accent/15 px-3 py-1.5 rounded-md transition">How to Order</a>
        <a href="index.php#contact" class="text-muted hover:text-accent hover:bg-accent/15 px-3 py-1.5 rounded-md transition">Contact</a>
      </div>
      <button id="mobile-menu-btn" class="md:hidden text-muted text-xl"><i class="fas fa-bars"></i></button>
    </div>
    <div id="mobile-menu" class="hidden md:hidden pb-3">
      <div class="flex flex-col gap-1.5 text-[0.9rem]">
        <?php 
          $current_page = basename($_SERVER['PHP_SELF']);
          $is_home = ($current_page == 'index.php') ? true : false;
          $is_products = ($current_page == 'allproduct.php' || $current_page == 'product-detail.php') ? true : false;
        ?>
        <a href="index.php" class="<?= $is_home ? 'text-accent bg-accent/15 font-medium' : 'text-muted hover:text-accent hover:bg-accent/15' ?> px-4 py-2 rounded-md transition">Home</a>
        <a href="allproduct.php" class="<?= $is_products ? 'text-accent bg-accent/15 font-medium' : 'text-muted hover:text-accent hover:bg-accent/15' ?> px-4 py-2 rounded-md transition">Products</a>
        <a href="#about" class="text-muted hover:text-accent hover:bg-accent/15 px-4 py-2 rounded-md transition">About</a>
        <a href="#howto" class="text-muted hover:text-accent hover:bg-accent/15 px-4 py-2 rounded-md transition">How to Order</a>
        <a href="#contact" class="text-muted hover:text-accent hover:bg-accent/15 px-4 py-2 rounded-md transition">Contact</a>
      </div>
    </div>
  </div>
</nav>
