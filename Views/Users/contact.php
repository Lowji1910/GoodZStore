<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
include_once __DIR__ . '/../header.php';
?>

<!-- Hero Section -->
<section class="bg-dark text-white py-5 position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25" style="background: url('https://source.unsplash.com/random/1920x600/?office') center/cover;"></div>
    <div class="container position-relative z-1 text-center py-5">
        <h1 class="display-4 fw-bold mb-3">Liên Hệ Với Chúng Tôi</h1>
        <p class="lead text-light opacity-75 mx-auto" style="max-width: 600px;">
            Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy gửi tin nhắn hoặc ghé thăm cửa hàng của GoodZStore.
        </p>
    </div>
</section>

<main class="py-5 bg-light">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">Thông tin liên hệ</h4>
                        
                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-primary-subtle text-primary p-3 rounded-circle me-3">
                                <i class="fas fa-map-marker-alt fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Địa chỉ</h6>
                                <p class="text-muted mb-0">123 Đường Thời Trang, Quận 1, TP. Hồ Chí Minh, Việt Nam</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-warning-subtle text-warning p-3 rounded-circle me-3">
                                <i class="fas fa-envelope fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Email</h6>
                                <p class="text-muted mb-0">support@goodzstore.com</p>
                                <p class="text-muted mb-0">sales@goodzstore.com</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-success-subtle text-success p-3 rounded-circle me-3">
                                <i class="fas fa-phone-alt fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Hotline</h6>
                                <p class="text-muted mb-0">0901 234 567</p>
                                <p class="text-muted mb-0">0909 888 999</p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3">Mạng xã hội</h6>
                        <div class="d-flex gap-3">
                            <a href="#" class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; display: grid; place-items: center;"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-outline-danger rounded-circle" style="width: 40px; height: 40px; display: grid; place-items: center;"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="btn btn-outline-dark rounded-circle" style="width: 40px; height: 40px; display: grid; place-items: center;"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h3 class="fw-bold mb-4">Gửi tin nhắn</h3>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control bg-light border-0" id="name" placeholder="Họ tên">
                                        <label for="name">Họ tên của bạn</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control bg-light border-0" id="email" placeholder="Email">
                                        <label for="email">Email liên hệ</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control bg-light border-0" id="subject" placeholder="Tiêu đề">
                                        <label for="subject">Tiêu đề</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control bg-light border-0" placeholder="Nội dung" id="message" style="height: 150px"></textarea>
                                        <label for="message">Nội dung tin nhắn</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary-custom py-3 px-5 fw-bold" type="submit">
                                        <i class="fas fa-paper-plane me-2"></i> Gửi Tin Nhắn
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.424167419717!2d106.69834531480076!3d10.779268992319692!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f38f9ed887b%3A0x14aded5703768966!2zRGluaCDEkOG7mWMgTOG6rXA!5e0!3m2!1svi!2s!4v1646812345678!5m2!1svi!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/../footer.php'; ?>
