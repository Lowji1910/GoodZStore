
<?php
// User product detail page
include_once __DIR__ . '/../header.php';
?>
<main>
    <div class="product-detail">
        <div class="product-image">
            <img src="../img/sample.jpg" alt="Sản phẩm" id="mainImg" class="product-img" onclick="zoomImage(this)">
        </div>
        <div class="product-info">
            <h2 class="product-name">Áo Thun Nam Cao Cấp</h2>
            <p class="product-price">1.000.000đ</p>
            <p class="product-desc">Mô tả sản phẩm chi tiết, chất liệu cotton cao cấp, form dáng trẻ trung, phù hợp nhiều phong cách.</p>
            <form class="product-options">
                <label>Kích cỡ:
                    <select><option>S</option><option>M</option><option>L</option></select>
                </label>
                <label>Màu sắc:
                    <select><option>Đen</option><option>Trắng</option></select>
                </label>
                <label>Số lượng:
                    <input type="number" value="1" min="1">
                </label>
                <button type="submit" class="btn">Thêm vào giỏ</button>
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