<?php
session_start();
include "../config/db.php";

// 🔒 Cek login admin
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

// Pastikan ada parameter id produk
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: products.php");
  exit();
}

$id = intval($_GET['id']);
$message = "";

// 🧾 Ambil data produk berdasarkan ID
$sql = "SELECT * FROM produk WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  die("<div style='text-align:center; padding:2rem; font-family:sans-serif;'>Produk tidak ditemukan.</div>");
}

$produk = $result->fetch_assoc();

// Get statistics
$total_products = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];
$total_categories = $conn->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'];

// 🛠 Update produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama        = trim($_POST['nama_produk']);
  $harga       = floatval($_POST['harga']);
  $kategori_id = intval($_POST['kategori_id']);
  $deskripsi   = trim($_POST['deskripsi']);
  $foto_lama   = $produk['foto'];

  // Upload foto baru (jika ada)
  $upload_dir = dirname(__DIR__) . "/uploads/";
  if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

  if (!empty($_FILES['foto']['name'])) {
    $foto_name = basename($_FILES['foto']['name']);
    $foto_tmp  = $_FILES['foto']['tmp_name'];
    $foto_type = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($foto_type, $allowed_types)) {
      $unique_name = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $foto_name);
      $target_file = $upload_dir . $unique_name;
      if (move_uploaded_file($foto_tmp, $target_file)) {
        // hapus foto lama jika ada
        $old_file = dirname(__DIR__) . "/" . $foto_lama;
        if (file_exists($old_file) && $foto_lama !== "") unlink($old_file);
        $foto_path = "uploads/" . $unique_name;
      } else {
        $message = "<div class='alert alert-danger'>Gagal mengupload foto baru.</div>";
      }
    } else {
      $message = "<div class='alert alert-danger'>Format file tidak valid (hanya JPG, PNG, GIF).</div>";
    }
  } else {
    $foto_path = $foto_lama;
  }

  // Update ke database
  if (empty($message)) {
    $update = "UPDATE produk 
               SET nama_produk = ?, kategori_id = ?, harga = ?, deskripsi = ?, foto = ?
               WHERE id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("sidssi", $nama, $kategori_id, $harga, $deskripsi, $foto_path, $id);

    if ($stmt->execute()) {
      $message = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        <i class='fa-solid fa-circle-check'></i> <strong>Produk berhasil diperbarui!</strong>
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
      </div>";
      // Refresh data produk
      $produk['nama_produk'] = $nama;
      $produk['harga'] = $harga;
      $produk['kategori_id'] = $kategori_id;
      $produk['deskripsi'] = $deskripsi;
      $produk['foto'] = $foto_path;
    } else {
      $message = "<div class='alert alert-danger'>Gagal memperbarui produk: " . htmlspecialchars($conn->error) . "</div>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Produk - Bellisca Florist Admin</title>

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
      max-width: 900px;
      margin: auto;
      animation: slideUp 0.6s ease-out 0.2s backwards;
    }

    .form-section h3 {
      font-family: "DM Serif Text", serif;
      font-size: 24px;
      margin-bottom: 2rem;
      color: var(--muted);
      text-align: center;
      padding-bottom: 1rem;
      border-bottom: 2px solid #f5f5f5;
    }

    /* Product Preview */
    .product-preview {
      text-align: center;
      margin-bottom: 2rem;
      padding: 2rem;
      background: linear-gradient(135deg, #fafafa, #f5f5f5);
      border-radius: 12px;
    }

    .product-preview img {
      max-width: 200px;
      height: 200px;
      object-fit: cover;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      transition: transform 0.3s ease;
    }

    .product-preview img:hover {
      transform: scale(1.05);
    }

    .product-preview p {
      margin-top: 1rem;
      color: var(--muted-light);
      font-size: 13px;
      font-weight: 500;
    }

    /* Form Elements */
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--muted);
      font-size: 14px;
    }

    .form-label i {
      margin-right: 6px;
      color: var(--accent);
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

    .btn-close {
      background: none;
      border: none;
      font-size: 20px;
      line-height: 1;
      color: inherit;
      opacity: 0.5;
      cursor: pointer;
      transition: opacity 0.3s ease;
      margin-left: auto;
      padding: 0;
    }

    .btn-close:hover {
      opacity: 1;
    }

    /* Buttons */
    .btn {
      border-radius: 10px;
      padding: 0.875rem 2rem;
      font-weight: 600;
      font-size: 15px;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .btn-accent {
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      color: white;
      box-shadow: 0 4px 15px rgba(207, 168, 110, 0.3);
    }

    .btn-accent:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(207, 168, 110, 0.4);
      color: white;
    }

    .btn-secondary {
      background: white;
      color: var(--accent-dark);
      border: 2px solid var(--accent);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .btn-secondary:hover {
      background: var(--accent);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(207, 168, 110, 0.3);
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

    /* Action Buttons Container */
    .form-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 2rem;
      padding-top: 2rem;
      border-top: 2px solid #f5f5f5;
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

      .form-actions {
        flex-direction: column;
        gap: 1rem;
      }

      .form-actions .btn {
        width: 100%;
        justify-content: center;
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

      .product-preview {
        padding: 1.5rem;
      }

      .product-preview img {
        max-width: 150px;
        height: 150px;
      }
    }
  </style>
</head>

<body>

  <!-- Sidebar - EXACT SAME AS products.php -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
          <i class="fas fa-flower"></i>
        </div>
        <div>
          <h4>Bellisca Admin</h4>
        </div>
      </div>
      <p class="sidebar-subtitle">Dashboard Panel</p>
    </div>
    
    <div class="sidebar-nav">
      <a href="dashboard.php">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Produk</span>
      </a>
      <a href="products.php">
        <i class="fa-solid fa-list"></i>
        <span>Daftar Produk</span>
      </a>
      <a href="#" class="active">
        <i class="fa-solid fa-pen-to-square"></i>
        <span>Edit Produk</span>
      </a>
    </div>
    
    <div style="padding: 0 1rem;">
      <a href="logout.php" class="logout">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
      </a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="page-header">
      <h3>Edit Produk</h3>
      <p>Perbarui informasi produk Bellisca Florist</p>
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
      <h3><i class="fas fa-edit"></i> Form Edit Produk</h3>
      
      <?= $message ?>

      <!-- Preview Produk -->
      <div class="product-preview">
        <img src="../<?= htmlspecialchars($produk['foto']) ?>" alt="Preview Produk" id="imagePreview">
        <p><i class="fas fa-image"></i> Foto produk saat ini</p>
      </div>

      <!-- Form Edit -->
      <form method="POST" enctype="multipart/form-data" id="editForm">
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-tag"></i> Nama Produk</label>
          <input type="text" name="nama_produk" class="form-control" placeholder="Masukkan nama produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="fas fa-money-bill-wave"></i> Harga (Rp)</label>
          <input type="number" name="harga" class="form-control" placeholder="Masukkan harga produk" min="0" value="<?= htmlspecialchars($produk['harga']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="fas fa-layer-group"></i> Kategori</label>
          <select name="kategori_id" class="form-select" required>
            <option value="">Pilih Kategori</option>
            <?php
              $kategori = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
              while ($row = $kategori->fetch_assoc()) {
                $selected = ($row['id'] == $produk['kategori_id']) ? "selected" : "";
                echo "<option value='{$row['id']}' {$selected}>" . htmlspecialchars($row['nama_kategori']) . "</option>";
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="fas fa-align-left"></i> Deskripsi Produk</label>
          <textarea name="deskripsi" class="form-control" rows="4" placeholder="Tulis deskripsi produk..." required><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
        </div>

        <div class="mb-4">
          <label class="form-label"><i class="fas fa-image"></i> Ganti Foto Produk (Opsional)</label>
          <input type="file" name="foto" class="form-control" accept="image/*" id="fotoInput">
          <small class="text-muted" style="font-size: 12px; display: block; margin-top: 0.5rem;">
            <i class="fas fa-info-circle"></i> Kosongkan jika tidak ingin mengubah foto. Format: JPG, JPEG, PNG, GIF (Max 5MB)
          </small>
        </div>

        <div class="form-actions">
          <a href="products.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
          </a>
          <button type="submit" class="btn btn-accent">
            <i class="fa-solid fa-floppy-disk"></i>
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/bootstrap.bundle.min.js"></script>
  <script>
    // Image preview on file selection
    document.getElementById('fotoInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('imagePreview').src = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    // Form validation
    document.getElementById('editForm').addEventListener('submit', function(e) {
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
