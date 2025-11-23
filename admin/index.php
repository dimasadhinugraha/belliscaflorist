<?php
session_start();

if (isset($_SESSION['admin_id'])) {
  header("Location: dashboard.php");
  exit();
}

include "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Bellisca Florist</title>
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
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    /* Floral background pattern */
    body::before {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      background-image: 
        radial-gradient(circle at 20% 80%, rgba(207, 168, 110, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(207, 168, 110, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(207, 168, 110, 0.05) 0%, transparent 50%);
      pointer-events: none;
    }

    /* Floating floral decorations */
    .floral-decoration {
      position: absolute;
      opacity: 0.15;
      pointer-events: none;
      animation: float 6s ease-in-out infinite;
    }

    .floral-decoration.top-left {
      top: 5%;
      left: 5%;
      font-size: 80px;
      color: var(--accent);
      animation-delay: 0s;
    }

    .floral-decoration.top-right {
      top: 10%;
      right: 8%;
      font-size: 60px;
      color: var(--accent-dark);
      animation-delay: 1s;
    }

    .floral-decoration.bottom-left {
      bottom: 8%;
      left: 10%;
      font-size: 70px;
      color: var(--accent);
      animation-delay: 2s;
    }

    .floral-decoration.bottom-right {
      bottom: 5%;
      right: 5%;
      font-size: 90px;
      color: var(--accent-dark);
      animation-delay: 1.5s;
    }

    @keyframes float {
      0%, 100% {
        transform: translateY(0) rotate(0deg);
      }
      50% {
        transform: translateY(-20px) rotate(5deg);
      }
    }

    .login-container {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 450px;
      padding: 20px;
    }

    .login-box {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      padding: 3rem 2.5rem;
      border-radius: 24px;
      box-shadow: 
        0 20px 60px rgba(207, 168, 110, 0.15),
        0 0 0 1px rgba(207, 168, 110, 0.1);
      position: relative;
      overflow: hidden;
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

    .login-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--accent), var(--accent-dark), var(--accent));
      background-size: 200% 100%;
      animation: shimmer 3s linear infinite;
    }

    @keyframes shimmer {
      0% {
        background-position: -200% 0;
      }
      100% {
        background-position: 200% 0;
      }
    }

    .brand-section {
      text-align: center;
      margin-bottom: 2rem;
    }

    .brand-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 1rem;
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 20px rgba(207, 168, 110, 0.3);
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
        box-shadow: 0 8px 20px rgba(207, 168, 110, 0.3);
      }
      50% {
        transform: scale(1.05);
        box-shadow: 0 12px 30px rgba(207, 168, 110, 0.4);
      }
    }

    .brand-icon i {
      font-size: 36px;
      color: white;
    }

    .login-box h2 {
      text-align: center;
      margin-bottom: 0.5rem;
      font-family: "DM Serif Text", serif;
      font-size: 28px;
      color: var(--muted);
      font-weight: 600;
    }

    .login-subtitle {
      text-align: center;
      font-size: 14px;
      color: var(--muted-light);
      margin-bottom: 2rem;
      font-weight: 300;
    }

    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--muted);
      font-size: 14px;
      transition: color 0.3s ease;
    }

    .input-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted-light);
      font-size: 16px;
      transition: color 0.3s ease;
    }

    .form-control {
      border-radius: 12px;
      padding: 0.875rem 1rem 0.875rem 3rem;
      border: 2px solid #e8dfd5;
      font-size: 15px;
      transition: all 0.3s ease;
      background: white;
      width: 100%;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(207, 168, 110, 0.1);
    }

    .form-control:focus + .input-icon {
      color: var(--accent);
    }

    .password-toggle {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--muted-light);
      cursor: pointer;
      font-size: 16px;
      padding: 4px;
      transition: color 0.3s ease;
    }

    .password-toggle:hover {
      color: var(--accent);
    }

    .btn-accent {
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      color: #fff;
      border-radius: 12px;
      width: 100%;
      padding: 1rem;
      font-weight: 600;
      font-size: 16px;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(207, 168, 110, 0.3);
      position: relative;
      overflow: hidden;
    }

    .btn-accent::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }

    .btn-accent:hover::before {
      width: 300px;
      height: 300px;
    }

    .btn-accent:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(207, 168, 110, 0.4);
    }

    .btn-accent:active {
      transform: translateY(0);
    }

    .btn-accent span {
      position: relative;
      z-index: 1;
    }

    .alert {
      border-radius: 12px;
      margin-bottom: 1.5rem;
      padding: 1rem 1.25rem;
      border: none;
      display: flex;
      align-items: center;
      gap: 12px;
      animation: slideDown 0.4s ease-out;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .alert-danger {
      background: linear-gradient(135deg, #fff5f5, #ffe5e5);
      color: #c53030;
      border-left: 4px solid #c53030;
    }

    .alert-success {
      background: linear-gradient(135deg, #f0fff4, #e5ffe5);
      color: #2f855a;
      border-left: 4px solid #2f855a;
    }

    .alert i {
      font-size: 18px;
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
    }

    .btn-close:hover {
      opacity: 1;
    }

    /* Loading state */
    .btn-accent:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }

    .spinner {
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 0.8s linear infinite;
      margin-left: 8px;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    /* Responsive */
    @media (max-width: 576px) {
      .login-box {
        padding: 2rem 1.5rem;
      }

      .login-box h2 {
        font-size: 24px;
      }

      .brand-icon {
        width: 70px;
        height: 70px;
      }

      .brand-icon i {
        font-size: 32px;
      }

      .floral-decoration {
        font-size: 50px !important;
      }
    }

    /* Remove autofill yellow background */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus {
      -webkit-box-shadow: 0 0 0 1000px white inset;
      -webkit-text-fill-color: var(--muted);
      transition: background-color 5000s ease-in-out 0s;
    }
  </style>
</head>
<body>
  <!-- Floating floral decorations -->
  <div class="floral-decoration top-left">
    <i class="fas fa-spa"></i>
  </div>
  <div class="floral-decoration top-right">
    <i class="fas fa-leaf"></i>
  </div>
  <div class="floral-decoration bottom-left">
    <i class="fas fa-seedling"></i>
  </div>
  <div class="floral-decoration bottom-right">
    <i class="fas fa-spa"></i>
  </div>

  <div class="login-container">
    <div class="login-box">
      <div class="brand-section">
        <div class="brand-icon">
          <i class="fas fa-flower"></i>
        </div>
        <h2>Admin Login</h2>
        <p class="login-subtitle">Bellisca Florist Dashboard</p>
      </div>

      <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
          <i class="fas fa-exclamation-circle"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
        <div class="alert alert-success" role="alert">
          <i class="fas fa-check-circle"></i>
          <span>Anda telah berhasil logout.</span>
          <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'" aria-label="Close">×</button>
        </div>
      <?php endif; ?>

      <form method="POST" id="loginForm">
        <div class="form-group">
          <label for="username" class="form-label">Username</label>
          <div class="input-wrapper">
            <input type="text" name="username" id="username" class="form-control" required autocomplete="username">
            <i class="fas fa-user input-icon"></i>
          </div>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <div class="input-wrapper">
            <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
            <i class="fas fa-lock input-icon"></i>
            <button type="button" class="password-toggle" id="togglePassword">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-accent mt-3" id="loginBtn">
          <span>Login</span>
        </button>
      </form>
    </div>
  </div>

  <script>
    // Password toggle visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
      const type = passwordInput.type === 'password' ? 'text' : 'password';
      passwordInput.type = type;
      
      const icon = this.querySelector('i');
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    });

    // Form submission with loading state
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    
    loginForm.addEventListener('submit', function() {
      loginBtn.disabled = true;
      loginBtn.innerHTML = '<span>Logging in</span><span class="spinner"></span>';
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
