<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!-- Views/header.php -->
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

    <!-- Right: Tools -->
    <div class="quick-tools">
      <div class="search-box">
        <input type="text" placeholder="Tìm kiếm..." class="search-input">
        <span class="search-icon">&#128269;</span>
      </div>

      <?php
      require_once __DIR__ . '/../Models/cart_functions.php';
      $cart_count = getCartItemCount();
      ?>
      <a href="/GoodZStore/Views/Users/cart.php" class="cart-link">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-badge"><?= $cart_count ?></span>
      </a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/GoodZStore/Views/Users/profile.php" class="user-link">👤</a>
      <?php else: ?>
        <a href="/GoodZStore/Views/Users/auth.php" class="user-link">Đăng nhập</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Chatbot Button -->
  <button id="chatbot-btn"
    style="position:fixed;right:20px;bottom:20px;width:56px;height:56px;border-radius:50%;background:#2563eb;color:#fff;border:none;box-shadow:0 8px 20px rgba(0,0,0,.25);cursor:pointer;z-index:2147483647;display:flex;align-items:center;justify-content:center;font-size:22px;">
    💬
  </button>

  <!-- Chat Panel -->
  <div id="ai-global-chat"
    style="display:none;position:fixed;right:20px;bottom:90px;width:340px;height:420px;background:#fff;border-radius:12px;box-shadow:0 6px 24px rgba(0,0,0,.25);z-index:2147483646;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#111827;color:#fff;">
      <div style="font-weight:600;">GoodZ AI</div>
      <button id="ai-close" style="background:transparent;border:none;color:#fff;font-size:18px;cursor:pointer;">×</button>
    </div>
    <div id="ai-chat-messages" style="height:300px;overflow-y:auto;padding:10px;background:#f9fafb;"></div>
    <div style="padding:10px;border-top:1px solid #eee;background:#fff;display:flex;gap:6px;">
      <input id="ai-input" type="text" placeholder="Hỏi trợ lý thời trang..." style="flex:1;padding:8px;border:1px solid #ddd;border-radius:8px;">
      <button id="ai-send" style="background:#2563eb;color:#fff;border:none;padding:8px 12px;border-radius:8px;cursor:pointer;">Gửi</button>
    </div>
    <div id="ai-extras" style="max-height:160px;overflow:auto;padding:10px;background:#fff;border-top:1px solid #eee;display:none;"></div>
    <div style="padding:8px 10px;background:#fff;border-top:1px solid #eee;font-size:12px;color:#6b7280;">
      🤖 AI có thể tư vấn size, gợi ý sản phẩm, và mã giảm giá.
    </div>
  </div>

  <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
  <link rel="stylesheet" href="/GoodZStore/Views/css/header.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-6m0p1Y2e0eYwq3M0k6Jz5x1Q9aYQ3h5jQ2k1rH8Q9D2kF6z7Gx9V2k1Q6Zp7j2bA" crossorigin="anonymous">
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Hover menu
  const menuItem = document.querySelector('#main-header nav ul li:nth-child(2)');
  const dropdown = menuItem.querySelector('.dropdown-menu');
  menuItem.addEventListener('mouseenter', () => dropdown.style.display = 'block');
  menuItem.addEventListener('mouseleave', () => dropdown.style.display = 'none');

  // Search redirect
  const searchInput = document.querySelector('#main-header .search-input');
  if (searchInput) {
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        const q = encodeURIComponent(this.value.trim());
        const base = '/GoodZStore/Views/Users/products.php';
        window.location.href = q ? `${base}?q=${q}` : base;
      }
    });
  }

  // Chatbot setup
  const aiBtn = document.getElementById('chatbot-btn');
  const aiPanel = document.getElementById('ai-global-chat');
  const aiClose = document.getElementById('ai-close');
  const aiSend = document.getElementById('ai-send');
  const aiInput = document.getElementById('ai-input');
  const aiBox = document.getElementById('ai-chat-messages');
  const aiExtras = document.getElementById('ai-extras');
  let aiSessionId = null;
  const aiUserId = <?php echo isset($_SESSION['user']['id']) ? intval($_SESSION['user']['id']) : (isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'null'); ?>;

  function aiAppend(who, text) {
    const d = document.createElement('div');
    d.style.margin = '6px 0';
    d.innerHTML = `<strong>${who}:</strong> <span>${text}</span>`;
    aiBox.appendChild(d);
    aiBox.scrollTop = aiBox.scrollHeight;
  }

  function aiAppendHTML(html) {
    aiExtras.style.display = 'block';
    const d = document.createElement('div');
    d.style.margin = '6px 0';
    d.innerHTML = html;
    aiExtras.appendChild(d);
  }

  async function aiSendMsg() {
    const txt = aiInput.value.trim();
    if (!txt) return;
    aiAppend('Bạn', txt);
    aiInput.value = '';
    const loadingId = `ld-${Date.now()}`;
    aiAppend('AI', `<span id="${loadingId}">Đang suy nghĩ...</span>`);
    try {
      const payload = { message: txt, user_id: aiUserId, session_id: aiSessionId, metadata: {} };
      const res = await fetch('http://127.0.0.1:5000/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const j = await res.json();
      aiSessionId = j.session_id || aiSessionId;
      const el = document.getElementById(loadingId);
      if (el) el.parentElement.remove();
      aiAppend('AI', j.text || '');
      if (j.size_suggestion && j.size_suggestion.size) {
        aiAppendHTML(`<div><b>📏 Gợi ý size:</b> ${j.size_suggestion.size}<br><small>${j.size_suggestion.reason || ''}</small></div>`);
      }
      if (j.recommendations && j.recommendations.length) {
        aiAppendHTML('<div><b>🛍️ Gợi ý:</b><ul>' +
          j.recommendations.map(r => `<li><a href="/GoodZStore/Views/Users/product.php?id=${r.id}">${r.name} - ${parseInt(r.price).toLocaleString('vi-VN')}đ</a></li>`).join('') +
          '</ul></div>');
      }
      if (j.vouchers && j.vouchers.length) {
        aiAppendHTML('<div><b>🎟️ Voucher:</b><ul>' +
          j.vouchers.map(v => {
            const disc = v.discount_type === 'percentage' ? `${v.discount_value}%` : `${parseInt(v.discount_value).toLocaleString('vi-VN')}đ`;
            const min = v.min_order_amount > 0 ? ` (tối thiểu ${parseInt(v.min_order_amount).toLocaleString('vi-VN')}đ)` : '';
            return `<li><code>${v.code}</code> - Giảm ${disc}${min}</li>`;
          }).join('') + '</ul></div>');
      }
    } catch (e) {
      const el = document.getElementById(loadingId);
      if (el) el.parentElement.remove();
      aiAppend('AI', '❌ Xin lỗi, tôi gặp sự cố kết nối.');
    }
  }

  // Event bindings
  aiBtn.addEventListener('click', () => {
    aiPanel.style.display = aiPanel.style.display === 'none' ? 'block' : 'none';
  });
  aiClose.addEventListener('click', () => aiPanel.style.display = 'none');
  aiSend.addEventListener('click', aiSendMsg);
  aiInput.addEventListener('keypress', e => {
    if (e.key === 'Enter') aiSendMsg();
  });
});
</script>
<!-- AI_WIDGET_DEBUG: v1 -->
<?php echo "<!-- HEADER_PATH: " . __FILE__ . " -->"; ?>