<?php
session_start();
include "config/db.php";

// 🔒 Cek login admin
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin-login.php");
  exit();
}

// Pastikan ada parameter id produk
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: admin-products.php");
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

// 🛠 Update produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama        = trim($_POST['nama_produk']);
  $harga       = floatval($_POST['harga']);
  $kategori_id = intval($_POST['kategori_id']);
  $deskripsi   = trim($_POST['deskripsi']);
  $foto_lama   = $produk['foto'];

  // Upload foto baru (jika ada)
  $target_dir = "uploads/";
  if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

  if (!empty($_FILES['foto']['name'])) {
    $foto_name = basename($_FILES['foto']['name']);
    $foto_tmp  = $_FILES['foto']['tmp_name'];
    $foto_type = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($foto_type, $allowed_types)) {
      $unique_name = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $foto_name);
      $target_file = $target_dir . $unique_name;
      if (move_uploaded_file($foto_tmp, $target_file)) {
        // hapus foto lama jika ada
        if (file_exists($foto_lama) && $foto_lama !== "") unlink($foto_lama);
        $foto_path = $target_file;
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

  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root { --accent: #cfa86e; --muted: #5d4b3b; --bg: #fffaf5; }
    body { background: var(--bg); font-family: 'Radley', serif; color: var(--muted); display: flex; min-height: 100vh; }

    .sidebar {
      width: 250px; background: var(--muted); color: #fff;
      flex-shrink: 0; display: flex; flex-direction: column;
      position: fixed; top: 0; bottom: 0; left: 0; padding: 2rem 1rem;
    }
    .sidebar h4 { font-family: "DM Serif Text", serif; text-align: center; margin-bottom: 2rem; color: #fffaf5; }
    .sidebar a { color: rgba(255,255,255,0.9); text-decoration: none; display: block; padding: 0.75rem 1rem;
      border-radius: 8px; margin-bottom: 0.5rem; font-size: 1.05rem; transition: all 0.3s ease; }
    .sidebar a:hover, .sidebar a.active { background: var(--accent); color: #fff; transform: translateX(5px); }
    .sidebar .logout { margin-top: auto; color: #ffdddd; }

    .main-content { margin-left: 250px; flex-grow: 1; padding: 2rem; }
    .form-section { background: #fff; border-radius: 16px; box-shadow: 0 6px 18px rgba(0,0,0,0.05); padding: 2.5rem;
      max-width: 800px; margin: auto; }
    .form-section h3 { font-family: "DM Serif Text", serif; margin-bottom: 1.5rem; text-align: center; }

    .product-preview { text-align: center; margin-bottom: 1.5rem; }
    .product-preview img { max-width: 160px; height: 160px; object-fit: cover; border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

    .btn-accent { background: var(--accent); color: #fff; border-radius: 8px; padding: 0.8rem 1.5rem; transition: all .3s ease; }
    .btn-accent:hover { background: #b98f56; }
    .btn-secondary { border: 1px solid var(--accent); color: var(--accent); border-radius: 8px;
      padding: 0.8rem 1.5rem; transition: all .3s ease; }
    .btn-secondary:hover { background: var(--accent); color: #fff; }

    footer { text-align: center; margin-top: 4rem; padding: 1rem; background: var(--muted); color: #fff; border-radius: 12px; }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h4>Bellisca Admin</h4>
    <a href="admin-dashboard.php"><i class="fa-solid fa-plus"></i> Tambah Produk</a>
    <a href="admin-products.php"><i class="fa-solid fa-list"></i> Daftar Produk</a>
    <a href="#" class="active"><i class="fa-solid fa-pen-to-square"></i> Edit Produk</a>
    <a href="logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="form-section">
      <h3>Edit Produk</h3>
      <?= $message ?>

      <!-- Preview Produk -->
      <div class="product-preview">
        <img src="<?= htmlspecialchars($produk['foto']) ?>" alt="Preview Produk">
        <p class="mt-2 text-muted">Foto produk saat ini</p>
      </div>

      <!-- Form Edit -->
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Nama Produk</label>
          <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Harga (Rp)</label>
          <input type="number" name="harga" class="form-control" value="<?= htmlspecialchars($produk['harga']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Kategori</label>
          <select name="kategori_id" class="form-select" required>
            <option value="">Pilih Kategori</option>
            <?php
              $kategori = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
              while ($row = $kategori->fetch_assoc()) {
                $selected = ($row['id'] == $produk['kategori_id']) ? "selected" : "";
                echo "<option value='{$row['id']}' {$selected}>{$row['nama_kategori']}</option>";
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Deskripsi Produk</label>
          <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Ganti Foto Produk</label>
          <input type="file" name="foto" class="form-control" accept="image/*">
        </div>

        <div class="d-flex justify-content-between mt-4">
          <a href="admin-products.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
          <button type="submit" class="btn btn-accent"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
