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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($produk['nama_produk']) ?> - Bellisca Florist</title>

  <!-- ✅ Link CSS sama seperti index -->
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

    body {
      background: var(--bg);
      color: var(--muted);
      font-family: "Radley", serif;
      font-size: 1.1rem;
      padding-top: 80px; /* biar ga ketimpa navbar */
    }

    /* === NAVBAR (copy dari index.html) === */
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

    .nav-link:hover {
      color: var(--accent) !important;
    }

    /* === DETAIL PRODUK === */
    .product-detail {
      padding: 3rem 0 6rem;
    }

    .product-detail img {
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .product-title {
      font-family: "DM Serif Text", serif;
      font-size: 2.2rem;
      margin-bottom: 0.5rem;
    }

    .product-price {
      color: var(--accent);
      font-size: 1.5rem;
      font-weight: 700;
    }

    .btn-whatsapp {
      background: #25D366;
      color: #fff;
      border-radius: 8px;
      padding: 0.8rem 1.5rem;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-whatsapp:hover {
      background: #1ebe5d;
      transform: scale(1.05);
    }

    .btn-accent {
      background: var(--accent);
      color: #fff;
      border-radius: 8px;
      padding: 0.8rem 1.5rem;
      text-decoration: none;
    }

    .btn-accent:hover {
      background: #b98f56;
    }
  </style>
</head>
<body>

  <!-- ✅ NAVBAR IDENTIK -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">Bellisca Florist</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="allproduct.html">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- === SECTION DETAIL PRODUK === -->
  <section class="product-detail">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-md-6">
          <img src="<?= htmlspecialchars($produk['foto']) ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" class="img-fluid">
        </div>
        <div class="col-md-6">
          <h2 class="product-title"><?= htmlspecialchars($produk['nama_produk']) ?></h2>
          <div class="product-price"><?= $harga ?></div>
          <p class="mt-3"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>

          <form id="formWA" class="mt-4">
            <div class="mb-3">
              <label class="form-label">Nama Anda</label>
              <input type="text" id="nama" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Jumlah Bunga</label>
              <input type="number" id="jumlah" class="form-control" min="1" value="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Catatan (Opsional)</label>
              <textarea id="catatan" class="form-control" rows="2"></textarea>
            </div>

            <button type="button" onclick="kirimWA()" class="btn btn-whatsapp">
              <i class="fa-brands fa-whatsapp me-2"></i> Kirim ke WhatsApp
            </button>
            <button type="button" class="btn btn-accent ms-2" onclick="history.back()">
              <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <script src="js/bootstrap.bundle.min.js"></script>
  <script>
    function kirimWA() {
      const nama = document.getElementById("nama").value.trim();
      const jumlah = document.getElementById("jumlah").value.trim();
      const catatan = document.getElementById("catatan").value.trim();
      const produk = "<?= addslashes($produk['nama_produk']) ?>";
      const nomorWA = "6281234567890"; // ubah ke nomor kamu

      if (!nama || !jumlah) {
        alert("Harap isi nama dan jumlah bunga terlebih dahulu.");
        return;
      }

      const pesan = `Halo Bellisca Florist 🌸%0ASaya, *${nama}*, ingin membeli bunga *${produk}* sebanyak *${jumlah}* buah.%0A${catatan ? "Catatan: " + catatan + "%0A" : ""}Apakah masih tersedia?`;
      const url = `https://wa.me/${nomorWA}?text=${pesan}`;
      window.open(url, "_blank");
    }
  </script>
</body>
</html>
