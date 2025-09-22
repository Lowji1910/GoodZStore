<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!-- Views/header.php - Sticky, minimalist header for GoodZStore -->
<header id="main-header" style="position:sticky;top:0;z-index:1000;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
  <div style="display:flex;align-items:center;justify-content:space-between;max-width:1200px;margin:auto;padding:0 24px;height:70px;">
    <!-- Left: Logo -->
    <div style="font-family:'Segoe UI',sans-serif;font-size:2rem;font-weight:700;color:#2d2e32;letter-spacing:2px;">
      <span style="color:#2d2e32;">GoodZ</span><span style="color:#FFD700;">Store</span>
    </div>
    <!-- Center: Main menu -->
    <nav style="flex:1;text-align:center;">
      <ul style="display:inline-flex;gap:32px;list-style:none;margin:0;padding:0;font-size:1.1rem;font-weight:500;">
        <li><a href="/GoodZStore/Views/Users/index.php" style="color:#222;text-decoration:none;">Trang chủ</a></li>
        <li style="position:relative;">
          <a href="/GoodZStore/Views/Users/categories.php" style="color:#222;text-decoration:none;">Danh mục ▼</a>
          <div class="dropdown-menu" style="display:none;position:absolute;left:0;top:32px;background:#fff;box-shadow:0 2px 8px #eee;border-radius:8px;padding:12px 0;min-width:220px;">
            <a href="/GoodZStore/Views/Users/categories.php?type=aothun" style="display:flex;align-items:center;gap:8px;padding:8px 24px;color:#222;text-decoration:none;">
              <img src="/img/shirt.png" alt="Áo thun" style="width:24px;">Áo thun
            </a>
            <a href="/GoodZStore/Views/Users/categories.php?type=quanjeans" style="display:flex;align-items:center;gap:8px;padding:8px 24px;color:#222;text-decoration:none;">
              <img src="/img/jeans.png" alt="Quần jeans" style="width:24px;">Quần jeans
            </a>
            <a href="/GoodZStore/Views/Users/categories.php?type=aokhoac" style="display:flex;align-items:center;gap:8px;padding:8px 24px;color:#222;text-decoration:none;">
              <img src="/img/jacket.png" alt="Áo khoác" style="width:24px;">Áo khoác
            </a>
            <a href="/GoodZStore/Views/Users/categories.php?type=vaynu" style="display:flex;align-items:center;gap:8px;padding:8px 24px;color:#222;text-decoration:none;">
              <img src="/img/dress.png" alt="Váy nữ" style="width:24px;">Váy nữ
            </a>
            <a href="/GoodZStore/Views/Users/categories.php?type=phukien" style="display:flex;align-items:center;gap:8px;padding:8px 24px;color:#222;text-decoration:none;">
              <img src="/img/accessory.png" alt="Phụ kiện" style="width:24px;">Phụ kiện
            </a>
          </div>
        </li>
  <li><a href="/GoodZStore/Views/Users/promotions.php" style="color:#222;text-decoration:none;">Khuyến mãi</a></li>
  <li><a href="/GoodZStore/Views/Users/contact.php" style="color:#222;text-decoration:none;">Liên hệ</a></li>
  <li><a href="/GoodZStore/Views/Users/about.php" style="color:#222;text-decoration:none;">Giới thiệu</a></li>
      </ul>
    </nav>
    <!-- Right: Quick tools -->
    <div style="display:flex;align-items:center;gap:18px;">
      <!-- Search -->
      <div style="position:relative;">
        <input type="text" placeholder="Tìm kiếm..." style="padding:7px 32px 7px 12px;border-radius:20px;border:1px solid #e0e0e0;width:160px;">
        <span style="position:absolute;right:10px;top:7px;font-size:1.2rem;color:#888;">&#128269;</span>
      </div>
      <!-- Cart -->
      <?php
      require_once __DIR__ . '/../Models/cart_functions.php';
      $cart_count = getCartItemCount();
      ?>
      <a href="/GoodZStore/Views/Users/cart.php" style="position:relative;color:#222;text-decoration:none;font-size:1.5rem;">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-badge" style="position:absolute;top:-8px;right:-10px;background:#ff6f61;color:#fff;font-size:0.9rem;padding:2px 7px;border-radius:12px;">
          <?= $cart_count ?>
        </span>
      </a>
      <!-- Account -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/GoodZStore/Views/Users/profile.php" style="color:#222;text-decoration:none;font-size:1.5rem;">👤</a>
      <?php else: ?>
        <a href="/GoodZStore/Views/Users/login.php" style="color:#222;text-decoration:none;font-size:1.5rem;">Đăng nhập</a>
      <?php endif; ?>
    </div>
  </div>
  <!-- Chatbot button -->
  <button id="chatbot-btn" style="position:fixed;bottom:32px;right:32px;background:#FFD700;color:#222;border:none;border-radius:50%;width:56px;height:56px;box-shadow:0 2px 8px #ccc;font-size:2rem;z-index:999;cursor:pointer;">💬</button>
<link rel="stylesheet" href="/Views/css/layout.css">
</header>
<script>
// Dropdown menu hover
const menuItem = document.querySelector('#main-header nav ul li:nth-child(2)');
const dropdown = menuItem.querySelector('.dropdown-menu');
menuItem.addEventListener('mouseenter',()=>dropdown.style.display='block');
menuItem.addEventListener('mouseleave',()=>dropdown.style.display='none');
</script>
