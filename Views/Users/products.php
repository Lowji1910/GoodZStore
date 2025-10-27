<?php
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';

// Xử lý phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;
$offset = ($page - 1) * $itemsPerPage;

// Lọc theo danh mục, sắp xếp và tìm kiếm
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$conditions = ["1=1"]; // điều kiện WHERE tích lũy
if ($categoryFilter > 0) $conditions[] = "p.category_id = $categoryFilter";
if ($q !== '') {
    $qEsc = $conn->real_escape_string($q);
    $conditions[] = "(p.name LIKE '%$qEsc%' OR c.name LIKE '%$qEsc%')";
}

// Đếm tổng theo điều kiện hiện tại
$countSql = "SELECT COUNT(DISTINCT p.id) as total
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE " . implode(' AND ', $conditions);
$totalResult = $conn->query($countSql);
$totalItems = 0;
if ($totalResult) {
    $row = $totalResult->fetch_assoc();
    $totalItems = (int)($row['total'] ?? 0);
}
$totalPages = max(1, (int)ceil($totalItems / $itemsPerPage));

// Lấy danh sách danh mục để làm bộ lọc (bảng categories không có cột status)
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);

$sql = "SELECT p.*, c.name as category_name, i.image_url 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 
        WHERE " . implode(' AND ', $conditions) . " ";

switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

$sql .= " LIMIT $itemsPerPage OFFSET $offset";
$result = $conn->query($sql);
?>

<main>
    <div class="page-header">
        <h1>Tất cả sản phẩm</h1>
        <p class="page-subtitle">Khám phá bộ sưu tập thời trang đa dạng của chúng tôi</p>
    </div>

    <!-- Bộ lọc và sắp xếp -->
    <div class="filter-bar">
        <form class="filter-form" method="GET">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Tìm sản phẩm hoặc danh mục...">
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="0">Tất cả danh mục</option>
                <?php 
                $categoriesResult->data_seek(0); // Reset pointer
                while ($category = $categoriesResult->fetch_assoc()): ?>
                    <option value="<?= $category['id'] ?>" <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="sort" class="form-select" onchange="this.form.submit()">
                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
            </select>
            <button class="btn btn-search" type="submit">
                <i class="fas fa-search"></i> Tìm kiếm
            </button>
        </form>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="products-grid">
        <?php if ($result && $result->num_rows > 0):
            while ($product = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="/GoodZStore/uploads/<?= htmlspecialchars($product['image_url'] ?? 'no-image.jpg') ?>" 
                         class="product-img" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-category"><?= htmlspecialchars($product['category_name']) ?></p>
                        <p class="product-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</p>
                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-detail">
                            <i class="fas fa-shopping-cart"></i> Xem chi tiết
                        </a>
                    </div>
                </div>
            <?php endwhile;
        else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Không tìm thấy sản phẩm nào</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
        <nav class="pagination-nav">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page-1 ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>&q=<?= urlencode($q) ?>">
                            <i class="fas fa-chevron-left"></i> Trước
                        </a>
                    </li>
                <?php endif; ?>

                <?php 
                // Hiển thị tối đa 7 số trang
                $start = max(1, $page - 3);
                $end = min($totalPages, $page + 3);
                
                if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1&category=<?= $categoryFilter ?>&sort=<?= $sort ?>&q=<?= urlencode($q) ?>">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>&q=<?= urlencode($q) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $totalPages ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>&q=<?= urlencode($q) ?>"><?= $totalPages ?></a>
                    </li>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>&q=<?= urlencode($q) ?>">
                            Tiếp <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/products.css">