<?php
include "config/db.php";

// Ambil parameter filter
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort     = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

// Query dasar
$sql = "SELECT produk.*, kategori.nama_kategori 
        FROM produk 
        INNER JOIN kategori ON produk.kategori_id = kategori.id
        WHERE 1";

$params = [];
$types  = "";

// Filter kategori
if ($category !== 'all') {
    $sql .= " AND LOWER(REPLACE(kategori.nama_kategori, ' ', '')) = ?";
    $params[] = strtolower(str_replace(' ', '', $category));
    $types .= "s";
}

// Filter pencarian
if ($search !== '') {
    $sql .= " AND produk.nama_produk LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

// Sort
switch ($sort) {
    case 'low-high': $sql .= " ORDER BY produk.harga ASC"; break;
    case 'high-low': $sql .= " ORDER BY produk.harga DESC"; break;
    case 'name-az':  $sql .= " ORDER BY produk.nama_produk ASC"; break;
    default:         $sql .= " ORDER BY produk.id DESC";
}

// Eksekusi
$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Kategori
$kategori_query = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$total_products = $result->num_rows;
// separate query for mobile dropdown (so we don't reuse the sidebar result set)
$kategori_mobile = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Products - Bellisca Florist</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Text&family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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
    body { font-size: 0.8rem; }
   h1 { font-size: 2rem; line-height: 1.2; }
    h2 { font-size: 1.6rem; line-height: 1.2; }
    h3 { font-size: 1.3rem; line-height: 1.3; }
    p, a, li, input, button, textarea { font-size: 0.9rem; }
    a, p, label, select { font-size: 0.9rem; }
    .navbar-scrolled { padding-top: 0.3rem; padding-bottom: 0.3rem; }
    .hero-overlay {
      background: linear-gradient(135deg, rgba(93,75,59,0.8), rgba(207,168,110,0.6));
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

<body class="bg-gradient-to-br from-[#fff5eb] to-[#ffe8d6] text-muted font-sans">

  <!-- Navbar -->
  <?php include 'includes/navbar.php'; ?>

  <!-- Hero -->
  <section class="relative h-[55vh] flex items-center justify-center overflow-hidden mt-14">
    <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/22a3b8f4-0e29-40e9-b833-f97162f505be.png" class="absolute inset-0 w-full h-full object-cover">
    <div class="hero-overlay absolute inset-0"></div>
    <div class="relative z-10 text-center text-white px-6">
      <h1 class="font-serif text-3xl font-bold mb-2">Our Premium Collection</h1>
      <p class="text-sm mb-4">Handcrafted arrangements made with love</p>
      <div class="text-xs flex justify-center gap-2">
        <a href="index.php" class="hover:text-accent-light">Home</a> <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-accent-light font-semibold">Products</span>
      </div>
    </div>
  </section>

  <!-- Main -->
  <section class="py-10 bg-white">
    <div class="max-w-container mx-auto px-6 lg:px-10 flex flex-col lg:flex-row gap-6">

      <!-- Sidebar (hidden on mobile — mobile uses dropdown) -->
      <aside class="hidden lg:block w-full lg:w-64 flex-shrink-0">
        <div class="bg-bg-primary rounded-xl p-4 lg:p-5 shadow">
          <h4 class="font-serif text-base lg:text-lg font-semibold mb-3 text-muted border-b pb-2">Categories</h4>

          <ul class="space-y-2">
            <li>
              <a href="allproduct.php?category=all&sort=<?= htmlspecialchars($sort) ?>"
                 class="block px-3 py-2 rounded-md text-sm lg:text-base <?= $category=='all'?'bg-accent text-white':'hover:bg-accent/10 text-muted' ?>">
                 <i class="fa-solid fa-th mr-1"></i> All Products
              </a>
            </li>
            <?php
            if ($kategori_query && $kategori_query->num_rows > 0) {
              while ($cat = $kategori_query->fetch_assoc()) {
                $catSlug = strtolower(str_replace(' ', '', $cat['nama_kategori']));
                $isActive = ($category == $catSlug);
                echo "<li><a href='allproduct.php?category={$catSlug}&sort=" . htmlspecialchars($sort) . "' class='block px-3 py-2 rounded-md text-sm lg:text-base " . ($isActive ? 'bg-accent text-white' : 'hover:bg-accent/10 text-muted') . "'><i class='fa-solid fa-leaf mr-1'></i> {$cat['nama_kategori']}</a></li>";
              }
            }
            ?>
          </ul>

          <div class="mt-5 p-3 lg:p-4 bg-accent text-white rounded-md text-center text-sm">
            <p class="mb-2">Need help?</p>
            <a href="https://wa.me/6289524810752?text=Hello%20Bellisca!%20I%20need%20help%20choosing%20flowers"
               target="_blank"
               class="inline-block bg-white text-accent px-3 lg:px-4 py-1.5 rounded-full hover:bg-accent-dark hover:text-white transition text-xs font-semibold">
               <i class="fab fa-whatsapp mr-1"></i> Chat Us
            </a>
          </div>
        </div>
      </aside>

      <!-- Products -->
      <main class="flex-1">
        <!-- Mobile filter dropdown (visible only on small screens) -->
        <div class="block lg:hidden mb-4 px-2">
          <form method="GET">
            <label for="mobile-category" class="block text-sm font-semibold text-muted mb-2">Categories</label>
            <select id="mobile-category" name="category" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-accent">
              <option value="all" <?= $category=='all'?'selected':'' ?>>All</option>
              <?php if ($kategori_mobile && $kategori_mobile->num_rows > 0) {
                while ($k = $kategori_mobile->fetch_assoc()) {
                  $catSlug = strtolower(str_replace(' ', '', $k['nama_kategori']));
                  echo '<option value="' . $catSlug . '"' . ($category == $catSlug ? ' selected' : '') . '>' . htmlspecialchars($k['nama_kategori']) . '</option>';
                }
              } ?>
            </select>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
          </form>
        </div>
        <div class="bg-white rounded-xl shadow p-4 mb-5 flex items-center justify-between">
          <div class="text-sm text-muted-light">
            Showing <strong class="text-accent"><?= $total_products ?></strong> products
          </div>
          <form method="GET" class="flex items-center gap-2 text-xs">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <select name="sort" onchange="this.form.submit()" class="border border-gray-300 rounded-md px-2 py-1 text-muted text-xs focus:border-accent focus:ring-accent/20">
              <option value="latest" <?= $sort=='latest'?'selected':'' ?>>Latest</option>
              <option value="low-high" <?= $sort=='low-high'?'selected':'' ?>>Low → High</option>
              <option value="high-low" <?= $sort=='high-low'?'selected':'' ?>>High → Low</option>
              <option value="name-az" <?= $sort=='name-az'?'selected':'' ?>>A → Z</option>
            </select>
          </form>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
          <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5">
            <?php while($row = $result->fetch_assoc()): ?>
              <div class="text-center">
                <div class="relative w-20 h-20 md:w-32 md:h-32 bg-gray-100 overflow-hidden rounded-full mb-3 mx-auto mt-2 md:mt-4">
                  <img src="<?= htmlspecialchars($row['foto']) ?>" class="w-full h-full object-cover rounded-full hover:scale-105 transition-transform duration-300">
                </div>
                <div class="text-xs md:text-sm text-muted-light uppercase mb-1"><?= htmlspecialchars($row['nama_kategori']) ?></div>
                <h3 class="font-display text-sm md:text-base font-semibold mb-1"><?= htmlspecialchars($row['nama_produk']) ?></h3>
                <div class="text-accent font-bold text-sm md:text-lg mb-3">Rp <?= number_format($row['harga'], 0, ',', '.') ?></div>
                <a href="product-detail.php?id=<?= $row['id'] ?>" class="inline-block bg-accent text-white px-4 py-2 rounded-full text-sm font-semibold hover:bg-accent-dark transition">View Details</a>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-12 text-muted-light">
            <i class="fa fa-box-open text-4xl text-accent/40 mb-2"></i>
            <p>No products found</p>
          </div>
        <?php endif; ?>
      </main>
    </div>
  </section>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>


  <script>
    const nav=document.getElementById('navbar');
    window.addEventListener('scroll',()=>{window.scrollY>30?nav.classList.add('navbar-scrolled','shadow-lg'):nav.classList.remove('navbar-scrolled','shadow-lg')});
    document.getElementById('mobile-menu-btn').addEventListener('click',()=>document.getElementById('mobile-menu').classList.toggle('hidden'));
  </script>
</body>
</html>
