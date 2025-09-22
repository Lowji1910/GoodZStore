<?php
include_once __DIR__ . '/../header.php';
?>
<main>
    <h2>Khuyến mãi & Ưu đãi</h2>
    <div class="promo-list">
        <?php // Dữ liệu động, sẽ lấy từ DB qua controller
        $promotions = [
            ["title" => "Giảm 20% cho đơn hàng đầu tiên!", "desc" => "Áp dụng cho khách mới đăng ký.", "code" => "WELCOME20"],
            ["title" => "Freeship toàn quốc", "desc" => "Đơn từ 500.000đ.", "code" => "FREESHIP"],
            ["title" => "Mua 2 tặng 1", "desc" => "Áp dụng cho sản phẩm áo thun.", "code" => "BUY2GET1"]
        ];
        foreach ($promotions as $promo): ?>
        <div class="promo-card">
            <h3><?= htmlspecialchars($promo['title']) ?></h3>
            <p><?= htmlspecialchars($promo['desc']) ?></p>
            <div class="promo-code">Mã: <b><?= htmlspecialchars($promo['code']) ?></b></div>
        </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/promotions.css">
<script src="../ui.js"></script>
