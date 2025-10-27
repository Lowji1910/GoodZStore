<?php
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';

// Lấy thông tin danh mục
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($categoryId <= 0) {
    header('Location: products.php');
    exit;
}

$categoryQuery = "SELECT * FROM categories WHERE id = ?";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$categoryResult = $stmt->get_result();
$category = $categoryResult->fetch_assoc();

if (!$category) {
    header('Location: products.php');
    exit;
}

// Xử lý phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;
$offset = ($page - 1) * $itemsPerPage;

// Lấy tổng số sản phẩm trong danh mục
$totalQuery = "SELECT COUNT(*) as total FROM products WHERE category_id = ?";
$stmt = $conn->prepare($totalQuery);
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRow = $totalResult->fetch_assoc();

$totalItems = $totalRow['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Xử lý sắp xếp
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Lấy sản phẩm trong danh mục
$sql = "SELECT p.*, i.image_url 
        FROM products p 
        LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 
        WHERE p.category_id = ? ";

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

$sql .= " LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $categoryId, $itemsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<main>
    <nav aria-label="breadcrumb" class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($category['name']) ?></li>
        </ol>
    </nav>

    <div class="page-header">
        <h1><?= htmlspecialchars($category['name']) ?></h1>
        <?php if ($category['description']): ?>
            <p class="page-subtitle"><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Sắp xếp -->
    <div class="filter-bar">
        <form class="sort-form" method="GET">
            <input type="hidden" name="id" value="<?= $categoryId ?>">
            <label for="sort">Sắp xếp theo:</label>
            <select name="sort" id="sort" class="form-select" onchange="this.form.submit()">
                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
            </select>
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
                        <p class="product-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</p>
                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-detail">
                            <i class="fas fa-shopping-cart"></i> Xem chi tiết
                        </a>
                    </div>
                </div>
            <?php endwhile;
        else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>Không có sản phẩm nào trong danh mục này</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
        <nav class="pagination-nav">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?id=<?= $categoryId ?>&page=<?= $page-1 ?>&sort=<?= $sort ?>">
                            <i class="fas fa-chevron-left"></i> Trước
                        </a>
                    </li>
                <?php endif; ?>

                <?php 
                $start = max(1, $page - 3);
                $end = min($totalPages, $page + 3);
                
                if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?id=<?= $categoryId ?>&page=1&sort=<?= $sort ?>">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?id=<?= $categoryId ?>&page=<?= $i ?>&sort=<?= $sort ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?id=<?= $categoryId ?>&page=<?= $totalPages ?>&sort=<?= $sort ?>"><?= $totalPages ?></a>
                    </li>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?id=<?= $categoryId ?>&page=<?= $page+1 ?>&sort=<?= $sort ?>">
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