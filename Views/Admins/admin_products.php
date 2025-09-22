<?php
// Quản lý sản phẩm cho admin
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm - GoodZStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Views/css/layout.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto px-0">
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3">
                    <h2>Quản lý Sản phẩm</h2>
                    <a href="#" class="btn btn-warning">+ Thêm sản phẩm</a>
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
                        require_once __DIR__ . '/../../Models/db.php';
                        $sql = "SELECT p.*, c.name as category, i.image_url FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1 ORDER BY p.created_at DESC";
                        $result = $conn->query($sql);
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
                                        <a href="#" class="btn btn-sm btn-danger" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="8">Không có sản phẩm nào.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
