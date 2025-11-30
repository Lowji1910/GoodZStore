<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';

// Load banners
$banners = [];
$sql = "SELECT * FROM contents WHERE type = 'banner' AND is_active = 1 
        AND (start_date IS NULL OR start_date <= NOW()) 
        AND (end_date IS NULL OR end_date >= NOW()) 
        ORDER BY position ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $banners[] = $row;
}

// Load promo banners
$promos = [];
$sqlPromo = "SELECT * FROM contents WHERE type = 'promo' AND is_active = 1 
             AND (start_date IS NULL OR start_date <= NOW()) 
             AND (end_date IS NULL OR end_date >= NOW()) 
             ORDER BY position ASC";
$resultPromo = $conn->query($sqlPromo);
if ($resultPromo && $resultPromo->num_rows > 0) {
    while ($row = $resultPromo->fetch_assoc()) $promos[] = $row;
}
?>

<main>
    <!-- Hero Section (Carousel) -->
    <section id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $index => $banner): ?>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>"></button>
                <?php endforeach; ?>
            <?php else: ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <?php endif; ?>
        </div>
        <div class="carousel-inner">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" style="height: 600px;">
                        <img src="/GoodZStore/uploads/<?= htmlspecialchars($banner['image_url'] ?? 'no-image.jpg') ?>" class="d-block w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($banner['title']) ?>">
                        <div class="carousel-caption d-none d-md-block glass-effect rounded-4 p-4 mb-5 mx-auto" style="max-width: 600px;">
                            <h2 class="display-4 fw-bold text-dark"><?= htmlspecialchars($banner['title']) ?></h2>
                            <?php if ($banner['description']): ?>
                                <p class="lead text-dark"><?= htmlspecialchars($banner['description']) ?></p>
                            <?php endif; ?>
                            <?php if ($banner['button_text'] && $banner['link_url']): ?>
                                <a href="<?= htmlspecialchars($banner['link_url']) ?>" class="btn btn-accent btn-lg mt-3">
                                    <?= htmlspecialchars($banner['button_text']) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active" style="height: 600px;">
                    <div class="d-flex align-items-center justify-content-center h-100 bg-secondary text-white">
                        <div class="text-center">
                            <h1>Chào mừng đến với GoodZStore</h1>
                            <p>Nâng tầm phong cách của bạn</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </section>

    <!-- Categories Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Danh mục nổi bật</h2>
            <div class="row g-4 justify-content-center">
                <?php
                $sql = "SELECT * FROM categories ORDER BY name LIMIT 6";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0):
                    while ($cat = $result->fetch_assoc()): ?>
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="category.php?id=<?= $cat['id'] ?>" class="text-decoration-none">
                                <div class="card card-hover border-0 text-center h-100 bg-light py-4">
                                    <div class="card-body">
                                        <i class="fas fa-tshirt fa-3x text-muted mb-3"></i>
                                        <h5 class="card-title text-dark fw-bold mb-0"><?= htmlspecialchars($cat['name']) ?></h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endwhile;
                endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0">Sản phẩm nổi bật</h2>
                <a href="products.php" class="btn btn-outline-dark">Xem tất cả</a>
            </div>
            
            <div class="row g-4">
                <?php
                $sql = "SELECT p.*, c.name as category_name, i.image_url 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id
                       LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 
                       WHERE p.is_featured = 1
                       LIMIT 4";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-3">
                            <div class="card card-hover h-100 border-0 shadow-sm">
                                <div class="position-relative overflow-hidden">
                                    <img src="/GoodZStore/uploads/<?= htmlspecialchars($row['image_url'] ?? 'no-image.jpg') ?>" 
                                         class="card-img-top object-fit-cover" 
                                         alt="<?= htmlspecialchars($row['name']) ?>"
                                         style="height: 300px;">
                                    <?php if($row['stock_quantity'] <= 0): ?>
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">Hết hàng</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="text-muted small mb-1"><?= htmlspecialchars($row['category_name']) ?></div>
                                    <h5 class="card-title fw-bold text-truncate"><?= htmlspecialchars($row['name']) ?></h5>
                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold text-primary"><?= number_format($row['price'], 0, ',', '.') ?>đ</span>
                                        <a href="product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle" title="Xem chi tiết">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;
                else: ?>
                    <div class="col-12 text-center">Chưa có sản phẩm nổi bật.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Promo Banner -->
    <?php if (!empty($promos)): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <?php foreach ($promos as $promo): ?>
                    <div class="col-md-6">
                        <div class="card border-0 text-white overflow-hidden rounded-4 shadow-md" style="height: 300px;">
                            <img src="/GoodZStore/uploads/<?= htmlspecialchars($promo['image_url']) ?>" class="card-img w-100 h-100 object-fit-cover" alt="Promo">
                            <div class="card-img-overlay d-flex flex-column justify-content-center p-5" style="background: rgba(0,0,0,0.4);">
                                <h3 class="fw-bold display-6"><?= htmlspecialchars($promo['title']) ?></h3>
                                <p class="lead"><?= htmlspecialchars($promo['description']) ?></p>
                                <?php if ($promo['button_text'] && $promo['link_url']): ?>
                                    <div>
                                        <a href="<?= htmlspecialchars($promo['link_url']) ?>" class="btn btn-light fw-bold">
                                            <?= htmlspecialchars($promo['button_text']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- New Arrivals -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0">Sản phẩm mới</h2>
                <a href="products.php?sort=newest" class="btn btn-outline-dark">Xem tất cả</a>
            </div>
            
            <div class="row g-4">
                <?php
                $sql = "SELECT p.*, c.name as category_name, i.image_url 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id
                       LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 
                       ORDER BY p.created_at DESC 
                       LIMIT 4";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-3">
                            <div class="card card-hover h-100 border-0 shadow-sm">
                                <div class="position-relative overflow-hidden">
                                    <img src="/GoodZStore/uploads/<?= htmlspecialchars($row['image_url'] ?? 'no-image.jpg') ?>" 
                                         class="card-img-top object-fit-cover" 
                                         alt="<?= htmlspecialchars($row['name']) ?>"
                                         style="height: 300px;">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="text-muted small mb-1"><?= htmlspecialchars($row['category_name']) ?></div>
                                    <h5 class="card-title fw-bold text-truncate"><?= htmlspecialchars($row['name']) ?></h5>
                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold text-primary"><?= number_format($row['price'], 0, ',', '.') ?>đ</span>
                                        <a href="product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;
                endif; ?>
            </div>
        </div>
    </section>
    <!-- Why Choose Us -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-3">
                    <div class="p-4 rounded-4 bg-light h-100">
                        <i class="fas fa-shipping-fast fa-3x text-warning mb-3"></i>
                        <h5 class="fw-bold">Giao Hàng Nhanh</h5>
                        <p class="text-muted small mb-0">Vận chuyển miễn phí cho đơn hàng từ 500k.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 rounded-4 bg-light h-100">
                        <i class="fas fa-undo fa-3x text-warning mb-3"></i>
                        <h5 class="fw-bold">Đổi Trả Dễ Dàng</h5>
                        <p class="text-muted small mb-0">Đổi trả trong vòng 30 ngày nếu không ưng ý.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 rounded-4 bg-light h-100">
                        <i class="fas fa-headset fa-3x text-warning mb-3"></i>
                        <h5 class="fw-bold">Hỗ Trợ 24/7</h5>
                        <p class="text-muted small mb-0">Đội ngũ hỗ trợ luôn sẵn sàng giải đáp mọi thắc mắc.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 rounded-4 bg-light h-100">
                        <i class="fas fa-shield-alt fa-3x text-warning mb-3"></i>
                        <h5 class="fw-bold">Thanh Toán An Toàn</h5>
                        <p class="text-muted small mb-0">Bảo mật thông tin tuyệt đối với các cổng thanh toán uy tín.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Khách Hàng Nói Gì?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="text-warning small">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-muted fst-italic">"Sản phẩm chất lượng tuyệt vời, giao hàng nhanh chóng. Tôi rất hài lòng với dịch vụ của GoodZStore."</p>
                        <div class="d-flex align-items-center mt-auto">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 40px; height: 40px;">H</div>
                            <div>
                                <h6 class="fw-bold mb-0">Hoàng Nam</h6>
                                <small class="text-muted">Khách hàng thân thiết</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="text-warning small">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-muted fst-italic">"Áo thun mặc rất mát, form đẹp. Chắc chắn sẽ ủng hộ shop dài dài."</p>
                        <div class="d-flex align-items-center mt-auto">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 40px; height: 40px;">L</div>
                            <div>
                                <h6 class="fw-bold mb-0">Lan Anh</h6>
                                <small class="text-muted">Đã mua 5 đơn hàng</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="text-warning small">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                        <p class="text-muted fst-italic">"Nhân viên tư vấn nhiệt tình, đổi size rất nhanh. Rất đáng tiền."</p>
                        <div class="d-flex align-items-center mt-auto">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 40px; height: 40px;">T</div>
                            <div>
                                <h6 class="fw-bold mb-0">Tuấn Kiệt</h6>
                                <small class="text-muted">Khách hàng mới</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-5 bg-dark text-white position-relative overflow-hidden">
        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background: url('https://source.unsplash.com/random/1920x600/?fashion') center/cover;"></div>
        <div class="container position-relative z-1 text-center">
            <h2 class="fw-bold mb-3">Đăng Ký Nhận Tin</h2>
            <p class="mb-4 text-white-50">Nhận thông tin khuyến mãi và voucher giảm giá 10% cho đơn hàng đầu tiên.</p>
            <form class="d-flex justify-content-center gap-2 mx-auto" style="max-width: 500px;">
                <input type="email" class="form-control rounded-pill px-4" placeholder="Nhập email của bạn...">
                <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Đăng Ký</button>
            </form>
        </div>
    </section>
</main>

<?php include_once __DIR__ . '/../footer.php'; ?>
<script src="../ui.js"></script>