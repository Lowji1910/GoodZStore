<?php
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';
?>
<main>
    <div class="page-header">
        <h1>Danh mục sản phẩm</h1>
        <p class="page-subtitle">Khám phá các danh mục thời trang đa dạng của chúng tôi</p>
    </div>
    
    <div class="category-list">
        <?php
        $result = $conn->query("SELECT id, name, description FROM categories ORDER BY name ASC");
        if ($result && $result->num_rows > 0):
            while ($cat = $result->fetch_assoc()): ?>
                <a href="category.php?id=<?= $cat['id'] ?>" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3><?= htmlspecialchars($cat['name']) ?></h3>
                    <?php if (!empty($cat['description'])): ?>
                        <p><?= htmlspecialchars($cat['description']) ?></p>
                    <?php else: ?>
                        <p>Xem tất cả sản phẩm trong danh mục này</p>
                    <?php endif; ?>
                    <div class="category-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            <?php endwhile;
        else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Chưa có danh mục nào</p>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/categories.css">
<script src="../ui.js"></script>
