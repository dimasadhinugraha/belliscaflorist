<?php
include "config/db.php";

// Ambil 4 produk terbaru
$sql = "SELECT produk.*, kategori.nama_kategori 
        FROM produk 
        INNER JOIN kategori ON produk.kategori_id = kategori.id
        ORDER BY produk.id DESC 
        LIMIT 4";
$result = $conn->query($sql);

// Ambil kategori untuk filter
$kategori_query = $conn->query("SELECT DISTINCT nama_kategori FROM kategori ORDER BY nama_kategori ASC");
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bellisca Florist - Premium Flower Arrangements</title>
  
  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Text&family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            accent: '#cfa86e',
            'accent-dark': '#b98f56',
            muted: '#5d4b3b',
            'muted-light': '#8b7355',
            'bg-primary': '#fffaf5',
          },
          fontFamily: {
            serif: ['"DM Serif Text"', 'serif'],
            display: ['"Playfair Display"', 'serif'],
            sans: ['Poppins', 'sans-serif'],
          },
          maxWidth: { container: '1400px' }
        }
      }
    }
  </script>

  <style>
    body {
      font-size: 0.9rem;
      line-height: 1.5;
    }

    h1 { font-size: 2rem; line-height: 1.2; }
    h2 { font-size: 1.6rem; line-height: 1.2; }
    h3 { font-size: 1.3rem; line-height: 1.3; }
    p, a, li, input, button, textarea { font-size: 0.9rem; }

    section { padding-top: 3rem; padding-bottom: 3rem; }

    .hero-overlay {
      background: linear-gradient(135deg, rgba(93,75,59,0.7), rgba(207,168,110,0.5));
    }

    .navbar-scrolled { padding-top: 0.3rem; padding-bottom: 0.3rem; }

    /* Horizontal scroll carousel for categories on mobile */
    .category-carousel {
      display: flex;
      gap: 0.5rem;
      overflow-x: auto;
      scroll-behavior: smooth;
      padding-bottom: 0.5rem;
    }

    .category-carousel::-webkit-scrollbar {
      height: 6px;
    }

    .category-carousel::-webkit-scrollbar-track {
      background: transparent;
    }

    .category-carousel::-webkit-scrollbar-thumb {
      background: #cfa86e;
      border-radius: 3px;
    }

    .category-item {
      flex-shrink: 0;
    }

    /* Mobile menu animations */
    #mobile-menu {
      animation: slideDown 0.3s ease-out;
    }

    #mobile-menu.hidden {
      animation: slideUp 0.3s ease-in forwards;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideUp {
      from {
        opacity: 1;
        transform: translateY(0);
      }
      to {
        opacity: 0;
        transform: translateY(-10px);
      }
    }
  </style>
</head>

<body class="bg-gradient-to-br from-[#fff5eb] to-[#ffe8d6] text-muted font-sans overflow-x-hidden">

  <?php include 'includes/navbar.php'; ?>

  <!-- Hero -->
  <section class="relative h-[70vh] flex items-center justify-center overflow-hidden mt-14">
    <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/5a7ef425-0d82-4f43-9d7f-9a061da92d3a.png" class="absolute inset-0 w-full h-full object-cover">
    <div class="hero-overlay absolute inset-0"></div>
    <div class="relative z-10 text-center text-white px-6">
      <h1 class="font-serif text-4xl md:text-5xl font-bold mb-3 drop-shadow-lg">Beautiful Moments Start Here</h1>
      <p class="text-sm md:text-base mb-6 font-light drop-shadow-md max-w-2xl mx-auto">Discover our curated collection of premium flowers to celebrate life's special moments</p>
      <a href="allproduct.php" class="bg-white text-muted px-7 py-2.5 rounded-full text-sm font-semibold shadow hover:bg-accent hover:text-white transition">Explore Collection</a>
    </div>
  </section>

  <!-- Category Filter -->
  <section class="bg-white py-5 shadow-sm">
    <div class="max-w-container mx-auto px-6">
      <!-- Desktop: flex wrap centered -->
      <div class="hidden md:flex flex-wrap justify-center gap-2 text-sm">
        <a href="allproduct.php" class="px-4 py-2 rounded-full border border-accent bg-accent text-white hover:-translate-y-0.5 transition">All</a>
        <?php 
        if ($kategori_query && $kategori_query->num_rows > 0) {
          while ($kat = $kategori_query->fetch_assoc()) {
            echo '<a href="allproduct.php?kategori=' . urlencode($kat['nama_kategori']) . '" class="px-4 py-2 rounded-full border border-accent text-muted hover:bg-accent hover:text-white transition">' . htmlspecialchars($kat['nama_kategori']) . '</a>';
          }
        }
        ?>
      </div>
      <!-- Mobile: horizontal carousel -->
      <div class="md:hidden">
        <div class="category-carousel">
          <a href="allproduct.php" class="category-item px-4 py-2 rounded-full border border-accent bg-accent text-white text-sm whitespace-nowrap">All</a>
          <?php 
          if ($kategori_query && $kategori_query->num_rows > 0) {
            $kategori_query->data_seek(0);
            while ($kat = $kategori_query->fetch_assoc()) {
              echo '<a href="allproduct.php?kategori=' . urlencode($kat['nama_kategori']) . '" class="category-item px-4 py-2 rounded-full border border-accent text-muted hover:bg-accent hover:text-white transition text-sm whitespace-nowrap">' . htmlspecialchars($kat['nama_kategori']) . '</a>';
            }
          }
          ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Products -->
  <section class="py-12 bg-gradient-to-b from-white to-bg-primary">
    <div class="max-w-container mx-auto px-6">
      <div class="text-center mb-8">
        <h2 class="font-serif text-2xl font-semibold mb-1 text-muted">Featured Collection</h2>
        <p class="text-muted-light text-sm">Handpicked arrangements for every occasion</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <div class="text-center">
              <div class="relative w-32 h-32 bg-gray-100 overflow-hidden rounded-full mb-3 mx-auto">
                <img src="<?= htmlspecialchars($row['foto']) ?>" class="w-full h-full object-cover rounded-full hover:scale-105 transition-transform duration-300">
              </div>
              <div class="text-xs text-muted-light uppercase mb-1"><?= htmlspecialchars($row['nama_kategori']) ?></div>
              <h3 class="font-display text-base font-semibold mb-1"><?= htmlspecialchars($row['nama_produk']) ?></h3>
              <div class="text-accent font-bold mb-3">Rp <?= number_format($row['harga'], 0, ',', '.') ?></div>
              <a href="product-detail.php?id=<?= $row['id'] ?>" class="inline-block bg-accent text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-accent-dark transition">View Details</a>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-center text-muted-light py-8">No products available at the moment.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- About -->
  <section id="about" class="bg-accent/10">
    <div class="max-w-container mx-auto px-6 grid md:grid-cols-2 gap-10 items-center">
      <div class="rounded-2xl overflow-hidden shadow">
        <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/08c19845-7700-4638-91ff-56976eebf88e.png" class="w-full h-[380px] object-cover hover:scale-105 transition-transform">
      </div>
      <div>
        <h2 class="font-serif text-2xl font-semibold mb-3 text-muted">Crafted with Love & Passion</h2>
        <p class="text-sm text-muted-light mb-5 leading-relaxed">At Bellisca Florist, every bloom tells a story. Our expert florists handcraft each arrangement with attention to detail, using the freshest flowers.</p>
        <ul class="space-y-2 mb-5 text-sm text-muted">
          <li><i class="fas fa-check-circle text-accent mr-2"></i> Premium quality flowers</li>
          <li><i class="fas fa-check-circle text-accent mr-2"></i> Custom designs available</li>
          <li><i class="fas fa-check-circle text-accent mr-2"></i> Same-day delivery</li>
        </ul>
        <a href="allproduct.php" class="bg-accent text-white px-6 py-2 rounded-full text-sm font-semibold hover:bg-accent-dark transition">Discover More</a>
      </div>
    </div>
  </section>

  <!-- How to Order -->
  <section id="howto" class="py-12 bg-gradient-to-b from-white to-bg-primary">
    <div class="max-w-container mx-auto px-6 text-center">
      <h2 class="font-serif text-2xl font-semibold text-muted mb-6">How to Order</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <div class="bg-white p-3 md:p-10 rounded-xl shadow hover:-translate-y-1 transition">
          <i class="fas fa-search text-accent text-2xl md:text-5xl mb-2 md:mb-4"></i>
          <h4 class="font-semibold text-xs md:text-lg mb-1">Browse & Select</h4>
          <p class="text-muted-light text-xs md:text-sm">Explore our beautiful flower collections or request a custom design.</p>
        </div>
        <div class="bg-white p-3 md:p-10 rounded-xl shadow hover:-translate-y-1 transition">
          <i class="fas fa-palette text-accent text-2xl md:text-5xl mb-2 md:mb-4"></i>
          <h4 class="font-semibold text-xs md:text-lg mb-1">Customize</h4>
          <p class="text-muted-light text-xs md:text-sm">Choose your preferred style, color, or flower combination.</p>
        </div>
        <div class="bg-white p-3 md:p-10 rounded-xl shadow hover:-translate-y-1 transition">
          <i class="fab fa-whatsapp text-accent text-2xl md:text-5xl mb-2 md:mb-4"></i>
          <h4 class="font-semibold text-xs md:text-lg mb-1">Order via WhatsApp</h4>
          <p class="text-muted-light text-xs md:text-sm">Chat directly with our team for fast and easy ordering.</p>
        </div>
        <div class="bg-white p-3 md:p-10 rounded-xl shadow hover:-translate-y-1 transition">
          <i class="fas fa-truck text-accent text-2xl md:text-5xl mb-2 md:mb-4"></i>
          <h4 class="font-semibold text-xs md:text-lg mb-1">Fast Delivery</h4>
          <p class="text-muted-light text-xs md:text-sm">We deliver fresh flowers right to your doorstep safely.</p>
        </div>
      </div>
      <div class="mt-6">
        <a href="https://wa.me/6289524810752?text=Hello%20Bellisca!%20I%27d%20like%20to%20order%20flowers" 
           target="_blank" class="inline-block bg-green-500 text-white px-8 py-3 rounded-full text-sm font-semibold hover:bg-green-600 transition">
          <i class="fab fa-whatsapp mr-2"></i>Order via WhatsApp
        </a>
      </div>
    </div>
  </section>

  <!-- Contact -->
  <section id="contact" class="py-12 bg-white">
    <div class="max-w-container mx-auto px-6">
      <div class="text-center mb-8">
        <h2 class="font-serif text-2xl font-semibold mb-1 text-muted">Get in Touch</h2>
        <p class="text-muted-light text-sm">We'd love to hear from you!</p>
      </div>
      <div class="grid md:grid-cols-2 gap-8">
        <form class="bg-bg-primary p-6 rounded-xl shadow">
          <input type="text" placeholder="Full Name" class="w-full mb-3 px-4 py-2 border border-gray-300 rounded-md focus:border-accent outline-none text-sm">
          <input type="email" placeholder="Email Address" class="w-full mb-3 px-4 py-2 border border-gray-300 rounded-md focus:border-accent outline-none text-sm">
          <textarea placeholder="Your Message" class="w-full mb-3 px-4 py-2 border border-gray-300 rounded-md focus:border-accent outline-none text-sm"></textarea>
          <button type="submit" class="w-full bg-accent text-white py-2 rounded-md text-sm font-semibold hover:bg-accent-dark transition">Send Message</button>
        </form>
        <div class="space-y-4">
          <div class="flex items-center gap-4 bg-bg-primary p-5 rounded-xl shadow">
            <i class="fas fa-map-marker-alt text-accent text-2xl"></i>
            <p>Rawa Belong, Jakarta Barat, Indonesia</p>
          </div>
          <div class="flex items-center gap-4 bg-bg-primary p-5 rounded-xl shadow">
            <i class="fas fa-phone text-accent text-2xl"></i>
            <p>+628 9524810752<br><span class="text-sm text-muted-light">Mon-Sat: 9AM - 6PM</span></p>
          </div>
          <div class="flex items-center gap-4 bg-bg-primary p-5 rounded-xl shadow">
            <i class="fas fa-envelope text-accent text-2xl"></i>
            <p>belliscaflorist@gmail.com</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

  <script>
    window.addEventListener('scroll', () => {
      const nav = document.getElementById('navbar');
      if (window.scrollY > 30) nav.classList.add('navbar-scrolled', 'shadow-lg');
      else nav.classList.remove('navbar-scrolled', 'shadow-lg');
    });
    document.getElementById('mobile-menu-btn').addEventListener('click', () => {
      document.getElementById('mobile-menu').classList.toggle('hidden');
    });
  </script>
</body>
</html>
