<?php
session_start();
include "config/db.php";

// 🔒 Cek login admin
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin-login.php");
  exit();
}

// Cek apakah ada parameter id
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: admin-products.php");
  exit();
}

$id = intval($_GET['id']);

// Ambil data produk (untuk hapus foto juga)
$sql = "SELECT foto FROM produk WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  header("Location: admin-products.php?error=notfound");
  exit();
}

$produk = $result->fetch_assoc();

// Hapus produk
$delete = "DELETE FROM produk WHERE id = ?";
$stmt = $conn->prepare($delete);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  // Hapus file foto dari server (jika ada)
  if (!empty($produk['foto']) && file_exists($produk['foto'])) {
    unlink($produk['foto']);
  }

  header("Location: admin-products.php?deleted=1");
  exit();
} else {
  header("Location: admin-products.php?error=1");
  exit();
}
?>
