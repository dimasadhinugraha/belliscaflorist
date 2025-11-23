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

  /* Sidebar - EXACT SAME AS products.php */
  .sidebar {
    width: var(--sidebar-width);
    background: linear-gradient(180deg, var(--muted) 0%, #4a3a2d 100%);
    color: #fff;
    flex-shrink: 0;
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    padding: 2rem 0;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    overflow-y: auto;
  }

  .sidebar-header {
    padding: 0 1.5rem 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 2rem;
  }

  .sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 0.5rem;
  }

  .sidebar-brand-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(207, 168, 110, 0.3);
  }

  .sidebar-brand-icon i {
    font-size: 24px;
    color: white;
  }

  .sidebar h4 {
    font-family: "DM Serif Text", serif;
    margin: 0;
    font-size: 20px;
    color: #fffaf5;
  }

  .sidebar-subtitle {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
    padding-left: 1.5rem;
    font-weight: 300;
  }

  .sidebar-nav {
    padding: 0 1rem;
  }

  .sidebar a {
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.875rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 0.5rem;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .sidebar a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--accent);
    transform: scaleY(0);
    transition: transform 0.3s ease;
  }

  .sidebar a:hover, .sidebar a.active {
    background: rgba(207, 168, 110, 0.15);
    color: var(--accent-light);
    transform: translateX(4px);
  }

  .sidebar a.active::before {
    transform: scaleY(1);
  }

  .sidebar a i {
    font-size: 18px;
    width: 24px;
    text-align: center;
  }

  .sidebar .logout {
    margin-top: auto;
    color: #ffdddd;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 2rem;
    padding-top: 1rem;
  }

  .sidebar .logout:hover {
    background: rgba(255, 100, 100, 0.1);
    color: #ff6b6b;
  }
</style>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-brand">
      <div class="sidebar-brand-icon">
        <i class="fas fa-flower"></i>
      </div>
      <div>
        <h4>Bellisca Admin</h4>
      </div>
    </div>
    <p class="sidebar-subtitle">Dashboard Panel</p>
  </div>
  
  <div class="sidebar-nav">
    <?php 
      $current_page = basename($_SERVER['PHP_SELF']);
      $is_dashboard = ($current_page == 'dashboard.php') ? 'active' : '';
      $is_products = ($current_page == 'products.php') ? 'active' : '';
      $is_users = ($current_page == 'users.php') ? 'active' : '';
    ?>
    <a href="dashboard.php" class="<?= $is_dashboard ?>">
      <i class="fa-solid fa-plus"></i>
      <span>Tambah Produk</span>
    </a>
    <a href="products.php" class="<?= $is_products ?>">
      <i class="fa-solid fa-list"></i>
      <span>Daftar Produk</span>
    </a>
    <a href="users.php" class="<?= $is_users ?>">
      <i class="fa-solid fa-users"></i>
      <span>Tambah User</span>
    </a>
  </div>
  
  <div style="padding: 0 1rem;">
    <a href="logout.php" class="logout">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Logout</span>
    </a>
  </div>
</div>

