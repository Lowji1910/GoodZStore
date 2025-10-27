<?php
include_once __DIR__ . '/../header.php';
?>
<main>
    <div class="page-header">
        <h1>Giới thiệu về GoodZStore</h1>
        <p class="page-subtitle">Điểm đến thời trang của giới trẻ Việt Nam</p>
    </div>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-store"></i>
            </div>
            <h2>Chào mừng đến với GoodZStore</h2>
            <p>Nơi phong cách gặp gỡ chất lượng, nơi cá tính được thể hiện qua từng sản phẩm</p>
        </div>
    </section>

    <!-- About Grid -->
    <div class="about-grid">
        <!-- Mission -->
        <div class="about-card">
            <div class="card-icon mission">
                <i class="fas fa-bullseye"></i>
            </div>
            <h3>Sứ mệnh</h3>
            <p>GoodZStore mang đến phong cách thời trang hiện đại, chất lượng cao và đầy cá tính cho giới trẻ Việt Nam. Chúng tôi tin rằng thời trang không chỉ là trang phục, mà là cách bạn thể hiện bản thân.</p>
        </div>

        <!-- Vision -->
        <div class="about-card">
            <div class="card-icon vision">
                <i class="fas fa-eye"></i>
            </div>
            <h3>Tầm nhìn</h3>
            <p>Trở thành thương hiệu thời trang hàng đầu Việt Nam, được yêu thích bởi sự đổi mới không ngừng và chất lượng vượt trội. Chúng tôi hướng đến việc tạo ra trải nghiệm mua sắm tuyệt vời nhất.</p>
        </div>

        <!-- Values -->
        <div class="about-card featured">
            <div class="card-icon values">
                <i class="fas fa-heart"></i>
            </div>
            <h3>Giá trị cốt lõi</h3>
            <ul class="values-list">
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Chất lượng sản phẩm là ưu tiên hàng đầu</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Đổi mới, sáng tạo trong thiết kế</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Dịch vụ khách hàng tận tâm</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Giá cả hợp lý, minh bạch</span>
                </li>
            </ul>
        </div>

        <!-- Team -->
        <div class="about-card">
            <div class="card-icon team">
                <i class="fas fa-users"></i>
            </div>
            <h3>Đội ngũ của chúng tôi</h3>
            <p>Đội ngũ trẻ trung, năng động và đầy nhiệt huyết. Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ khách hàng 24/7 để mang đến trải nghiệm mua sắm tuyệt vời nhất.</p>
        </div>
    </div>

    <!-- Contact CTA -->
    <section class="contact-cta">
        <div class="cta-content">
            <h2>Liên hệ với chúng tôi</h2>
            <p>Có câu hỏi hoặc cần hỗ trợ? Chúng tôi luôn sẵn sàng!</p>
            <div class="contact-info-grid">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>support@goodzstore.com</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>1900 xxxx</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Hà Nội, Việt Nam</span>
                </div>
            </div>
            <a href="contact.php" class="btn-contact">
                <i class="fas fa-paper-plane"></i>
                Gửi tin nhắn
            </a>
        </div>
    </section>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/about.css">
<script src="../ui.js"></script>
