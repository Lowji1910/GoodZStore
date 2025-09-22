
<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';

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
                <div class="mb-3">
                    <label class="form-label">Số lượng:
                        <input type="number" name="quantity" value="1" min="1" 
                               max="<?= $product['stock_quantity'] ?>" class="form-control" required>
                    </label>
                </div>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                </button>
            </form>
        </div>
    </div>
    <section class="related-products">
        <h3>Sản phẩm liên quan</h3>
        <div class="product-list">
            <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="product-card">
                <img src="../img/product<?= $i ?>.jpg" alt="Sản phẩm liên quan <?= $i ?>" class="product-img">
                <div class="product-name">Áo Thun Nam <?= $i ?></div>
                <div class="product-price">299,000đ</div>
                <a href="product.php?id=<?= $i ?>" class="btn">Xem chi tiết</a>
            </div>
            <?php endfor; ?>
        </div>
    </section>
    <section class="reviews">
        <h3>Đánh giá & Bình luận</h3>
        <div class="review-list">
            <div class="review-item">
                <strong>Nguyễn Văn A</strong> <span>★★★★★</span>
                <p>Áo đẹp, chất liệu tốt, giao hàng nhanh!</p>
            </div>
            <div class="review-item">
                <strong>Trần Thị B</strong> <span>★★★★☆</span>
                <p>Form chuẩn, mặc rất thoải mái.</p>
            </div>
        </div>
        <form class="review-form">
            <input type="text" placeholder="Viết bình luận...">
            <button class="btn">Gửi</button>
        </form>
    </section>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/product.css">
<script src="../ui.js"></script>