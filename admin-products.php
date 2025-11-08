<?php
session_start();
include "config/db.php";

// 🔒 Cek apakah sudah login
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin-login.php");
  exit();
}

// 🧾 Ambil data produk + nama kategori
$sql = "SELECT produk.*, kategori.nama_kategori 
        FROM produk 
        INNER JOIN kategori ON produk.kategori_id = kategori.id
        ORDER BY produk.id DESC";
$result = $conn->query($sql);

// Cek apakah ada pesan sukses (misal setelah tambah produk)
$alert = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
  $alert = "
  <div class='alert alert-success alert-dismissible fade show' role='alert' style='margin-bottom:1.5rem;'>
    <i class='fa-solid fa-circle-check'></i>
    <strong>Produk berhasil ditambahkan!</strong> Data produk baru telah disimpan ke database.
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
  </div>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Produk - Bellisca Florist Admin</title>

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

    .main-content h3 {
      font-family: "DM Serif Text", serif;
      margin-bottom: 1.5rem;
    }

    .table {
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    }

    thead {
      background: var(--accent);
      color: #fff;
      font-family: "Playfair Display", serif;
    }

    tbody tr:hover {
      background-color: rgba(207, 168, 110, 0.1);
    }

    .btn-action {
      border: none;
      background: transparent;
      font-size: 1.2rem;
      margin: 0 6px;
      color: var(--muted);
      transition: color 0.2s;
      cursor: pointer;
    }

    .btn-action.edit:hover {
      color: #3a7e3a;
    }

    .btn-action.delete:hover {
      color: #b93c3c;
    }

    footer {
      text-align: center;
      margin-top: 3rem;
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

      .table-responsive {
        overflow-x: auto;
      }
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h4>Bellisca Admin</h4>
    <a href="admin-dashboard.php"><i class="fa-solid fa-plus"></i> Tambah Produk</a>
    <a href="admin-products.php" class="active"><i class="fa-solid fa-list"></i> Daftar Produk</a>
    <a href="logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <h3>Daftar Produk</h3>

    <!-- Alert -->
    <?= $alert ?>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
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
              echo "
              <tr>
                <td>{$no}</td>
                <td><img src='{$row['foto']}' alt='Foto Produk' width='60' height='60' style='object-fit:cover; border-radius:8px;'></td>
                <td>" . htmlspecialchars($row['nama_produk']) . "</td>
                <td>" . htmlspecialchars($row['nama_kategori']) . "</td>
                <td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>
                <td>" . htmlspecialchars($row['deskripsi']) . "</td>
                <td>
                  <a href='admin-edit.php?id={$row['id']}' class='btn-action edit' title='Edit'><i class='fa-solid fa-pen-to-square'></i></a>
                  <a href='admin-delete.php?id={$row['id']}' class='btn-action delete' title='Hapus' onclick='return confirm(\"Yakin hapus produk ini?\")'><i class='fa-solid fa-trash'></i></a>
                </td>
              </tr>";
              $no++;
            }
          } else {
            echo "<tr><td colspan='7' class='text-center text-muted py-4'>Belum ada produk</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <footer>
      © 2025 Bellisca Florist Admin Panel
    </footer>
  </div>

  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
