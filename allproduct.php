<?php
include "config/db.php";

// Ambil kategori dari URL
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Query ambil produk (join kategori)
if ($category == 'all') {
  $sql = "SELECT produk.*, kategori.nama_kategori 
          FROM produk 
          INNER JOIN kategori ON produk.kategori_id = kategori.id
          ORDER BY produk.id DESC";
  $stmt = $conn->prepare($sql);
} else {
  $sql = "SELECT produk.*, kategori.nama_kategori 
          FROM produk 
          INNER JOIN kategori ON produk.kategori_id = kategori.id
          WHERE kategori.nama_kategori LIKE ?";
  $stmt = $conn->prepare($sql);
  $catLike = "%$category%";
  $stmt->bind_param("s", $catLike);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>All Products - Bellisca Florist</title>

  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Radley:ital@0;1&display=swap"
    rel="stylesheet"
  />
  <style>
    :root {
      --bg: #fffaf5;
      --accent: #cfa86e;
      --muted: #5d4b3b;
      --muted-2: #6b5646;
    }

    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }

    body {
      min-height: 100vh;
      background: var(--bg);
      color: var(--muted);
      font-family: "Radley", serif;
      font-size: 1.1rem;
      padding-top: 80px;
    }

    footer { margin-top: auto; }

    /* Navbar (identik dengan index.html) */
    .navbar {
      background: #fff;
      box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }
    .navbar-brand {
      font-family: "DM Serif Text", serif;
      color: var(--muted);
      font-size: 2rem;
      letter-spacing: 1px;
    }
    .nav-link {
      font-family: 'Playfair Display', serif;
      color: var(--muted) !important;
      font-size: 1.1rem;
      margin-right: 0.4rem;
      transition: color .3s ease;
    }
    .nav-link:hover { color: var(--accent) !important; }

    /* Section */
    .all-products { padding: 5rem 0; }
    .all-products h2 {
      font-family: "DM Serif Text", serif;
      font-size: 2.5rem;
      text-align: center;
      margin-bottom: 1rem;
    }
    .filter-container { text-align: center; margin-bottom: 2rem; }

    .card-product {
      border: none;
      border-radius: 14px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }
    .card-product:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
    }
    .card-product img {
      width: 100%;
      height: 220px;
      object-fit: cover;
    }
    .product-title {
      font-family: "Playfair Display", serif;
      font-weight: 600;
      font-size: 1.1rem;
      margin-top: 0.5rem;
    }
    .product-price { color: var(--accent); font-weight: 700; }

    footer {
      background: var(--muted);
      color: #fffaf5;
      text-align: center;
      padding: 1.2rem 0;
      font-size: 1rem;
    }

    .btn-detail {
      display: inline-block;
      border: 1px solid var(--accent);
      color: var(--accent);
      font-size: 1rem;
      border-radius: 30px;
      padding: 0.45rem 1.2rem;
      background: transparent;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    .btn-detail:hover {
      background: var(--accent);
      color: #fff;
      box-shadow: 0 6px 15px rgba(207, 168, 110, 0.3);
    }

    /* Sidebar */
    .sidebar-categories {
      background: #fff;
      border-right: 1px solid #eee;
      min-height: 100vh;
    }
    .sidebar-title {
      font-family: "DM Serif Text", serif;
      color: var(--muted);
      font-size: 1.4rem;
      margin-bottom: 1rem;
    }
    .category-list a {
      display: block;
      padding: 0.6rem 1rem;
      color: var(--muted-2);
      border-radius: 6px;
      font-family: 'Radley', serif;
      text-decoration: none;
      transition: all .3s ease;
    }
    .category-list a:hover, .category-list a.active {
      background: var(--accent);
      color: #fff;
    }

    @media (max-width: 767px) {
      .sidebar-categories { border-right: none; border-bottom: 1px solid #eee; text-align: center; min-height: auto; padding-bottom: 1rem; }
      .category-list { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; }
      .category-list a { padding: 0.4rem 0.8rem; font-size: 0.95rem; }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">Bellisca Florist</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="allproduct.php">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Section -->
  <section class="all-products">
    <div class="container-fluid">
      <div class="row">
        <!-- Sidebar -->
        <aside class="col-md-3 col-lg-2 sidebar-categories p-4">
          <h4 class="sidebar-title">Categories</h4>
          <ul class="list-unstyled category-list">
            <li><a href="allproduct.php?category=all" <?= $category=='all'?'class="active"':'' ?>>Semua Produk</a></li>
            <?php
              $cats = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
              while ($cat = $cats->fetch_assoc()) {
                $catSlug = strtolower(str_replace(' ', '', $cat['nama_kategori']));
                $active = ($category == $catSlug) ? 'class="active"' : '';
                echo "<li><a href='allproduct.php?category={$catSlug}' {$active}>{$cat['nama_kategori']}</a></li>";
              }
            ?>
          </ul>
        </aside>

        <!-- Produk -->
        <main class="col-md-9 col-lg-10 p-4">
          <h2 class="text-center mb-4">All Products</h2>
          <p class="text-center text-muted mb-5">
            Temukan berbagai pilihan bunga dan rangkaian terbaik dari Bellisca Florist 🌷
          </p>

          <div class="row g-4" id="product-list">
            <?php if ($result->num_rows > 0): ?>
              <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-6 col-md-4 col-lg-3 product-item" data-category="<?= strtolower(str_replace(' ', '', $row['nama_kategori'])) ?>">
                  <div class="card-product">
                    <img src="<?= htmlspecialchars($row['foto']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" />
                    <div class="p-2 text-center">
                      <div class="product-title"><?= htmlspecialchars($row['nama_produk']) ?></div>
                      <div class="product-price">Rp <?= number_format($row['harga'], 0, ',', '.') ?></div>
                      <a href="product-detail.php?id=<?= $row['id'] ?>" class="btn-detail">Lihat Produk</a>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p class="text-center text-muted">Belum ada produk pada kategori ini.</p>
            <?php endif; ?>
          </div>
        </main>
      </div>
    </div>
  </section>

  <footer>
    © 2025 Copyright by Bellisca Florist
  </footer>

  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
