<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
include_once __DIR__ . '/../header.php';
echo '<link rel="stylesheet" href="../css/about_landing.css">';
?>

<main class="landing-hero">
  <section class="hero py-6 text-center">
    <div class="container">
      <h1 class="display-4">GoodZStore — Thời trang cho cuộc sống hiện đại</h1>
      <p class="lead">Chúng tôi tạo ra trang phục giúp bạn tự tin, thoải mái và thể hiện cá tính mỗi ngày.</p>
      <p class="mt-4">
        <a href="/GoodZStore/Views/Users/products.php" class="btn btn-primary btn-lg">Khám phá sản phẩm</a>
        <a href="/GoodZStore/Views/Users/contact.php" class="btn btn-outline-secondary btn-lg ms-2">Liên hệ</a>
      </p>
    </div>
  </section>

  <section class="values py-5">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <div class="icon mb-3">🧵</div>
            <h5>Chất lượng</h5>
            <p class="text-muted">Chọn lựa chất liệu bền đẹp, quy trình kiểm soát chất lượng nghiêm ngặt.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <div class="icon mb-3">⚡</div>
            <h5>Nhanh chóng & Tin cậy</h5>
            <p class="text-muted">Giao hàng nhanh, chính sách đổi trả rõ ràng và hỗ trợ khách hàng tận tâm.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <div class="icon mb-3">🌿</div>
            <h5>Trách nhiệm</h5>
            <p class="text-muted">Hướng tới chuỗi cung ứng bền vững và các hoạt động có trách nhiệm xã hội.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="mission py-5 bg-light">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h3>Sứ mệnh của chúng tôi</h3>
          <p class="text-muted">GoodZStore tồn tại để giúp khách hàng tìm thấy trang phục phù hợp với phong cách và ngân sách, đồng thời luôn đặt trải nghiệm và chất lượng lên hàng đầu. Chúng tôi tin rằng thời trang là cách thể hiện bản thân — và mọi người đều xứng đáng có được điều đó.</p>
          <ul>
            <li>Thiết kế tối giản, phù hợp hàng ngày</li>
            <li>Giá cả minh bạch và cạnh tranh</li>
            <li>Hỗ trợ khách hàng nhanh chóng, chính sách rõ ràng</li>
          </ul>
        </div>
        <div class="col-md-6 text-center">
          <img src="/GoodZStore/uploads/hero-about.jpg" alt="GoodZStore" class="img-fluid rounded shadow-sm" style="max-width:420px;">
        </div>
      </div>
    </div>
  </section>

  <section class="team py-5">
    <div class="container">
      <h3 class="text-center mb-4">Đội ngũ GoodZ</h3>
      <div class="row g-4 justify-content-center">
        <div class="col-sm-6 col-md-3">
          <div class="card team-card text-center p-3">
            <div class="avatar mb-2">👩‍💼</div>
            <h6 class="mb-0">Trần Thị B</h6>
            <small class="text-muted">Founder & CEO</small>
          </div>
        </div>
        <div class="col-sm-6 col-md-3">
          <div class="card team-card text-center p-3">
            <div class="avatar mb-2">👨‍💻</div>
            <h6 class="mb-0">Ngô Văn G</h6>
            <small class="text-muted">CTO</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="cta py-5 bg-primary text-white text-center">
    <div class="container">
      <h4>Tiếp theo? Hãy khám phá bộ sưu tập mới nhất của chúng tôi.</h4>
      <p class="mt-3"><a class="btn btn-light btn-lg" href="/GoodZStore/Views/Users/products.php">Xem sưu tập</a></p>
    </div>
  </section>
</main>

<?php include_once __DIR__ . '/../footer.php'; ?>
