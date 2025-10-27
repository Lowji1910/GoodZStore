<?php // Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ghi log mỗi khi include file
function debug_include($path) {
    echo "<pre style='background:#f4f4f4;padding:10px'>Đang include file: " . realpath($path) . "</pre>";
    include($path);
}
include_once __DIR__ . '/../header.php';
// DEBUG: show which header file is included
echo "<!-- INDEX_INCLUDE_DEBUG: " . realpath(__DIR__ . '/../header.php') . " | mtime=" . @filemtime(__DIR__ . '/../header.php') . " -->";
// DEBUG: confirm current index file path
echo "<!-- INDEX_FILE: " . __FILE__ . " -->";
require_once __DIR__ . '/../../Models/db.php';

// Load banners from database
$banners = [];
$sql = "SELECT * FROM contents 
        WHERE type = 'banner' 
        AND is_active = 1 
        AND (start_date IS NULL OR start_date <= NOW())
        AND (end_date IS NULL OR end_date >= NOW())
        ORDER BY position ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $banners[] = $row;
    }
}

// Load promo banners
$promos = [];
$sqlPromo = "SELECT * FROM contents 
             WHERE type = 'promo' 
             AND is_active = 1 
             AND (start_date IS NULL OR start_date <= NOW())
             AND (end_date IS NULL OR end_date >= NOW())
             ORDER BY position ASC";
$resultPromo = $conn->query($sqlPromo);
if ($resultPromo && $resultPromo->num_rows > 0) {
    while ($row = $resultPromo->fetch_assoc()) {
        $promos[] = $row;
    }
}
?>
<main>
    <!-- Banner Slider với Auto-slide -->
    <section class="banner">
        <div class="banner-slider" id="bannerSlider">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                        <img src="/GoodZStore/uploads/<?= htmlspecialchars($banner['image_url'] ?? 'no-image.jpg') ?>" 
                             alt="<?= htmlspecialchars($banner['title']) ?>" />
                        <div class="banner-caption">
                            <h2><?= htmlspecialchars($banner['title']) ?></h2>
                            <?php if ($banner['description']): ?>
                                <p><?= htmlspecialchars($banner['description']) ?></p>
                            <?php endif; ?>
                            <?php if ($banner['button_text'] && $banner['link_url']): ?>
                                <a href="<?= htmlspecialchars($banner['link_url']) ?>" class="banner-btn">
                                    <?= htmlspecialchars($banner['button_text']) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="slide active">
                    <img src="../img/placeholder-banner.jpg" alt="Banner" />
                    <div class="banner-caption">Chào mừng đến với GoodZStore</div>
                </div>
            <?php endif; ?>
            
            <?php if (count($banners) > 1): ?>
                <button class="banner-prev" onclick="changeSlide(-1)">&#10094;</button>
                <button class="banner-next" onclick="changeSlide(1)">&#10095;</button>
                <div class="banner-dots">
                    <?php foreach ($banners as $index => $banner): ?>
                        <span class="dot <?= $index === 0 ? 'active' : '' ?>" onclick="currentSlide(<?= $index ?>)"></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
                   WHERE p.is_featured = 1
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
                   WHERE 1=1
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
            $sql = "SELECT * FROM categories ORDER BY name";
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
    <!-- Promo Banners Slider -->
    <?php if (!empty($promos)): ?>
    <section class="promo-banners">
        <h2>Ưu đãi đặc biệt</h2>
        <div class="promo-slider" id="promoSlider">
            <?php foreach ($promos as $index => $promo): ?>
                <div class="promo-slide <?= $index === 0 ? 'active' : '' ?>">
                    <div class="promo-card">
                        <?php if ($promo['image_url']): ?>
                            <img src="/GoodZStore/uploads/<?= htmlspecialchars($promo['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($promo['title']) ?>"
                                 class="promo-img">
                        <?php endif; ?>
                        <div class="promo-content">
                            <h3><?= htmlspecialchars($promo['title']) ?></h3>
                            <?php if ($promo['description']): ?>
                                <p><?= htmlspecialchars($promo['description']) ?></p>
                            <?php endif; ?>
                            <?php if ($promo['button_text'] && $promo['link_url']): ?>
                                <a href="<?= htmlspecialchars($promo['link_url']) ?>" class="btn btn-promo">
                                    <?= htmlspecialchars($promo['button_text']) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($promos) > 1): ?>
                <button class="promo-prev" onclick="changePromoSlide(-1)">&#10094;</button>
                <button class="promo-next" onclick="changePromoSlide(1)">&#10095;</button>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/home.css">
<script src="../js/slider.js"></script>
<script src="../ui.js"></script>