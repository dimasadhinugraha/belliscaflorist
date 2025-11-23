<?php
session_start();
include "../config/db.php";

// 🔒 Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

// Pastikan ada ID yang dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: users.php");
  exit();
}

$id = intval($_GET['id']);

// ✅ Hapus user dari database
$sql = "DELETE FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  header("Location: users.php?success=deleted");
  exit();
} else {
  header("Location: users.php?error=delete_failed");
  exit();
}
?>
