<?php
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';

// Xử lý phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;
$offset = ($page - 1) * $itemsPerPage;

// Lấy tổng số sản phẩm để tính số trang
$totalQuery = "SELECT COUNT(*) as total FROM products WHERE status = 1";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalItems = $totalRow['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Lấy danh sách danh mục để làm bộ lọc
$categoriesQuery = "SELECT * FROM categories WHERE status = 1 ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);

// Xử lý lọc theo danh mục và sắp xếp
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$sql = "SELECT p.*, c.name as category_name, i.image_url 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 
        WHERE p.status = 1 ";

if ($categoryFilter > 0) {
    $sql .= " AND p.category_id = $categoryFilter";
}

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

<main class="container py-4">
    <h1 class="text-center mb-4">Tất cả sản phẩm</h1>

    <!-- Bộ lọc và sắp xếp -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form class="d-flex" method="GET">
                <select name="category" class="form-select me-2" onchange="this.form.submit()">
                    <option value="0">Tất cả danh mục</option>
                    <?php while ($category = $categoriesResult->fetch_assoc()): ?>
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
            </form>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="row">
        <?php if ($result && $result->num_rows > 0):
            while ($product = $result->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="/GoodZStore/uploads/<?= htmlspecialchars($product['image_url'] ?? 'no-image.jpg') ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted"><?= htmlspecialchars($product['category_name']) ?></small>
                            </p>
                            <p class="card-text">
                                <strong class="text-danger"><?= number_format($product['price'], 0, ',', '.') ?>đ</strong>
                            </p>
                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-warning w-100">
                                <i class="fas fa-shopping-cart"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile;
        else: ?>
            <div class="col-12">
                <p class="text-center">Không tìm thấy sản phẩm nào.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page-1 ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>">
                            Trước
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>">
                            Tiếp
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<?php include_once __DIR__ . '/../footer.php'; ?>