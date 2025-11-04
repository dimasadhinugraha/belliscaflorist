<?php
session_start();
include "config/db.php";
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin-login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nama = $_POST['nama_produk'];
  $kategori = $_POST['kategori_id'];
  $harga = $_POST['harga'];
  $deskripsi = $_POST['deskripsi'];

  // Upload foto
  $target_dir = "uploads/";
  $foto = basename($_FILES["foto"]["name"]);
  $target_file = $target_dir . $foto;
  move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);

  $sql = "INSERT INTO produk (nama_produk, kategori_id, harga, deskripsi, foto) VALUES ('$nama','$kategori','$harga','$deskripsi','$target_file')";
  if ($conn->query($sql)) {
    header("Location: admin-products.php");
  } else {
    echo "Gagal menambahkan produk: " . $conn->error;
  }
}
?>

<!-- tampilkan form tambah produk seperti HTML kamu sebelumnya -->
