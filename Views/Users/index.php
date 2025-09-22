<?php

include_once __DIR__ . '/../header.php';
?>
<main>
    <section class="banner">
        <div class="banner-slider">
            <div class="slide active">
                <img src="../img/banner1.jpg" alt="Banner 1" />
                <div class="banner-caption">Bộ sưu tập Thu Đông 2025</div>
            </div>
            <div class="slide">
                <img src="../img/banner2.jpg" alt="Banner 2" />
                <div class="banner-caption">Ưu đãi đặc biệt cho thành viên mới</div>
            </div>
            <div class="slide">
                <img src="../img/banner3.jpg" alt="Banner 3" />
                <div class="banner-caption">Thời trang nam nữ cao cấp</div>
            </div>
            <button class="banner-prev">&#10094;</button>
            <button class="banner-next">&#10095;</button>
        </div>
    </section>
    <section class="featured">
        <h2>Sản phẩm nổi bật</h2>
        <div class="product-list">
            <?php
            require_once __DIR__ . '/../../Models/db.php';
            $sql = "SELECT p.*, c.name as category_name, i.image_url 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id
                   LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 
                   WHERE p.is_featured = 1 AND p.status = 1
                   LIMIT 4";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="/GoodZStore/uploads/<?= htmlspecialchars($row['image_url'] ?? 'no-image.jpg') ?>" 
                             alt="<?= htmlspecialchars($row['name']) ?>" 
                             class="product-img">
                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($row['name']) ?></div>
                            <div class="product-category"><?= htmlspecialchars($row['category_name']) ?></div>
                            <div class="product-price"><?= number_format($row['price'], 0, ',', '.') ?>đ</div>
                            <a href="product.php?id=<?= $row['id'] ?>" class="btn btn-warning">
                                <i class="fas fa-shopping-cart"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div>Không có sản phẩm nổi bật.</div>
            <?php endif; ?>
        </div>
    </section>
    <section class="bestseller">
        <h2>Sản phẩm mới</h2>
        <div class="product-list">
            <?php
            $sql = "SELECT p.*, c.name as category_name, i.image_url 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id
                   LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 
                   WHERE p.status = 1
                   ORDER BY p.created_at DESC 
                   LIMIT 4";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="/GoodZStore/uploads/<?= htmlspecialchars($row['image_url'] ?? 'no-image.jpg') ?>" 
                             alt="<?= htmlspecialchars($row['name']) ?>" 
                             class="product-img">
                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($row['name']) ?></div>
                            <div class="product-category"><?= htmlspecialchars($row['category_name']) ?></div>
                            <div class="product-price"><?= number_format($row['price'], 0, ',', '.') ?>đ</div>
                            <a href="product.php?id=<?= $row['id'] ?>" class="btn btn-warning">
                                <i class="fas fa-shopping-cart"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div>Không có sản phẩm mới.</div>
            <?php endif; ?>
        </div>
    </section>
    <section class="promo">
        <h2>Danh mục sản phẩm</h2>
        <ul class="category-list">
            <?php
            $sql = "SELECT * FROM categories WHERE status = 1 ORDER BY name";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0):
                while ($category = $result->fetch_assoc()): ?>
                    <li class="category-item">
                        <a href="category.php?id=<?= $category['id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    </li>
                <?php endwhile;
            endif; ?>
        </ul>
    </section>
    <section class="special-offer">
        <h2>Ưu đãi đặc biệt</h2>
        <div class="offer-box">
            <div class="offer-title">Giảm 20% cho đơn hàng đầu tiên!</div>
            <a href="checkout.php" class="btn btn-offer">Nhận ưu đãi</a>
        </div>
    </section>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/home.css">
<script src="../ui.js"></script>