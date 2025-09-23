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

<main class="container py-4">
    <h1 class="text-center mb-4">Tất cả sản phẩm</h1>

    <!-- Bộ lọc và sắp xếp -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form class="d-flex" method="GET" style="gap:8px;">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control me-2" placeholder="Tìm sản phẩm hoặc danh mục...">
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
                <button class="btn btn-warning" type="submit">Tìm</button>
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
                        <a class="page-link" href="?page=<?= $page-1 ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>&q=<?= urlencode($q) ?>">
                            Trước
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>&q=<?= urlencode($q) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?>&category=<?= $categoryFilter ?>&sort=<?= $sort ?>&q=<?= urlencode($q) ?>">
                            Tiếp
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/products.css">