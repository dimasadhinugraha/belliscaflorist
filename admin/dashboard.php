<?php
session_start();
include "../config/db.php";

// 🔒 Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

$message = "";

// 🧾 Fungsi tambah produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama        = trim($_POST['nama_produk']);
  $kategori_id = intval($_POST['kategori_id']);
  $harga       = floatval($_POST['harga']);
  $deskripsi   = trim($_POST['deskripsi']);

  // 🔍 Validasi input sederhana
  if ($nama === "" || $kategori_id <= 0 || $harga <= 0 || $deskripsi === "") {
    $message = "<div class='alert alert-danger'>Semua kolom wajib diisi dengan benar!</div>";
  } else {
    // 🖼️ Folder upload disimpan di luar folder admin
    $upload_dir = dirname(__DIR__) . "/uploads/"; // otomatis ke folder di luar admin
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }

    $foto_name = basename($_FILES["foto"]["name"]);
    $foto_tmp  = $_FILES["foto"]["tmp_name"];
    $foto_type = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($foto_type, $allowed_types)) {
      $message = "<div class='alert alert-danger'>Hanya boleh upload file gambar (JPG, JPEG, PNG, GIF)!</div>";
    } else {
      $unique_name = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $foto_name);
      $target_file = $upload_dir . $unique_name;

      if (move_uploaded_file($foto_tmp, $target_file)) {
        // 🧭 Simpan path relatif ke root agar bisa diakses dari semua folder
        $db_path = "uploads/" . $unique_name;

        // ✅ Simpan ke database
        $sql = "INSERT INTO produk (nama_produk, kategori_id, harga, deskripsi, foto)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sidss", $nama, $kategori_id, $harga, $deskripsi, $db_path);

        if ($stmt->execute()) {
          header("Location: products.php?success=1");
          exit();
        } else {
          $message = "<div class='alert alert-danger'>Gagal menambahkan produk: " . htmlspecialchars($conn->error) . "</div>";
        }
      } else {
        $message = "<div class='alert alert-danger'>Upload foto gagal. Periksa permission folder uploads!</div>";
      }
    }
  }
}

// Get statistics
$total_products = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];
$total_categories = $conn->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Bellisca Florist</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Serif+Text&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --accent: #cfa86e;
      --accent-dark: #b98f56;
      --accent-light: #e5c79f;
      --muted: #5d4b3b;
      --muted-light: #8b7355;
      --bg: #fffaf5;
      --bg-gradient-start: #fff5eb;
      --bg-gradient-end: #ffe8d6;
      --sidebar-width: 280px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
      font-family: 'Poppins', sans-serif;
      color: var(--muted);
      min-height: 100vh;
      display: flex;
      position: relative;
      overflow-x: hidden;
    }

    /* Floral background pattern */
    body::before {
      content: '';
      position: fixed;
      width: 100%;
      height: 100%;
      background-image: 
        radial-gradient(circle at 20% 80%, rgba(207, 168, 110, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(207, 168, 110, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(207, 168, 110, 0.03) 0%, transparent 50%);
      pointer-events: none;
      z-index: 0;
    }

    /* Sidebar - EXACT SAME AS products.php */
    .sidebar {
      width: var(--sidebar-width);
      background: linear-gradient(180deg, var(--muted) 0%, #4a3a2d 100%);
      color: #fff;
      flex-shrink: 0;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      padding: 2rem 0;
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
      z-index: 1000;
      overflow-y: auto;
    }

    .sidebar-header {
      padding: 0 1.5rem 2rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 2rem;
    }

    .sidebar-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 0.5rem;
    }

    .sidebar-brand-icon {
      width: 48px;
      height: 48px;
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(207, 168, 110, 0.3);
    }

    .sidebar-brand-icon i {
      font-size: 24px;
      color: white;
    }

    .sidebar h4 {
      font-family: "DM Serif Text", serif;
      margin: 0;
      font-size: 20px;
      color: #fffaf5;
    }

    .sidebar-subtitle {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.6);
      padding-left: 1.5rem;
      font-weight: 300;
    }

    .sidebar-nav {
      padding: 0 1rem;
    }

    .sidebar a {
      color: rgba(255, 255, 255, 0.85);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0.875rem 1.25rem;
      border-radius: 12px;
      margin-bottom: 0.5rem;
      font-size: 15px;
      font-weight: 500;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .sidebar a::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      background: var(--accent);
      transform: scaleY(0);
      transition: transform 0.3s ease;
    }

    .sidebar a:hover, .sidebar a.active {
      background: rgba(207, 168, 110, 0.15);
      color: var(--accent-light);
      transform: translateX(4px);
    }

    .sidebar a.active::before {
      transform: scaleY(1);
    }

    .sidebar a i {
      font-size: 18px;
      width: 24px;
      text-align: center;
    }

    .sidebar .logout {
      margin-top: auto;
      color: #ffdddd;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      margin-top: 2rem;
      padding-top: 1rem;
    }

    .sidebar .logout:hover {
      background: rgba(255, 100, 100, 0.1);
      color: #ff6b6b;
    }

    /* Main Content */
    .main-content {
      margin-left: var(--sidebar-width);
      flex-grow: 1;
      padding: 2rem;
      position: relative;
      z-index: 1;
    }

    .page-header {
      margin-bottom: 2rem;
      animation: slideDown 0.6s ease-out;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .page-header h3 {
      font-family: "DM Serif Text", serif;
      font-size: 32px;
      color: var(--muted);
      margin-bottom: 0.5rem;
    }

    .page-header p {
      color: var(--muted-light);
      font-size: 14px;
    }

    /* Statistics Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
      animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .stat-card {
      background: white;
      padding: 1.5rem;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      display: flex;
      align-items: center;
      gap: 1rem;
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 30px rgba(207, 168, 110, 0.15);
    }

    .stat-icon {
      width: 56px;
      height: 56px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    .stat-icon.products {
      background: linear-gradient(135deg, #4ade80, #22c55e);
      color: white;
    }

    .stat-icon.categories {
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      color: white;
    }

    .stat-content h4 {
      font-size: 28px;
      font-weight: 700;
      color: var(--muted);
      margin: 0;
    }

    .stat-content p {
      font-size: 13px;
      color: var(--muted-light);
      margin: 0;
    }

    /* Form Section */
    .form-section {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      padding: 2.5rem;
      animation: slideUp 0.6s ease-out 0.2s backwards;
    }

    .form-section h3 {
      font-family: "DM Serif Text", serif;
      font-size: 24px;
      margin-bottom: 1.5rem;
      color: var(--muted);
      padding-bottom: 1rem;
      border-bottom: 2px solid #f5f5f5;
    }

    /* Form Elements */
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--muted);
      font-size: 14px;
    }

    .form-control, .form-select {
      border: 2px solid #e8dfd5;
      border-radius: 10px;
      padding: 0.75rem 1rem;
      font-size: 14px;
      transition: all 0.3s ease;
      background: white;
      width: 100%;
    }

    .form-control:focus, .form-select:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(207, 168, 110, 0.1);
    }

    textarea.form-control {
      resize: vertical;
      min-height: 100px;
    }

    /* Alerts */
    .alert {
      border-radius: 12px;
      margin-bottom: 1.5rem;
      padding: 1rem 1.25rem;
      border: none;
      display: flex;
      align-items: center;
      gap: 12px;
      animation: slideDown 0.4s ease-out;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .alert-success {
      background: linear-gradient(135deg, #f0fff4, #dcfce7);
      color: #15803d;
      border-left: 4px solid #22c55e;
    }

    .alert-danger {
      background: linear-gradient(135deg, #fef2f2, #fee2e2);
      color: #b91c1c;
      border-left: 4px solid #ef4444;
    }

    .alert i {
      font-size: 20px;
    }

    /* Button */
    .btn-accent {
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      color: white;
      border-radius: 10px;
      padding: 0.875rem 2rem;
      font-weight: 600;
      font-size: 15px;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(207, 168, 110, 0.3);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-accent:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(207, 168, 110, 0.4);
    }

    .btn-accent:active {
      transform: translateY(0);
    }

    /* File Input Styling */
    input[type="file"] {
      padding: 0.6rem 1rem;
    }

    input[type="file"]::file-selector-button {
      background: linear-gradient(135deg, var(--accent-light), var(--accent));
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      margin-right: 1rem;
      transition: all 0.3s ease;
    }

    input[type="file"]::file-selector-button:hover {
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    }

    /* Responsive */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 1rem;
      }

      .page-header h3 {
        font-size: 24px;
      }

      .form-section {
        padding: 1.5rem;
      }
    }
  </style>
</head>

<body>

  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="page-header">
      <h3>Tambah Produk Baru</h3>
      <p>Tambahkan produk baru ke katalog Bellisca Florist</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon products">
          <i class="fas fa-box"></i>
        </div>
        <div class="stat-content">
          <h4><?= $total_products ?></h4>
          <p>Total Produk</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon categories">
          <i class="fas fa-tags"></i>
        </div>
        <div class="stat-content">
          <h4><?= $total_categories ?></h4>
          <p>Total Kategori</p>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h3><i class="fas fa-plus-circle"></i> Form Tambah Produk</h3>

      <?= $message ?>

      <form method="POST" enctype="multipart/form-data" id="productForm">
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-tag"></i> Nama Produk</label>
          <input type="text" name="nama_produk" class="form-control" placeholder="Masukkan nama produk" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="fas fa-money-bill-wave"></i> Harga (Rp)</label>
          <input type="number" name="harga" class="form-control" placeholder="Masukkan harga produk" min="0" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="fas fa-layer-group"></i> Kategori</label>
          <select name="kategori_id" class="form-select" required>
            <option value="">Pilih Kategori</option>
            <?php
              $kategori = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
              if ($kategori && $kategori->num_rows > 0) {
                while ($row = $kategori->fetch_assoc()) {
                  echo "<option value='{$row['id']}'>" . htmlspecialchars($row['nama_kategori']) . "</option>";
                }
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="fas fa-align-left"></i> Deskripsi Produk</label>
          <textarea name="deskripsi" class="form-control" rows="4" placeholder="Tulis deskripsi produk..." required></textarea>
        </div>

        <div class="mb-4">
          <label class="form-label"><i class="fas fa-image"></i> Foto Produk</label>
          <input type="file" name="foto" class="form-control" accept="image/*" required>
          <small class="text-muted" style="font-size: 12px; display: block; margin-top: 0.5rem;">
            <i class="fas fa-info-circle"></i> Format: JPG, JPEG, PNG, GIF (Max 5MB)
          </small>
        </div>

        <button type="submit" class="btn btn-accent">
          <i class="fas fa-plus-circle"></i>
          Tambah Produk
        </button>
      </form>
    </div>
  </div>

  <script src="../js/bootstrap.bundle.min.js"></script>
  <script>
    // Form validation
    document.getElementById('productForm').addEventListener('submit', function(e) {
      const harga = document.querySelector('input[name="harga"]').value;
      if (parseFloat(harga) <= 0) {
        e.preventDefault();
        alert('Harga harus lebih dari 0!');
        return false;
      }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => alert.remove(), 500);
      });
    }, 5000);
  </script>
</body>
</html>