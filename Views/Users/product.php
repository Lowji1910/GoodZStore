
<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/notifications.php';

// Lấy thông tin sản phẩm từ database
$product_id = $_GET['id'] ?? 0;
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: index.php');
    exit;
}

// Lấy ảnh sản phẩm
$sql = "SELECT * FROM product_images WHERE product_id = ? AND is_main = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();

// Lấy danh sách size nếu có
$sizes = [];
$sqlSizes = "SELECT * FROM product_sizes WHERE product_id = ? ORDER BY size_name";
$stmtSizes = $conn->prepare($sqlSizes);
if ($stmtSizes) {
    $stmtSizes->bind_param("i", $product_id);
    $stmtSizes->execute();
    $resultSizes = $stmtSizes->get_result();
    while ($sizeRow = $resultSizes->fetch_assoc()) {
        $sizes[] = $sizeRow;
    }
}

// Lấy sản phẩm liên quan (cùng danh mục, khác id hiện tại)
$related = [];
if (!empty($product['category_id'])) {
    $rel_sql = "SELECT p.id, p.name, p.price, i.image_url
                FROM products p
                LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1
                WHERE p.category_id = ? AND p.id <> ?
                ORDER BY p.created_at DESC
                LIMIT 4";
    $stmt = $conn->prepare($rel_sql);
    $stmt->bind_param("ii", $product['category_id'], $product['id']);
    $stmt->execute();
    $related = $stmt->get_result();
}

// Xử lý thêm review khi người dùng gửi biểu mẫu
$review_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_review') {
    if (!isset($_SESSION['user'])) {
        $review_msg = 'Bạn cần đăng nhập để đánh giá.';
    } else {
        $rating = intval($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5) {
            $review_msg = 'Rating phải từ 1 đến 5.';
        } else {
            $uid = intval($_SESSION['user']['id']);
            $pid = intval($product_id);
            $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('iiis', $uid, $pid, $rating, $comment);
                if ($stmt->execute()) {
                    $review_msg = 'Gửi đánh giá thành công!';
                    // Notify admin about new review
                    if (function_exists('add_notification')) {
                        $pname = $product['name'] ?? ('Sản phẩm #' . $product_id);
                        $uname = $_SESSION['user']['full_name'] ?? 'Người dùng';
                        $msg = $uname . ' đã đánh giá ' . $pname . ' (' . $rating . '★)';
                        $link = '/GoodZStore/Views/Admins/admin_reviews.php';
                        add_notification('Review', $msg, $link);
                    }
                } else {
                    $review_msg = 'Lỗi khi lưu đánh giá: ' . htmlspecialchars($stmt->error);
                }
            } else {
                $review_msg = 'Không thể tạo câu lệnh lưu đánh giá.';
            }
        }
    }
}

include_once __DIR__ . '/../header.php';
?>
<main>
    <div class="product-detail">
        <div class="product-image">
            <img src="/GoodZStore/uploads/<?= $image ? htmlspecialchars($image['image_url']) : 'no-image.jpg' ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 id="mainImg" class="product-img" onclick="zoomImage(this)">
        </div>
        <div class="product-info">
            <h2 class="product-name"><?= htmlspecialchars($product['name']) ?></h2>
            <p class="product-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</p>
            <p class="product-desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <form method="post" action="cart.php" class="product-options">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <?php if (!empty($sizes)): ?>
                    <div class="size-selection">
                        <label class="form-label">Chọn kích thước:</label>
                        <div class="size-options">
                            <?php foreach ($sizes as $size): ?>
                                <label class="size-option <?= $size['stock_quantity'] <= 0 ? 'out-of-stock' : '' ?>">
                                    <input type="radio" name="size_id" value="<?= $size['id'] ?>" 
                                           data-stock="<?= $size['stock_quantity'] ?>"
                                           <?= $size['stock_quantity'] <= 0 ? 'disabled' : '' ?>
                                           <?= empty($_POST) ? ($size === reset($sizes) && $size['stock_quantity'] > 0 ? 'checked' : '') : '' ?>
                                           required>
                                    <span class="size-label"><?= htmlspecialchars($size['size_name']) ?></span>
                                    <?php if ($size['stock_quantity'] <= 0): ?>
                                        <span class="size-status">Hết hàng</span>
                                    <?php elseif ($size['stock_quantity'] <= 5): ?>
                                        <span class="size-stock">(Còn <?= $size['stock_quantity'] ?>)</span>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="quantity-selection">
                    <label class="form-label">Số lượng:</label>
                    <div class="quantity-input">
                        <button type="button" class="qty-btn" onclick="decreaseQty()">−</button>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" 
                               max="<?= !empty($sizes) ? reset($sizes)['stock_quantity'] : $product['stock_quantity'] ?>" 
                               class="form-control" required>
                        <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-add-cart">
                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                </button>
            </form>
        </div>
    </div>
    <section class="related-products">
        <h3>Sản phẩm liên quan</h3>
        <div class="product-list">
            <?php if ($related && $related->num_rows > 0):
                while ($rp = $related->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="/GoodZStore/uploads/<?= htmlspecialchars($rp['image_url'] ?? 'no-image.jpg') ?>" alt="<?= htmlspecialchars($rp['name']) ?>" class="product-img">
                    <div class="product-name"><?= htmlspecialchars($rp['name']) ?></div>
                    <div class="product-price"><?= number_format($rp['price'], 0, ',', '.') ?>đ</div>
                    <a href="product.php?id=<?= $rp['id'] ?>" class="btn">Xem chi tiết</a>
                </div>
            <?php endwhile; else: ?>
                <div>Chưa có sản phẩm liên quan.</div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- AI Chatbox -->
    <section class="ai-chatbox-section">
        <h3>Trợ lý AI - Tư vấn thời trang</h3>
        <div id="ai-chat" class="ai-chat-container">
            <div id="chatBox" class="chat-messages"></div>
            <div class="chat-input-wrapper">
                <input id="chatInput" type="text" placeholder="Hỏi về size, phối đồ, khuyến mãi..." class="chat-input" />
                <button id="sendBtn" class="chat-send-btn">
                    <i class="fas fa-paper-plane"></i> Gửi
                </button>
            </div>
        </div>
    </section>
    
    <?php
    // Lấy review thực tế từ DB
    $rev_sql = "SELECT r.rating, r.comment, r.created_at, u.full_name
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($rev_sql);
    $stmt->bind_param("i", $product['id']);
    $stmt->execute();
    $reviews = $stmt->get_result();
    ?>
    <section class="reviews">
        <h3>Đánh giá & Bình luận</h3>
        <?php if ($review_msg): ?>
            <div class="alert <?=strpos($review_msg,'thành công')!==false ? 'alert-success' : 'alert-warning' ?>" style="max-width:720px;"><?= $review_msg ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['user'])): ?>
            <form method="post" class="mb-4" style="max-width:720px;">
                <input type="hidden" name="action" value="add_review">
                <div class="mb-2">
                    <label class="form-label">Chọn rating</label>
                    <select name="rating" class="form-select" required style="max-width:140px;">
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Bình luận</label>
                    <textarea name="comment" rows="3" class="form-control" placeholder="Chia sẻ cảm nhận của bạn..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
            </form>
        <?php else: ?>
            <div class="alert alert-info" style="max-width:720px;">Hãy <a href="/GoodZStore/Views/Users/login.php">đăng nhập</a> để đánh giá sản phẩm.</div>
        <?php endif; ?>
        <div class="review-list">
            <?php if ($reviews && $reviews->num_rows > 0):
                while ($rv = $reviews->fetch_assoc()): ?>
            <div class="review-item">
                <strong><?= htmlspecialchars($rv['full_name'] ?? 'Người dùng') ?></strong>
                <span><?= str_repeat('★', max(1, (int)$rv['rating'])) ?></span>
                <p><?= nl2br(htmlspecialchars($rv['comment'] ?? '')) ?></p>
                <small><?= htmlspecialchars($rv['created_at']) ?></small>
            </div>
            <?php endwhile; else: ?>
                <div>Chưa có đánh giá cho sản phẩm này.</div>
            <?php endif; ?>
        </div>
    </section>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/product.css">
<script src="../ui.js"></script>
<script>
// Quantity control
function increaseQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    const min = parseInt(input.min);
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}

// Update max quantity when size changes
const sizeInputs = document.querySelectorAll('input[name="size_id"]');
const qtyInput = document.getElementById('quantity');

sizeInputs.forEach(input => {
    input.addEventListener('change', function() {
        const stock = parseInt(this.dataset.stock);
        qtyInput.max = stock;
        if (parseInt(qtyInput.value) > stock) {
            qtyInput.value = stock;
        }
    });
});

// AI Chatbox functionality
const productId = <?= $product['id'] ?>;
const userId = <?= isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 'null' ?>;
let sessionId = null;

document.getElementById('sendBtn').addEventListener('click', sendMessage);
document.getElementById('chatInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

async function sendMessage() {
    const input = document.getElementById('chatInput');
    const txt = input.value.trim();
    if (!txt) return;
    
    appendToChat('Bạn', txt, 'user');
    input.value = '';
    
    // Show loading indicator
    const loadingId = appendToChat('AI', 'Đang suy nghĩ...', 'bot', true);
    
    try {
        const payload = {
            message: txt,
            user_id: userId,
            session_id: sessionId,
            metadata: { 
                product_id: productId
            }
        };
        
        const res = await fetch('http://127.0.0.1:5000/api/chat', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        
        if (!res.ok) {
            throw new Error('Lỗi kết nối server AI');
        }
        
        const data = await res.json();
        sessionId = data.session_id || sessionId;
        
        // Remove loading message
        const loadingMsg = document.getElementById(loadingId);
        if (loadingMsg) loadingMsg.remove();
        
        // Add AI response
        appendToChat('AI', data.text, 'bot');
        
        // Show size suggestion if available
        if (data.size_suggestion && data.size_suggestion.size) {
            const sizeHtml = `<div class="size-suggestion">
                <strong>📏 Gợi ý size:</strong> ${data.size_suggestion.size}
                <br><small>${data.size_suggestion.reason}</small>
            </div>`;
            appendHtmlToChat(sizeHtml);
        }
        
        // Show recommendations if available
        if (data.recommendations && data.recommendations.length > 0) {
            let recsHtml = '<div class="chat-recommendations"><strong>🛍️ Gợi ý sản phẩm:</strong><ul>';
            data.recommendations.forEach(r => {
                recsHtml += `<li><a href="product.php?id=${r.id}">${r.name} - ${parseInt(r.price).toLocaleString('vi-VN')}đ</a></li>`;
            });
            recsHtml += '</ul></div>';
            appendHtmlToChat(recsHtml);
        }
        
        // Show vouchers if available
        if (data.vouchers && data.vouchers.length > 0) {
            let voucherHtml = '<div class="chat-vouchers"><strong>🎟️ Mã giảm giá:</strong><ul>';
            data.vouchers.forEach(v => {
                const discount = v.discount_type === 'percentage' 
                    ? `${v.discount_value}%` 
                    : `${parseInt(v.discount_value).toLocaleString('vi-VN')}đ`;
                voucherHtml += `<li><code>${v.code}</code> - Giảm ${discount}`;
                if (v.min_order_amount > 0) {
                    voucherHtml += ` (Đơn tối thiểu ${parseInt(v.min_order_amount).toLocaleString('vi-VN')}đ)`;
                }
                voucherHtml += '</li>';
            });
            voucherHtml += '</ul></div>';
            appendHtmlToChat(voucherHtml);
        }
        
    } catch (error) {
        // Remove loading message
        const loadingMsg = document.getElementById(loadingId);
        if (loadingMsg) loadingMsg.remove();
        
        appendToChat('AI', '❌ Xin lỗi, tôi gặp sự cố kết nối. Vui lòng thử lại sau.', 'bot error');
        console.error('Chat error:', error);
    }
}

function appendToChat(who, text, className = '', isLoading = false) {
    const box = document.getElementById('chatBox');
    const msgDiv = document.createElement('div');
    const msgId = 'msg-' + Date.now();
    msgDiv.id = msgId;
    msgDiv.className = `chat-message ${className}`;
    msgDiv.innerHTML = `<strong>${who}:</strong> <span>${text}</span>`;
    box.appendChild(msgDiv);
    box.scrollTop = box.scrollHeight;
    return msgId;
}

function appendHtmlToChat(html) {
    const box = document.getElementById('chatBox');
    const div = document.createElement('div');
    div.className = 'chat-extra';
    div.innerHTML = html;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}
</script>

<style>
/* AI Chatbox Styles */
.ai-chatbox-section {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.ai-chat-container {
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 1rem;
    background: #fafafa;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.chat-messages {
    height: 320px;
    overflow-y: auto;
    margin-bottom: 1rem;
    background: white;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.chat-message {
    margin-bottom: 0.8rem;
    padding: 0.6rem;
    border-radius: 6px;
    line-height: 1.5;
}

.chat-message.user {
    background: #e3f2fd;
    text-align: right;
}

.chat-message.bot {
    background: #f5f5f5;
}

.chat-message.error {
    background: #ffebee;
    color: #c62828;
}

.chat-extra {
    margin: 0.8rem 0;
    padding: 0.8rem;
    background: #fff3e0;
    border-left: 3px solid #ff9800;
    border-radius: 4px;
}

.size-suggestion, .chat-recommendations, .chat-vouchers {
    margin: 0.5rem 0;
}

.chat-recommendations ul, .chat-vouchers ul {
    margin: 0.5rem 0 0 1.5rem;
    padding: 0;
}

.chat-recommendations li, .chat-vouchers li {
    margin: 0.3rem 0;
}

.chat-vouchers code {
    background: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: bold;
    color: #d32f2f;
}

.chat-input-wrapper {
    display: flex;
    gap: 0.5rem;
}

.chat-input {
    flex: 1;
    padding: 0.7rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 0.95rem;
}

.chat-send-btn {
    padding: 0.7rem 1.5rem;
    background: #2196f3;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.3s;
}

.chat-send-btn:hover {
    background: #1976d2;
}

.chat-send-btn i {
    margin-right: 0.3rem;
}
</style>