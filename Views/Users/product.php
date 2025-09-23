
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
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/product.css">
<script src="../ui.js"></script>