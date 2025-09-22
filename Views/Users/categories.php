<?php
include_once __DIR__ . '/../header.php';
?>
<main>
    <h2>Danh mục sản phẩm</h2>
    <div class="category-list">
        <?php // Dữ liệu động, sẽ lấy từ DB qua controller
        $categories = [
            ["name" => "Áo thun", "desc" => "Nhiều mẫu áo thun nam nữ cá tính."],
            ["name" => "Quần jeans", "desc" => "Quần jeans thời trang, đa dạng kiểu dáng."],
            ["name" => "Áo khoác", "desc" => "Áo khoác phong cách, phù hợp mọi thời tiết."],
            ["name" => "Váy nữ", "desc" => "Váy nữ trẻ trung, hiện đại."],
            ["name" => "Phụ kiện", "desc" => "Phụ kiện thời trang, túi xách, mũ, kính."]
        ];
        foreach ($categories as $cat): ?>
        <div class="category-card">
            <h3><?= htmlspecialchars($cat['name']) ?></h3>
            <p><?= htmlspecialchars($cat['desc']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/categories.css">
<script src="../ui.js"></script>
