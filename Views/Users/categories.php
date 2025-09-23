<?php
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';
?>
<main>
    <h2>Danh mục sản phẩm</h2>
    <div class="category-list">
        <?php
        $result = $conn->query("SELECT id, name, description FROM categories ORDER BY name ASC");
        if ($result && $result->num_rows > 0):
            while ($cat = $result->fetch_assoc()): ?>
                <div class="category-card">
                    <h3><a href="category.php?id=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></h3>
                    <p><?= htmlspecialchars($cat['description'] ?? '') ?></p>
                </div>
            <?php endwhile;
        else: ?>
            <div>Chưa có danh mục.</div>
        <?php endif; ?>
    </div>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/categories.css">
<script src="../ui.js"></script>
