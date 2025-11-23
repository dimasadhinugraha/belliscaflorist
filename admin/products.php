<?php
session_start();
include "../config/db.php";

// 🔒 Cek apakah sudah login
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

// 🧾 Variabel filter & pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$filter_kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;

// 🧾 Query dasar
$sql = "SELECT produk.*, kategori.nama_kategori 
        FROM produk 
        INNER JOIN kategori ON produk.kategori_id = kategori.id
        WHERE 1";

// 🔍 Tambahkan kondisi berdasarkan filter
if ($filter_kategori > 0) {
  $sql .= " AND produk.kategori_id = $filter_kategori";
}
if ($search !== "") {
  $safe_search = $conn->real_escape_string($search);
  $sql .= " AND produk.nama_produk LIKE '%$safe_search%'";
}

$sql .= " ORDER BY produk.id DESC";
$result = $conn->query($sql);

// Ambil semua kategori untuk dropdown filter
$kategori_all = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Get statistics
$total_products = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];
$total_categories = $conn->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'];

// 🧾 Hapus produk terpilih
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_action'])) {
  if ($_POST['delete_action'] === 'delete_selected' && !empty($_POST['selected_ids'])) {
    $ids = implode(',', array_map('intval', $_POST['selected_ids']));
    $conn->query("DELETE FROM produk WHERE id IN ($ids)");
    header("Location: products.php?deleted=selected");
    exit();
  } elseif ($_POST['delete_action'] === 'delete_all') {
    $conn->query("DELETE FROM produk");
    header("Location: products.php?deleted=all");
    exit();
  }
}

// ✅ Alert sukses tambah produk
$alert = "";
if (isset($_GET['success'])) {
  $alert = "
  <div class='alert alert-success alert-dismissible fade show' role='alert'>
    <i class='fa-solid fa-circle-check'></i>
    <strong>Produk berhasil ditambahkan!</strong>
    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
  </div>";
} elseif (isset($_GET['deleted'])) {
  if ($_GET['deleted'] === 'selected') {
    $alert = "
    <div class='alert alert-warning alert-dismissible fade show' role='alert'>
      <i class='fa-solid fa-trash'></i>
      <strong>Produk yang dipilih berhasil dihapus!</strong>
      <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
  } elseif ($_GET['deleted'] === 'all') {
    $alert = "
    <div class='alert alert-danger alert-dismissible fade show' role='alert'>
      <i class='fa-solid fa-skull'></i>
      <strong>Semua produk berhasil dihapus!</strong>
      <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Produk - Bellisca Florist Admin</title>
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

    /* Sidebar */
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

    .alert-warning {
      background: linear-gradient(135deg, #fffbeb, #fef3c7);
      color: #a16207;
      border-left: 4px solid #eab308;
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

    /* Filter Section */
    .filter-section {
      background: white;
      padding: 1.5rem;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      margin-bottom: 2rem;
      animation: slideUp 0.6s ease-out 0.1s backwards;
    }

    .form-select, .form-control {
      border: 2px solid #e8dfd5;
      border-radius: 10px;
      padding: 0.75rem 1rem;
      font-size: 14px;
      transition: all 0.3s ease;
      background: white;
    }

    .form-select:focus, .form-control:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(207, 168, 110, 0.1);
    }

    /* Buttons */
    .btn {
      border-radius: 10px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-accent {
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      color: white;
      box-shadow: 0 4px 12px rgba(207, 168, 110, 0.3);
    }

    .btn-accent:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(207, 168, 110, 0.4);
    }

    .btn-danger {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    .btn-outline-danger {
      background: white;
      color: #dc2626;
      border: 2px solid #dc2626;
      box-shadow: none;
    }

    .btn-outline-danger:hover {
      background: #dc2626;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    /* Table Container */
    .table-container {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      padding: 1.5rem;
      animation: slideUp 0.6s ease-out 0.2s backwards;
    }

    .table-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 2px solid #f5f5f5;
    }

    /* Table */
    .table-responsive {
      overflow-x: auto;
      border-radius: 12px;
    }

    .table {
      width: 100%;
      margin: 0;
    }

    thead {
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      color: white;
    }

    thead th {
      padding: 1rem;
      font-weight: 600;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border: none;
      white-space: nowrap;
    }

    thead th:first-child {
      border-radius: 12px 0 0 0;
    }

    thead th:last-child {
      border-radius: 0 12px 0 0;
    }

    tbody tr {
      transition: all 0.3s ease;
      border-bottom: 1px solid #f5f5f5;
    }

    tbody tr:hover {
      background: linear-gradient(90deg, rgba(207, 168, 110, 0.05), transparent);
      transform: scale(1.01);
    }

    tbody td {
      padding: 1rem;
      vertical-align: middle;
      font-size: 14px;
    }

    .product-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .product-image:hover {
      transform: scale(1.5);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      z-index: 10;
      position: relative;
    }

    .product-name {
      font-weight: 600;
      color: var(--muted);
    }

    .product-category {
      display: inline-block;
      padding: 4px 12px;
      background: linear-gradient(135deg, var(--accent-light), var(--accent));
      color: white;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 500;
    }

    .product-price {
      font-weight: 700;
      color: var(--accent-dark);
      font-size: 15px;
    }

    .product-description {
      max-width: 300px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      color: var(--muted-light);
    }

    /* Action Buttons */
    .btn-action {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      margin: 0 4px;
    }

    .btn-action.edit {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
      box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .btn-action.edit:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-action.delete {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .btn-action.delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: var(--muted-light);
    }

    .empty-state i {
      font-size: 64px;
      color: var(--accent-light);
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    .empty-state h5 {
      font-family: "DM Serif Text", serif;
      color: var(--muted);
      margin-bottom: 0.5rem;
    }

    /* Checkbox */
    input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: var(--accent);
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

      .table-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 1rem;
      }

      .page-header h3 {
        font-size: 24px;
      }

      .filter-section .row {
        gap: 0.5rem;
      }

      .table-container {
        padding: 1rem;
      }

      tbody td {
        font-size: 12px;
        padding: 0.75rem 0.5rem;
      }

      .product-image {
        width: 48px;
        height: 48px;
      }
    }

    /* Loading Animation */
    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    .loading {
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 0.8s linear infinite;
    }
  </style>
</head>

<body>

  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="page-header">
      <h3>Daftar Produk</h3>
      <p>Kelola semua produk Bellisca Florist</p>
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

    <?= $alert ?>

    <!-- Filter Section -->
    <div class="filter-section">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--muted);">
            <i class="fas fa-filter"></i> Filter Kategori
          </label>
          <select name="kategori" class="form-select" onchange="this.form.submit()">
            <option value="0">Semua Kategori</option>
            <?php
            if ($kategori_all && $kategori_all->num_rows > 0) {
              while ($kat = $kategori_all->fetch_assoc()) {
                $selected = ($filter_kategori == $kat['id']) ? "selected" : "";
                echo "<option value='{$kat['id']}' $selected>{$kat['nama_kategori']}</option>";
              }
            }
            ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--muted);">
            <i class="fas fa-search"></i> Cari Produk
          </label>
          <input type="text" name="search" class="form-control" placeholder="Cari nama produk..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label" style="opacity: 0;">Action</label>
          <button type="submit" class="btn btn-accent w-100">
            <i class="fa-solid fa-magnifying-glass"></i> Cari
          </button>
        </div>
      </form>
    </div>

    <!-- Table Container -->
    <div class="table-container">
      <form method="POST" onsubmit="return confirmDelete()">
        <div class="table-actions">
          <div>
            <button type="submit" name="delete_action" value="delete_selected" class="btn btn-danger btn-sm">
              <i class="fa-solid fa-trash"></i> Hapus yang Dipilih
            </button>
            <button type="submit" name="delete_action" value="delete_all" class="btn btn-outline-danger btn-sm">
              Hapus Semua
            </button>
          </div>
          <div style="font-size: 13px; color: var(--muted-light);">
            <i class="fas fa-info-circle"></i> 
            <?= $result ? $result->num_rows : 0 ?> produk ditemukan
          </div>
        </div>

        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th><input type="checkbox" id="checkAll"></th>
                <th>No</th>
                <th>Foto</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  $fotoPath = "../" . $row['foto'];
                  echo "
                  <tr>
                    <td><input type='checkbox' name='selected_ids[]' value='{$row['id']}'></td>
                    <td>{$no}</td>
                    <td><img src='{$fotoPath}' alt='Foto Produk' class='product-image'></td>
                    <td class='product-name'>" . htmlspecialchars($row['nama_produk']) . "</td>
                    <td><span class='product-category'>" . htmlspecialchars($row['nama_kategori']) . "</span></td>
                    <td class='product-price'>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>
                    <td class='product-description'>" . htmlspecialchars($row['deskripsi']) . "</td>
                    <td>
                      <a href='edit.php?id={$row['id']}' class='btn-action edit' title='Edit'><i class='fa-solid fa-pen-to-square'></i></a>
                      <a href='delete.php?id={$row['id']}' class='btn-action delete' title='Hapus' onclick='return confirm(\"Yakin hapus produk ini?\")'><i class='fa-solid fa-trash'></i></a>
                    </td>
                  </tr>";
                  $no++;
                }
              } else {
                echo "
                <tr>
                  <td colspan='8'>
                    <div class='empty-state'>
                      <i class='fas fa-box-open'></i>
                      <h5>Tidak Ada Produk</h5>
                      <p>Belum ada produk yang ditambahkan atau sesuai dengan filter.</p>
                    </div>
                  </td>
                </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </form>
    </div>

  </div>

  <script src="../js/bootstrap.bundle.min.js"></script>
  <script>
    // Check all functionality
    document.getElementById("checkAll").addEventListener("click", function() {
      let checkboxes = document.querySelectorAll("input[name='selected_ids[]']");
      checkboxes.forEach(chk => chk.checked = this.checked);
    });

    // Confirm delete
    function confirmDelete() {
      const selected = document.querySelectorAll("input[name='selected_ids[]']:checked").length;
      const action = document.activeElement.value;
      
      if (action === "delete_selected" && selected === 0) {
        alert("Pilih minimal satu produk untuk dihapus!");
        return false;
      }
      
      if (action === "delete_all") {
        return confirm("⚠️ Yakin ingin menghapus SEMUA produk?");
      }
      
      return confirm("Yakin ingin menghapus produk yang dipilih?");
    }

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

    // Animate elements on scroll
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    document.querySelectorAll('.stat-card, .filter-section, .table-container').forEach(el => {
      observer.observe(el);
    });
  </script>
</body>
</html>
