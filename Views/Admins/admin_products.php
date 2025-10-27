<?php
// Quản lý sản phẩm cho admin
require_once __DIR__ . '/../../Models/db.php';
// Xử lý thêm/xóa sản phẩm
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Thêm sản phẩm
    if (isset($_POST['add_product'])) {
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $category_id = intval($_POST['category_id']);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $created_at = date('Y-m-d H:i:s');
        $image_url = '';
        // Xử lý upload ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_url = uniqid('prod_', true) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../img/' . $image_url);
        }
        // Thêm vào bảng products
        $stmt = $conn->prepare("INSERT INTO products (name, price, stock_quantity, category_id, is_featured, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiiis", $name, $price, $stock_quantity, $category_id, $is_featured, $created_at);
        if ($stmt->execute()) {
            $product_id = $conn->insert_id;
            if ($image_url) {
                $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, 1)");
                $stmt_img->bind_param("is", $product_id, $image_url);
                $stmt_img->execute();
            }
            header("Location: admin_products.php?msg=added");
            exit;
        } else {
            $msg = "Lỗi thêm sản phẩm: " . $stmt->error;
        }
    }
    // Xóa sản phẩm
    if (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['delete_product']);
        // Xóa ảnh vật lý
        $imgs = $conn->query("SELECT image_url FROM product_images WHERE product_id = $product_id");
        if ($imgs) while($img = $imgs->fetch_assoc()) {
            $img_path = __DIR__ . '/../img/' . $img['image_url'];
            if (file_exists($img_path)) @unlink($img_path);
        }
        $conn->query("DELETE FROM product_images WHERE product_id = $product_id");
        $conn->query("DELETE FROM products WHERE id = $product_id");
        header("Location: admin_products.php?msg=deleted");
        exit;
    }
}
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '1=1';
if ($q !== '') {
    $qEsc = $conn->real_escape_string($q);
    $where = "(p.name LIKE '%$qEsc%' OR c.name LIKE '%$qEsc%')";
}
$sql_count = "SELECT COUNT(DISTINCT p.id) as total FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where";
$result_count = $conn->query($sql_count);
$total = $result_count ? intval($result_count->fetch_assoc()['total']) : 0;
$total_pages = max(1, ceil($total / $limit));
$sql = "SELECT p.*, c.name as category, i.image_url FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 WHERE $where ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
// Lấy danh mục cho select
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm - GoodZStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto px-0">
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3">
                    <h2>Quản lý Sản phẩm</h2>
                    <div class="d-flex align-items-center gap-2">
                        <form method="get" class="d-flex" style="gap:8px;">
                            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Tìm sản phẩm hoặc danh mục..." style="min-width:260px;">
                            <button class="btn btn-outline-warning" type="submit">Tìm</button>
                        </form>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addProductModal">+ Thêm sản phẩm</button>
                    </div>
                </div>
                <!-- Modal thêm sản phẩm -->
                <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addProductModalLabel">Thêm sản phẩm mới</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Tên sản phẩm</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Giá</label>
                                        <input type="number" name="price" class="form-control" min="0" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Số lượng</label>
                                        <input type="number" name="stock_quantity" class="form-control" min="0" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Danh mục</label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">-- Chọn danh mục --</option>
                                            <?php if ($categories) while($cat = $categories->fetch_assoc()): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ảnh chính</label>
                                        <input type="file" name="image" class="form-control" accept="image/*" required>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                                        <label class="form-check-label" for="is_featured">Nổi bật</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    <button type="submit" name="add_product" class="btn btn-primary">Thêm</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="content">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Danh mục</th>
                                <th>Trạng thái</th>
                                <th>Ảnh chính</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($result && $result->num_rows > 0):
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= number_format($row['price'], 0, ',', '.') ?>đ</td>
                                    <td><?= $row['stock_quantity'] ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td><?= $row['is_featured'] ? 'Nổi bật' : 'Bình thường' ?></td>
                                    <td><img src="../img/<?= htmlspecialchars($row['image_url']) ?>" alt="Ảnh" style="width:48px;height:48px;border-radius:8px;"></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Sửa</a>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Xóa sản phẩm này?')">
                                            <input type="hidden" name="delete_product" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="8">Không có sản phẩm nào.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <!-- Phân trang -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>&q=<?= urlencode($q) ?>">&laquo; Trước</a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item<?= $i==$page ? ' active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($q) ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>">Tiếp &raquo;</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
