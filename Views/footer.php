<!-- Views/footer.php - Modern footer for GoodZStore -->
<link rel="stylesheet" href="/GoodZStore/Views/css/footer.css">

<footer class="bg-dark text-white pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row">
            <!-- Column 1: Brand -->
            <div class="col-md-3 mb-4">
                <h4 class="fw-bold mb-3">GoodZ<span class="text-warning">Store</span></h4>
                <p class="text-secondary small">
                    Nơi hội tụ những phong cách thời trang đẳng cấp và hiện đại nhất. Chúng tôi cam kết mang đến chất lượng tốt nhất cho bạn.
                </p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white fs-5"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white fs-5"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white fs-5"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold mb-3 text-warning">Liên kết nhanh</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/GoodZStore/Views/Users/index.php" class="text-secondary text-decoration-none hover-text-white">Trang chủ</a></li>
                    <li class="mb-2"><a href="/GoodZStore/Views/Users/products.php" class="text-secondary text-decoration-none hover-text-white">Sản phẩm</a></li>
                    <li class="mb-2"><a href="/GoodZStore/Views/Users/about.php" class="text-secondary text-decoration-none hover-text-white">Giới thiệu</a></li>
                    <li class="mb-2"><a href="/GoodZStore/Views/Users/contact.php" class="text-secondary text-decoration-none hover-text-white">Liên hệ</a></li>
                </ul>
            </div>

            <!-- Column 3: Policy -->
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold mb-3 text-warning">Chính sách</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-text-white">Chính sách đổi trả</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-text-white">Chính sách bảo mật</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-text-white">Điều khoản dịch vụ</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-text-white">Hướng dẫn mua hàng</a></li>
                </ul>
            </div>

            <!-- Column 4: Contact -->
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold mb-3 text-warning">Liên hệ</h5>
                <ul class="list-unstyled text-secondary">
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-warning"></i> 123 Đường Thời Trang, Q.1, TP.HCM</li>
                    <li class="mb-2"><i class="fas fa-envelope me-2 text-warning"></i> support@goodzstore.com</li>
                    <li class="mb-2"><i class="fas fa-phone me-2 text-warning"></i> 0901 234 567</li>
                </ul>
            </div>
        </div>
        
        <hr class="border-secondary my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start text-secondary small">
                &copy; 2025 GoodZStore. All Rights Reserved.
            </div>
            <div class="col-md-6 text-center text-md-end">
                <i class="fab fa-cc-visa text-secondary fs-4 me-2"></i>
                <i class="fab fa-cc-mastercard text-secondary fs-4 me-2"></i>
                <i class="fab fa-cc-paypal text-secondary fs-4"></i>
            </div>
        </div>
    </div>
</footer>

<!-- AI Chat Panel (global) -->
<div id="ai-global-chat" style="display:none;position:fixed;right:20px;bottom:90px;width:340px;height:420px;background:#fff;border-radius:12px;box-shadow:0 6px 24px rgba(0,0,0,.25);z-index:2147483646;overflow:hidden;border:1px solid #e5e7eb;">
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
  <div style="padding:8px 10px;background:#fff;border-top:1px solid #eee;font-size:12px;color:#6b7280;">🤖 AI có thể tư vấn size, gợi ý sản phẩm, và mã giảm giá.</div>
</div>

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

  function sanitizeAiText(text) {
    if (!text) return '';
    try {
      return String(text).replace(/^\s*(AI|Assistant|Bot)\s*:\s*/i, '');
    } catch { return text; }
  }

  async function aiSendMsg() {
    const txt = aiInput.value.trim();
    if (!txt) return;
    aiAppend('Bạn', txt);
    aiInput.value = '';
    aiAppend('AI', '⏳ Đang xử lý...');

    try {
      const metadata = {};

      const res = await fetch('http://127.0.0.1:5000/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: txt, user_id: aiUserId, metadata })
      });
      const j = await res.json();
      const cleaned = sanitizeAiText(j.text || '🤖 Không có phản hồi.');
      aiBox.lastChild.innerHTML = `<strong>AI:</strong> ${cleaned}`;
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
      const cleaned = sanitizeAiText('❌ Không thể kết nối API.');
      aiBox.lastChild.innerHTML = `<strong>AI:</strong> ${cleaned}`;
    }
  }

  if (aiBtn) aiBtn.addEventListener('click', () => aiPanel.style.display = aiPanel.style.display === 'none' ? 'block' : 'none');
  if (aiClose) aiClose.addEventListener('click', () => aiPanel.style.display = 'none');
  if (aiSend) aiSend.addEventListener('click', aiSendMsg);
  if (aiInput) aiInput.addEventListener('keypress', e => { if (e.key === 'Enter') aiSendMsg(); });
});
</script>
</body>
</html>
