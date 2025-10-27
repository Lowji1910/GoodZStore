<!-- Views/footer.php - Modern footer for GoodZStore -->
<link rel="stylesheet" href="/GoodZStore/Views/css/footer.css">

<footer>
  <div class="footer-container">
    <!-- Cột 1: Logo + Giới thiệu -->
    <div class="footer-column">
      <div class="footer-logo">GoodZStore</div>
      <p class="footer-description">
        GoodZStore – Nơi mang đến cho bạn phong cách thời trang hiện đại, chất lượng và cá tính.
      </p>
    </div>

    <!-- Cột 2: Liên kết nhanh -->
    <div class="footer-column">
      <h4 class="footer-title">Liên kết nhanh</h4>
      <ul class="footer-list">
        <li><a href="/GoodZStore/Views/Users/index.php">Trang chủ</a></li>
        <li><a href="/GoodZStore/Views/Users/products.php">Sản phẩm</a></li>
        <li><a href="/GoodZStore/Views/Users/about.php">Giới thiệu</a></li>
        <li><a href="/GoodZStore/Views/Users/contact.php">Liên hệ</a></li>
      </ul>
    </div>

    <!-- Cột 3: Hỗ trợ khách hàng -->
    <div class="footer-column">
      <h4 class="footer-title">Hỗ trợ khách hàng</h4>
      <ul class="footer-list">
        <li><a href="#">Chính sách đổi trả</a></li>
        <li><a href="#">Chính sách bảo mật</a></li>
        <li><a href="#">Hướng dẫn mua hàng</a></li>
        <li><a href="#">Phương thức thanh toán</a></li>
      </ul>
    </div>

    <!-- Cột 4: Kết nối & Liên hệ -->
    <div class="footer-column">
      <h4 class="footer-title">Kết nối & Liên hệ</h4>
      <div class="social-links">
        <a href="#"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-tiktok"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
      </div>
      <div class="contact-info">Địa chỉ: 123 Đường Thời Trang, Quận 1, TP.HCM</div>
      <div class="contact-info">Email: support@goodzstore.com</div>
      <div class="contact-info">Hotline: 0901 234 567</div>
    </div>
  </div>

  <div class="footer-bottom">
    © 2025 GoodZStore. All Rights Reserved.
  </div>
</footer>

<!-- 🚀 Chatbot Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const aiBtn = document.getElementById('chatbot-btn');
  const aiPanel = document.getElementById('ai-global-chat');
  const aiClose = document.getElementById('ai-close');
  const aiSend = document.getElementById('ai-send');
  const aiInput = document.getElementById('ai-input');
  const aiBox = document.getElementById('ai-chat-messages');
  const aiExtras = document.getElementById('ai-extras');
  let aiSessionId = null;
  const aiUserId = <?php echo isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'null'; ?>;

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
    aiAppend('AI', '⏳ Đang xử lý...');

    try {
      const res = await fetch('http://127.0.0.1:5000/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: txt, user_id: aiUserId })
      });
      const j = await res.json();
      aiBox.lastChild.innerHTML = `<strong>AI:</strong> ${j.text || '🤖 Không có phản hồi.'}`;
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
    } catch {
      aiBox.lastChild.innerHTML = '<strong>AI:</strong> ❌ Không thể kết nối API.';
    }
  }

  // Event bindings
  if (aiBtn) aiBtn.addEventListener('click', () => aiPanel.style.display = aiPanel.style.display === 'none' ? 'block' : 'none');
  if (aiClose) aiClose.addEventListener('click', () => aiPanel.style.display = 'none');
  if (aiSend) aiSend.addEventListener('click', aiSendMsg);
  if (aiInput) aiInput.addEventListener('keypress', e => { if (e.key === 'Enter') aiSendMsg(); });
});
</script>
