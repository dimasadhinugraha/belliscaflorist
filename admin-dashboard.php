<?php
session_start();
include "config/db.php";

// 🔒 Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin-login.php");
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
    // 🖼️ Upload foto
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
      mkdir($target_dir, 0755, true);
    }

    $foto_name = basename($_FILES["foto"]["name"]);
    $foto_tmp  = $_FILES["foto"]["tmp_name"];
    $foto_type = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($foto_type, $allowed_types)) {
      $message = "<div class='alert alert-danger'>Hanya boleh upload file gambar (JPG, JPEG, PNG, GIF)!</div>";
    } else {
      $unique_name = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $foto_name);
      $target_file = $target_dir . $unique_name;

      if (move_uploaded_file($foto_tmp, $target_file)) {
        // ✅ Simpan ke database
        $sql = "INSERT INTO produk (nama_produk, kategori_id, harga, deskripsi, foto)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sidss", $nama, $kategori_id, $harga, $deskripsi, $target_file);

        if ($stmt->execute()) {
          header("Location: admin-products.php?success=1");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Bellisca Florist</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --accent: #cfa86e;
      --muted: #5d4b3b;
      --bg: #fffaf5;
    }

    body {
      background: var(--bg);
      font-family: 'Radley', serif;
      color: var(--muted);
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background: var(--muted);
      color: #fff;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      padding: 2rem 1rem;
    }

    .sidebar h4 {
      font-family: "DM Serif Text", serif;
      text-align: center;
      margin-bottom: 2rem;
      color: #fffaf5;
    }

    .sidebar a {
      color: rgba(255,255,255,0.9);
      text-decoration: none;
      display: block;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      margin-bottom: 0.5rem;
      font-size: 1.05rem;
      transition: all 0.3s ease;
    }

    .sidebar a:hover, .sidebar a.active {
      background: var(--accent);
      color: #fff;
      transform: translateX(5px);
    }

    .sidebar .logout {
      margin-top: auto;
      color: #ffdddd;
    }

    /* Main Content */
    .main-content {
      margin-left: 250px;
      flex-grow: 1;
      padding: 2rem;
    }

    .form-section {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.05);
      padding: 2.5rem;
    }

    .form-section h3 {
      font-family: "DM Serif Text", serif;
      margin-bottom: 1.5rem;
    }

    .btn-accent {
      background: var(--accent);
      color: #fff;
      border-radius: 8px;
      padding: 0.8rem 1.5rem;
      transition: all .3s ease;
    }

    .btn-accent:hover {
      background: #b98f56;
    }

    footer {
      text-align: center;
      margin-top: 4rem;
      padding: 1rem;
      background: var(--muted);
      color: #fff;
      border-radius: 12px;
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        flex-direction: row;
        justify-content: space-around;
        padding: 1rem 0.5rem;
      }

      .main-content {
        margin-left: 0;
        padding-top: 2rem;
      }
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h4>Bellisca Admin</h4>
    <a href="admin-dashboard.php" class="active"><i class="fa-solid fa-plus"></i> Tambah Produk</a>
    <a href="admin-products.php"><i class="fa-solid fa-list"></i> Daftar Produk</a>
    <a href="logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="form-section">
      <h3>Tambah Produk Baru</h3>

      <?= $message ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Nama Produk</label>
          <input type="text" name="nama_produk" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Harga (Rp)</label>
          <input type="number" name="harga" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Kategori</label>
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
          <label class="form-label">Deskripsi Produk</label>
          <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Foto Produk</label>
          <input type="file" name="foto" class="form-control" accept="image/*" required>
        </div>

        <button type="submit" class="btn btn-accent">Tambah Produk</button>
      </form>
    </div>
  </div>
</body>
</html>
