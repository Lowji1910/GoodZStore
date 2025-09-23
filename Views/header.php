<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!-- Views/header.php - Sticky, minimalist header for GoodZStore -->
<header id="main-header">
  <div class="header-container">
    <!-- Left: Logo -->
    <div class="logo">
      <span class="logo-text">GoodZ</span><span class="logo-highlight">Store</span>
    </div>
    <!-- Center: Main menu -->
    <nav class="main-nav">
      <ul class="nav-list">
        <li><a href="/GoodZStore/Views/Users/index.php">Trang chủ</a></li>
        <li>
          <a href="/GoodZStore/Views/Users/products.php">Danh mục ▼</a>
          <div class="dropdown-menu">
            <?php
            require_once __DIR__ . '/../Models/db.php';
            $sql = "SELECT * FROM categories ORDER BY name";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0):
                while ($category = $result->fetch_assoc()): ?>
                    <a href="/GoodZStore/Views/Users/category.php?id=<?= $category['id'] ?>">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                <?php endwhile;
            endif; ?>
          </div>
        </li>
        <li><a href="/GoodZStore/Views/Users/products.php">Sản phẩm</a></li>
        <li><a href="/GoodZStore/Views/Users/contact.php">Liên hệ</a></li>
        <li><a href="/GoodZStore/Views/Users/about.php">Giới thiệu</a></li>
      </ul>
    </nav>
    <!-- Right: Quick tools -->
    <div class="quick-tools">
      <!-- Search -->
      <div class="search-box">
        <input type="text" placeholder="Tìm kiếm..." class="search-input">
        <span class="search-icon">&#128269;</span>
      </div>
      <!-- Cart -->
      <?php
      require_once __DIR__ . '/../Models/cart_functions.php';
      $cart_count = getCartItemCount();
      ?>
      <a href="/GoodZStore/Views/Users/cart.php" class="cart-link">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-badge">
          <?= $cart_count ?>
        </span>
      </a>
      <!-- Account -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/GoodZStore/Views/Users/profile.php" class="user-link">👤</a>
      <?php else: ?>
        <a href="/GoodZStore/Views/Users/auth.php" class="user-link">Đăng nhập</a>
      <?php endif; ?>
    </div>
  </div>
  <!-- Chatbot button -->
  <button id="chatbot-btn">💬</button>
  <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
  <link rel="stylesheet" href="/GoodZStore/Views/css/header.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</header>
<script>
// Dropdown menu hover
const menuItem = document.querySelector('#main-header nav ul li:nth-child(2)');
const dropdown = menuItem.querySelector('.dropdown-menu');
menuItem.addEventListener('mouseenter',()=>dropdown.style.display='block');
menuItem.addEventListener('mouseleave',()=>dropdown.style.display='none');

// Header search: redirect to products.php?q=...
const searchInput = document.querySelector('#main-header .search-input');
if (searchInput) {
  searchInput.addEventListener('keydown', function(e){
    if (e.key === 'Enter') {
      const q = encodeURIComponent(this.value.trim());
      const base = '/GoodZStore/Views/Users/products.php';
      window.location.href = q ? `${base}?q=${q}` : base;
    }
  });
}
</script>
