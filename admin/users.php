<?php
session_start();
include "../config/db.php";

// 🔒 Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

$message = "";

// 🧾 Fungsi tambah user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $email    = trim($_POST['email']);

  // 🔍 Validasi input sederhana
  if ($username === "" || $password === "" || $email === "") {
    $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Semua kolom wajib diisi!</div>";
  } elseif (strlen($password) < 6) {
    $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Password minimal 6 karakter!</div>";
  } else {
    // Cek apakah username atau email sudah terdaftar
    $check = $conn->query("SELECT id FROM admin WHERE username='$username' OR email='$email'");
    if ($check->num_rows > 0) {
      $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Username atau email sudah terdaftar!</div>";
    } else {
      // Hash password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      
      // ✅ Simpan ke database
      $sql = "INSERT INTO admin (username, password, email) VALUES (?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sss", $username, $hashed_password, $email);

      if ($stmt->execute()) {
        $message = "<div class='alert alert-success'><i class='fas fa-circle-check'></i> User berhasil ditambahkan!</div>";
        // Reset form
        $_POST = array();
      } else {
        $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Gagal menambahkan user: " . htmlspecialchars($conn->error) . "</div>";
      }
    }
  }
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as total FROM admin")->fetch_assoc()['total'];
$total_products = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah User - Bellisca Florist Admin</title>
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

    .stat-icon.users {
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      color: white;
    }

    .stat-icon.products {
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
      animation: slideUp 0.6s ease-out 0.2s backwards;
    }

    .form-section h3 {
      font-family: "DM Serif Text", serif;
      font-size: 24px;
      margin-bottom: 1.5rem;
      color: var(--muted);
      padding-bottom: 1rem;
      border-bottom: 2px solid #f5f5f5;
    }

    /* Form Elements */
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--muted);
      font-size: 14px;
    }

    .form-control {
      border: 2px solid #e8dfd5;
      border-radius: 10px;
      padding: 0.75rem 1rem;
      font-size: 14px;
      transition: all 0.3s ease;
      background: white;
      width: 100%;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(207, 168, 110, 0.1);
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

    /* Button */
    .btn-accent {
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      color: white;
      border-radius: 10px;
      padding: 0.875rem 2rem;
      font-weight: 600;
      font-size: 15px;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(207, 168, 110, 0.3);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      justify-content: center;
    }

    .btn-accent:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(207, 168, 110, 0.4);
    }

    .btn-accent:active {
      transform: translateY(0);
    }

    /* Security Tips */
    .security-tips {
      background: #eff6ff;
      border-left: 4px solid #3b82f6;
      border-radius: 10px;
      padding: 1.5rem;
      margin-top: 2rem;
    }

    .security-tips h5 {
      color: #1e40af;
      font-weight: 600;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .security-tips ul {
      list-style: none;
      color: #1e3a8a;
      font-size: 14px;
    }

    .security-tips li {
      margin-bottom: 0.5rem;
    }

    /* Responsive */
    @media (max-width: 992px) {
      .main-content {
        margin-left: 0;
      }

      .stats-grid {
        grid-template-columns: 1fr;
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
    }
  </style>
</head>

<body>

  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="page-header">
      <h3>Tambah User Baru</h3>
      <p>Tambahkan user admin baru ke sistem Bellisca Florist</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon users">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <h4><?= $total_users ?></h4>
          <p>Total User</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon products">
          <i class="fas fa-box"></i>
        </div>
        <div class="stat-content">
          <h4><?= $total_products ?></h4>
          <p>Total Produk</p>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h3><i class="fas fa-user-plus"></i> Form Tambah User</h3>

      <?= $message ?>

      <form method="POST" id="userForm">
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-user"></i> Username</label>
          <input type="text" name="username" class="form-control" placeholder="Masukkan username" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
          <small class="text-muted" style="font-size: 12px; display: block; margin-top: 0.5rem;">
            <i class="fas fa-info-circle"></i> Username untuk login admin
          </small>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
          <input type="email" name="email" class="form-control" placeholder="Masukkan email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>

        <div class="mb-4">
          <label class="form-label"><i class="fas fa-lock"></i> Password</label>
          <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
          <small class="text-muted" style="font-size: 12px; display: block; margin-top: 0.5rem;">
            <i class="fas fa-info-circle"></i> Password minimal 6 karakter
          </small>
        </div>

        <button type="submit" class="btn btn-accent">
          <i class="fas fa-user-plus"></i>
          Tambah User
        </button>
      </form>

      <div class="security-tips">
        <h5><i class="fas fa-lightbulb"></i> Tips Keamanan</h5>
        <ul>
          <li>• Gunakan password yang kuat dan sulit ditebak</li>
          <li>• Hindari menggunakan informasi pribadi dalam password</li>
          <li>• Jangan bagikan password dengan siapapun</li>
          <li>• Setiap user harus memiliki email yang unik</li>
        </ul>
      </div>
    </div>

    <!-- Users Table -->
    <div class="form-section">
      <h3><i class="fas fa-list"></i> Daftar User</h3>

      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background-color: #f5f5f5; border-bottom: 2px solid #e8dfd5;">
              <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--muted); font-size: 14px;">No</th>
              <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--muted); font-size: 14px;">Username</th>
              <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--muted); font-size: 14px;">Email</th>
              <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--muted); font-size: 14px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $users = $conn->query("SELECT id, username, email FROM admin ORDER BY id DESC");
              if ($users && $users->num_rows > 0) {
                $no = 1;
                while ($user = $users->fetch_assoc()) {
                  $username = htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8');
                  $email = htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8');
                  $user_id = intval($user['id'] ?? 0);
                  echo "
                    <tr style='border-bottom: 1px solid #f5f5f5; transition: all 0.3s ease;' onmouseover=\"this.style.backgroundColor='#fffaf5'\" onmouseout=\"this.style.backgroundColor='white'\">
                      <td style='padding: 1rem; color: var(--muted-light); font-size: 14px;'>" . $no++ . "</td>
                      <td style='padding: 1rem; color: var(--muted); font-weight: 500; font-size: 14px;'>" . $username . "</td>
                      <td style='padding: 1rem; color: var(--muted-light); font-size: 14px;'>" . $email . "</td>
                      <td style='padding: 1rem; text-align: center;'>
                        <a href='edit-user.php?id=" . $user_id . "' style='display: inline-flex; align-items: center; gap: 6px; padding: 0.5rem 1rem; background: #e3f2fd; color: #1976d2; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.3s ease; margin-right: 0.5rem;' onmouseover=\"this.style.background='#bbdefb'\" onmouseout=\"this.style.background='#e3f2fd'\">
                          <i class='fas fa-pen'></i> Edit
                        </a>
                        <a href='#' onclick=\"if(confirm('Hapus user ini?')) { window.location.href='hapus-user.php?id=" . $user_id . "'; } return false;\" style='display: inline-flex; align-items: center; gap: 6px; padding: 0.5rem 1rem; background: #ffebee; color: #d32f2f; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.3s ease;' onmouseover=\"this.style.background='#ffcdd2'\" onmouseout=\"this.style.background='#ffebee'\">
                          <i class='fas fa-trash'></i> Hapus
                        </a>
                      </td>
                    </tr>
                  ";
                }
              } else {
                echo "<tr><td colspan='4' style='padding: 2rem; text-align: center; color: var(--muted-light);'>Tidak ada user</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    // Form validation
    document.getElementById('userForm').addEventListener('submit', function(e) {
      const password = document.querySelector('input[name="password"]').value;
      if (password.length < 6) {
        e.preventDefault();
        alert('Password minimal 6 karakter!');
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
