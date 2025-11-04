<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $query = $conn->prepare("SELECT * FROM admin WHERE username = ?");
  $query->bind_param("s", $username);
  $query->execute();
  $result = $query->get_result();

  if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();

    // Jika belum pakai hash, cukup bandingkan langsung
    if ($password === $admin['password']) {
      $_SESSION['admin_id'] = $admin['id'];
      $_SESSION['admin_name'] = $admin['nama_lengkap'];
      header("Location: admin-products.php");
      exit();
    } else {
      $error = "Password salah!";
    }
  } else {
    $error = "Akun tidak ditemukan!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
  <form method="POST" class="p-4 bg-white shadow rounded" style="width: 360px;">
    <h3 class="text-center mb-4">Admin Login</h3>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <div class="mb-3">
      <label>Username</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-warning w-100" type="submit">Login</button>
  </form>
</body>
</html>
