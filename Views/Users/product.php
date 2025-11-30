<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/notifications.php';

// 1. Get Product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Fetch Product Data
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: index.php');
    exit;
}

// 3. Fetch Images
$images = [];
$sqlImg = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC";
$stmtImg = $conn->prepare($sqlImg);
$stmtImg->bind_param("i", $product_id);
$stmtImg->execute();
$resImg = $stmtImg->get_result();
while ($row = $resImg->fetch_assoc()) {
    $images[] = $row;
}

// 4. Fetch Related Products
$related = [];
if (!empty($product['category_id'])) {
    $rel_sql = "SELECT p.id, p.name, p.price, i.image_url
                FROM products p
                LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1
                WHERE p.category_id = ? AND p.id <> ?
                ORDER BY p.created_at DESC
                LIMIT 4";
    $stmtRel = $conn->prepare($rel_sql);
    $stmtRel->bind_param("ii", $product['category_id'], $product_id);
    $stmtRel->execute();
    $related = $stmtRel->get_result();
}

// 5. Fetch Product Sizes
$sizes = [];
$sqlSizes = "SELECT * FROM product_sizes WHERE product_id = ? ORDER BY id ASC";
$stmtSizes = $conn->prepare($sqlSizes);
$stmtSizes->bind_param("i", $product_id);
$stmtSizes->execute();
$resSizes = $stmtSizes->get_result();
while ($row = $resSizes->fetch_assoc()) {
    $sizes[] = $row;
}

// 5. Handle Review Submission
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
            $stmtRev = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmtRev->bind_param('iiis', $uid, $product_id, $rating, $comment);
            
            if ($stmtRev->execute()) {
                $review_msg = 'Gửi đánh giá thành công!';
            } else {
                $review_msg = 'Lỗi: ' . $conn->error;
            }
        }
    }
}

include_once __DIR__ . '/../header.php';
?>

<main class="py-5 bg-white">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="products.php" class="text-decoration-none text-muted">Sản phẩm</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- Product Images -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-3 overflow-hidden rounded-4">
                    <?php 
                    $mainImg = !empty($images) ? $images[0]['image_url'] : 'no-image.jpg';
                    ?>
                    <img id="mainImage" src="/GoodZStore/uploads/<?= htmlspecialchars($mainImg) ?>" class="img-fluid w-100 object-fit-cover" style="height: 500px;" alt="<?= htmlspecialchars($product['name']) ?>">
                </div>
                <?php if (count($images) > 1): ?>
                    <div class="d-flex gap-2 overflow-auto pb-2">
                        <?php foreach ($images as $img): ?>
                            <img src="/GoodZStore/uploads/<?= htmlspecialchars($img['image_url']) ?>" 
                                 class="rounded-3 cursor-pointer border border-2 border-transparent hover-border-primary" 
                                 style="width: 80px; height: 80px; object-fit: cover;"
                                 onclick="document.getElementById('mainImage').src=this.src">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <span class="badge bg-light text-dark border mb-2"><?= htmlspecialchars($product['category_name']) ?></span>
                    <h1 class="fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <div class="d-flex align-items-center mb-4">
                        <h2 class="text-primary fw-bold mb-0 me-3"><?= number_format($product['price'], 0, ',', '.') ?>đ</h2>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="badge bg-success-subtle text-success rounded-pill">Còn hàng</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger rounded-pill">Hết hàng</span>
                        <?php endif; ?>
                    </div>

                    <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                    <form action="cart.php" method="post">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        
                        <!-- Size Selector -->
                        <?php if (!empty($sizes)): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Chọn kích thước</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php foreach($sizes as $index => $size): ?>
                                        <input type="radio" class="btn-check size-selector" 
                                               name="size_id" 
                                               id="size<?= $size['id'] ?>" 
                                               value="<?= $size['id'] ?>" 
                                               data-stock="<?= $size['stock_quantity'] ?>"
                                               data-price-adj="<?= $size['price_adjustment'] ?>"
                                               autocomplete="off" 
                                               <?= $index===0 ? 'checked' : '' ?> 
                                               <?= $size['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                                        <label class="btn btn-outline-secondary px-3" for="size<?= $size['id'] ?>">
                                            <?= htmlspecialchars($size['size_name']) ?>
                                            <?php if($size['stock_quantity'] <= 0) echo '<small class="d-block text-danger" style="font-size:0.6rem">Hết hàng</small>'; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-2 text-muted small">
                                    Kho: <span id="stock-display"><?= $sizes[0]['stock_quantity'] ?></span> sản phẩm
                                </div>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="size_id" value="0">
                            <div class="mb-4">
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i> Freesize / Một kích cỡ
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Quantity -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Số lượng</label>
                            <div class="input-group" style="width: 140px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty()">-</button>
                                <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="<?= !empty($sizes) ? $sizes[0]['stock_quantity'] : $product['stock_quantity'] ?>">
                                <button class="btn btn-outline-secondary" type="button" onclick="increaseQty()">+</button>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary-custom flex-grow-1 py-3" <?= ($product['stock_quantity'] <= 0 && empty($sizes)) ? 'disabled' : '' ?>>
                                <i class="fas fa-shopping-bag me-2"></i> Thêm vào giỏ
                            </button>
                            <button type="button" class="btn btn-outline-danger rounded-3 p-3">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
    <!-- Related Products -->
    <section class="mt-5 pt-5 border-top">
        <h3 class="fw-bold mb-4">Sản phẩm liên quan</h3>
        <div class="row g-4">
            <?php if ($related && $related->num_rows > 0):
                while ($rp = $related->fetch_assoc()): ?>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="position-relative overflow-hidden rounded-top-4">
                            <img src="/GoodZStore/uploads/<?= htmlspecialchars($rp['image_url'] ?? 'no-image.jpg') ?>" class="card-img-top object-fit-cover" style="height: 250px;" alt="<?= htmlspecialchars($rp['name']) ?>">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 translate-y-100 transition-transform product-actions">
                                <a href="product.php?id=<?= $rp['id'] ?>" class="btn btn-light w-100 fw-bold shadow-sm">Xem chi tiết</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title fw-bold text-truncate"><?= htmlspecialchars($rp['name']) ?></h6>
                            <p class="card-text text-primary fw-bold"><?= number_format($rp['price'], 0, ',', '.') ?>đ</p>
                        </div>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="col-12 text-center text-muted">Chưa có sản phẩm liên quan.</div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- AI Chatbox -->
    <section class="mt-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-light">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                        <div class="d-flex align-items-center gap-3 justify-content-center justify-content-md-start">
                            <div class="bg-white p-3 rounded-circle shadow-sm text-warning fs-2">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1">Trợ lý AI Stylist</h4>
                                <p class="text-muted mb-0 small">Hỏi về size, cách phối đồ, hoặc mã giảm giá.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div id="ai-chat" class="bg-white rounded-3 shadow-sm border p-3" style="height: 300px; display: flex; flex-direction: column;">
                            <div id="chatBox" class="flex-grow-1 overflow-auto mb-3 pe-2">
                                <div class="ai-msg bot bg-light p-2 rounded-3 d-inline-block mb-2 text-secondary small">
                                    Chào bạn! Bạn cần tư vấn gì về sản phẩm <strong><?= htmlspecialchars($product['name']) ?></strong> không?
                                </div>
                            </div>
                            <div class="input-group">
                                <input id="chatInput" type="text" class="form-control border-0 bg-light" placeholder="Nhập câu hỏi của bạn...">
                                <button id="sendBtn" class="btn btn-primary-custom px-4">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
    <section class="mt-5 pt-5 border-top">
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
            <div class="alert alert-info" style="max-width:720px;">Hãy <a href="/GoodZStore/Views/Users/auth.php">đăng nhập</a> để đánh giá sản phẩm.</div>
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

// Update max quantity and price when size changes
const sizeInputs = document.querySelectorAll('.size-selector');
const qtyInput = document.getElementById('quantity');
const stockDisplay = document.getElementById('stock-display');
const priceDisplay = document.querySelector('h2.text-primary'); // Assuming this is the price element
const basePrice = <?= $product['price'] ?>;

sizeInputs.forEach(input => {
    input.addEventListener('change', function() {
        const stock = parseInt(this.dataset.stock);
        const priceAdj = parseFloat(this.dataset.priceAdj);
        
        // Update stock
        qtyInput.max = stock;
        if (parseInt(qtyInput.value) > stock) {
            qtyInput.value = stock;
        }
        if(stockDisplay) stockDisplay.innerText = stock;
        
        // Update price
        const newPrice = basePrice + priceAdj;
        if(priceDisplay) {
            priceDisplay.innerText = new Intl.NumberFormat('vi-VN').format(newPrice) + 'đ';
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
        } else if (data.prev_recommendations && data.prev_recommendations.length > 0) {
            // If no new recommendations, show previous ones suggested in this session
            let recsHtml = '<div class="chat-recommendations"><strong>🛍️ Sản phẩm đã gợi ý trước đó:</strong><ul>';
            data.prev_recommendations.forEach(r => {
                const href = r.url ? r.url : `product.php?id=${r.id}`;
                recsHtml += `<li><a href="${href}">${r.name}</a></li>`;
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
    // Convert plain URLs in text to clickable links
    function linkify(inputText) {
        const urlPattern = /(https?:\/\/[^\s]+)/g;
        return inputText.replace(urlPattern, function(url) {
            return `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`;
        });
    }
    msgDiv.innerHTML = `<strong>${who}:</strong> <span>${linkify(text)}</span>`;
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