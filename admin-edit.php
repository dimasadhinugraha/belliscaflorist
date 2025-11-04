<?php
session_start();
include "config/db.php";
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin-login.php");
  exit();
}

$id = $_GET['id'];
$data = $conn->query("SELECT * FROM produk WHERE id=$id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nama = $_POST['nama_produk'];
  $kategori = $_POST['kategori_id'];
  $harga = $_POST['harga'];
  $deskripsi = $_POST['deskripsi'];

  if (!empty($_FILES['foto']['name'])) {
    $foto = "uploads/" . basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
    $conn->query("UPDATE produk SET nama_produk='$nama', kategori_id='$kategori', harga='$harga', deskripsi='$deskripsi', foto='$foto' WHERE id=$id");
  } else {
    $conn->query("UPDATE produk SET nama_produk='$nama', kategori_id='$kategori', harga='$harga', deskripsi='$deskripsi' WHERE id=$id");
  }

  header("Location: admin-products.php");
  exit();
}
?>

<!-- tampilkan form seperti admin-edit.html dengan value dari $data -->
