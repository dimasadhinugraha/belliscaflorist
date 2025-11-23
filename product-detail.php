<?php
include "config/db.php";

if (!isset($_GET['id'])) {
  header("Location: allproduct.php");
  exit();
}

$id = intval($_GET['id']);
$sql = "SELECT produk.*, kategori.nama_kategori 
        FROM produk 
        INNER JOIN kategori ON produk.kategori_id = kategori.id
        WHERE produk.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "<script>alert('Produk tidak ditemukan!'); window.location='allproduct.php';</script>";
  exit();
}

$produk = $result->fetch_assoc();
$harga = "Rp " . number_format($produk['harga'], 0, ',', '.');

// Get related products from same category
$related_sql = "SELECT produk.*, kategori.nama_kategori 
                FROM produk 
                INNER JOIN kategori ON produk.kategori_id = kategori.id
                WHERE produk.kategori_id = ? AND produk.id != ?
                ORDER BY RAND()
                LIMIT 4";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("ii", $produk['kategori_id'], $id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($produk['nama_produk']) ?> - Bellisca Florist</title>

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

    section { padding-top: 2rem; padding-bottom: 2rem; }

    .hero-overlay {
      background: linear-gradient(135deg, rgba(93,75,59,0.7), rgba(207,168,110,0.5));
    }

    .navbar-scrolled { padding-top: 0.3rem; padding-bottom: 0.3rem; }

    .product-image {
      transition: transform 0.5s ease;
    }

    .product-image:hover {
      transform: scale(1.05);
    }

    .product-card:hover {
      transform: translateY(-4px);
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

  <!-- Navbar -->
  <?php include 'includes/navbar.php'; ?>

  <!-- Breadcrumb -->
  <section class="bg-white py-3 shadow-sm mt-14">
    <div class="max-w-container mx-auto px-6">
      <nav aria-label="breadcrumb" class="text-sm">
        <ol class="flex gap-2">
          <li><a href="index.php" class="text-muted-light hover:text-accent">Home</a></li>
          <li class="text-muted-light">/</li>
          <li><a href="allproduct.php" class="text-muted-light hover:text-accent">Products</a></li>
          <li class="text-muted-light">/</li>
          <li class="text-muted font-medium"><?= htmlspecialchars($produk['nama_produk']) ?></li>
        </ol>
      </nav>
    </div>
  </section>

  <!-- Product Detail Section -->
  <section class="py-8">
    <div class="max-w-container mx-auto px-6">
      <div class="grid md:grid-cols-2 gap-8">
  <!-- Product Gallery -->
  <div class="md:sticky md:top-20">
          <div class="relative">
            <span class="absolute top-4 left-4 bg-accent text-white px-3 py-1 text-xs font-semibold uppercase z-10">Fresh</span>
            <img src="<?= htmlspecialchars($produk['foto']) ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" class="w-full h-80 md:h-96 object-cover product-image">
          </div>
        </div>

        <!-- Product Info -->
        <div class="bg-white p-6 shadow-lg">
          <span class="inline-block bg-accent/20 text-muted px-3 py-1 text-xs font-semibold uppercase mb-3"><?= htmlspecialchars($produk['nama_kategori']) ?></span>
          <h1 class="font-serif text-2xl font-bold mb-3 text-muted"><?= htmlspecialchars($produk['nama_produk']) ?></h1>
          
          <div class="flex items-center gap-3 mb-4">
            <div class="flex text-yellow-400 text-sm">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <span class="text-muted-light text-sm">5.0 <span class="text-accent font-medium">(28 reviews)</span></span>
          </div>

          <div class="bg-gradient-to-r from-orange-50 to-yellow-50 p-4 mb-5">
            <div class="text-muted-light text-xs uppercase font-semibold mb-1">Price</div>
            <div class="text-accent-dark text-2xl font-bold font-serif"><?= $harga ?></div>
          </div>

          <div class="mb-5">
            <h6 class="font-semibold text-muted mb-2 text-sm flex items-center gap-2"><i class="fas fa-info-circle text-accent"></i> Description</h6>
            <p class="text-muted-light leading-relaxed text-sm"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>
          </div>


          <!-- Order Form -->
          <div class="bg-gradient-to-r from-gray-50 to-white p-5 border-2 border-accent/20">
            <h5 class="font-serif text-lg font-semibold mb-4 text-muted flex items-center gap-2"><i class="fab fa-whatsapp text-green-500"></i> Order via WhatsApp</h5>
            <form id="formWA">
              <div class="mb-4">
                <label class="block text-muted font-medium mb-1 text-sm flex items-center gap-2"><i class="fas fa-user text-accent"></i> Your Name</label>
                <input type="text" id="nama" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-accent outline-none text-sm" placeholder="Enter your name" required>
              </div>
              <div class="mb-4">
                <label class="block text-muted font-medium mb-1 text-sm flex items-center gap-2"><i class="fas fa-sort-numeric-up text-accent"></i> Quantity</label>
                <div class="flex items-center gap-2">
                  <button type="button" class="w-8 h-8 bg-accent text-white rounded-lg flex items-center justify-center text-sm font-bold hover:bg-accent-dark transition" onclick="decreaseQty()">-</button>
                  <input type="number" id="jumlah" class="w-16 text-center px-2 py-2 border border-gray-300 rounded-lg focus:border-accent outline-none text-sm font-bold" min="1" value="1" required readonly>
                  <button type="button" class="w-8 h-8 bg-accent text-white rounded-lg flex items-center justify-center text-sm font-bold hover:bg-accent-dark transition" onclick="increaseQty()">+</button>
                </div>
              </div>
               <div class="mb-4">
                <label class="block text-muted font-medium mb-1 text-sm flex items-center gap-2"><i class="fas fa-phone text-accent"></i> Telephone Number</label>
                <input type="number" id="no_telp" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-accent outline-none text-sm" placeholder="Enter Number Phone +62" required>
              </div>
               <div class="mb-4">
                <label class="block text-muted font-medium mb-1 text-sm flex items-center gap-2"><i class="fas fa-user text-accent"></i> Recipient Name </label>
                <input type="text" id="recipient_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-accent outline-none text-sm" placeholder="Enter Recipient Name" required>
              </div>
              <div class="mb-4">
                <label class="block text-muted font-medium mb-1 text-sm flex items-center gap-2"><i class="fas fa-phone text-accent"></i> Recipient Number</label>
                <input type="text" id="recipient_phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-accent outline-none text-sm" placeholder="Enter Recipient Number" required>
              </div>
              <div class="mb-4">
                <label class="block text-muted font-medium mb-1 text-sm flex items-center gap-2"><i class="fas fa-comment-dots text-accent"></i> Add Ons/Notes (Optional)</label>
                <textarea id="catatan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-accent outline-none text-sm" rows="3" placeholder="Add any special requests or messages..."></textarea>
              </div>
              <button type="button" onclick="kirimWA()" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-3 rounded-full font-semibold text-sm hover:from-green-600 hover:to-green-700 transition flex items-center justify-center gap-2 shadow-lg">
                <i class="fab fa-whatsapp"></i> Send Order via WhatsApp
              </button>
              <a href="allproduct.php" class="inline-block w-full text-center mt-3 bg-white text-accent border-2 border-accent py-3 rounded-full fonst-semibold text-sm hover:bg-accent hover:text-white transition flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Products
              </a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Related Products -->
  <?php if ($related_result->num_rows > 0): ?>
  <section class="py-8 bg-white">
    <div class="max-w-container mx-auto px-6">
      <div class="text-center mb-6">
        <h2 class="font-serif text-xl font-semibold mb-1 text-muted">You May Also Like</h2>
        <p class="text-muted-light text-sm">Discover more beautiful arrangements</p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php while($related = $related_result->fetch_assoc()): ?>
          <div class="text-center">
            <div class="relative w-32 h-32 mx-auto mb-3 rounded-full overflow-hidden bg-gray-100">
              <img src="<?= htmlspecialchars($related['foto']) ?>" alt="<?= htmlspecialchars($related['nama_produk']) ?>" class="w-full h-full object-cover rounded-full hover:scale-105 transition-transform duration-300">
              <span class="absolute top-2 right-2 bg-accent text-white px-2 py-1 rounded-full text-xs font-semibold">Fresh</span>
            </div>
            <div>
              <div class="text-xs text-muted-light uppercase mb-1"><?= htmlspecialchars($related['nama_kategori']) ?></div>
              <h3 class="font-display text-sm font-semibold mb-2 line-clamp-2"><?= htmlspecialchars($related['nama_produk']) ?></h3>
              <div class="text-accent font-bold mb-3 text-sm">Rp <?= number_format($related['harga'], 0, ',', '.') ?></div>
              <a href="product-detail.php?id=<?= $related['id'] ?>" class="inline-block bg-accent text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-accent-dark transition">View Details</a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>


  <script>
    // Navbar scroll effect
    window.addEventListener('scroll', () => {
      const nav = document.getElementById('navbar');
      if (window.scrollY > 30) nav.classList.add('navbar-scrolled', 'shadow-lg');
      else nav.classList.remove('navbar-scrolled', 'shadow-lg');
    });

    // Mobile menu
    document.getElementById('mobile-menu-btn').addEventListener('click', () => {
      document.getElementById('mobile-menu').classList.toggle('hidden');
    });

    // Quantity controls
    function increaseQty() {
      const input = document.getElementById('jumlah');
      input.value = parseInt(input.value) + 1;
    }

    function decreaseQty() {
      const input = document.getElementById('jumlah');
      if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
      }
    }

    // WhatsApp Order
    function kirimWA() {
      const nama = document.getElementById("nama").value.trim();
      const notelp = document.getElementById("no_telp").value.trim();
      const recipientName = document.getElementById("recipient_name").value.trim();
      const recipientPhone = document.getElementById("recipient_phone").value.trim();
      const jumlah = document.getElementById("jumlah").value.trim();
      const catatan = document.getElementById("catatan").value.trim();
      const produk = "<?= addslashes($produk['nama_produk']) ?>";
      const hargaPerItem = <?= (int)$produk['harga'] ?>;
      const hargaText = "<?= addslashes($harga) ?>";
      const nomorWA = "6289524810752";

      if (!nama || !jumlah) {
        alert("Please fill in your name and quantity.");
        return;
      }

      const totalHarga = hargaPerItem * parseInt(jumlah);
      const totalFormatted = "Rp " + totalHarga.toLocaleString('id-ID');

      let pesan = `Hello Customer %0A`;
      pesan += `Thankyou for Ordering and trusting us %0A%0A`;
      pesan += `- Nama Pemesan: *${nama}*%0A`;
      if (notelp) pesan += `- No. Telp Pemesan: *${notelp}*%0A`;
      pesan += `- Produk: *${produk}*%0A`;
      pesan += `- Jumlah: *${jumlah}* pcs%0A`;
      pesan += `- Harga per Item: *${hargaText}*%0A`;
      if (recipientName) pesan += `- Penerima: *${recipientName}*%0A`;
      if (recipientPhone) pesan += `- No. Telp Penerima: *${recipientPhone}*%0A`;
      if (catatan) pesan += `- Catatan: ${catatan}%0A`;
      pesan += `- Total: *${totalFormatted}*%0A%0A`;
      pesan += `Terima kasih!`;

      const url = `https://wa.me/${nomorWA}?text=${pesan}`;
      window.open(url, "_blank");
    }

    // Prevent form submission
    document.getElementById('formWA').addEventListener('submit', function(e) {
      e.preventDefault();
    });
  </script>
</body>
</html>
