<?php
// Quản lý danh mục cho admin
require_once __DIR__ . '/../../Models/db.php';
$msg = '';
// Thêm danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $created_at = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO categories (name, description, created_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $description, $created_at);
    if ($stmt->execute()) {
        header("Location: admin_categories.php?msg=added");
        exit;
    } else {
        $msg = "Lỗi thêm danh mục: " . $stmt->error;
    }
}
// Xóa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $cat_id = intval($_POST['delete_category']);
    $conn->query("DELETE FROM categories WHERE id = $cat_id");
    header("Location: admin_categories.php?msg=deleted");
    exit;
}
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '1=1';
if ($q !== '') {
    $qEsc = $conn->real_escape_string($q);
    $where = "(CAST(id AS CHAR) LIKE '%$qEsc%' OR name LIKE '%$qEsc%' OR description LIKE '%$qEsc%' OR created_at LIKE '%$qEsc%')";
}
$sql_count = "SELECT COUNT(*) as total FROM categories WHERE $where";
$result_count = $conn->query($sql_count);
$total = $result_count ? intval($result_count->fetch_assoc()['total']) : 0;
$total_pages = max(1, ceil($total / $limit));
$sql = "SELECT * FROM categories WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục - GoodZStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/admin.css">
</head>
<body class="admin">
    <div class="container-fluid">
        <div class="row">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                    <h2>Quản lý Danh mục</h2>
                    <div class="d-flex align-items-center gap-3">
                        <form method="get" class="d-flex" style="gap:8px;">
                            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Tìm danh mục..." style="min-width:200px;">
                            <button class="btn btn-outline-warning" type="submit"><i class="fas fa-search"></i></button>
                        </form>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fas fa-plus"></i> Thêm</button>
                        <div class="vr"></div>
                        <?php include __DIR__ . '/admin_topbar_notifications.php'; ?>
                    </div>
                </div>
                <div class="p-4">
                    <?php include __DIR__ . '/admin_alerts.php'; ?>
                <!-- Modal thêm danh mục -->
                                <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="addCategoryModalLabel">Thêm danh mục mới</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tên danh mục</label>
                                                        <input type="text" name="name" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Mô tả</label>
                                                        <textarea name="description" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                    <button type="submit" name="add_category" class="btn btn-primary">Thêm</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                </div>
                <div class="content">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên danh mục</th>
                                <th>Mô tả</th>
                                <th>Ngày tạo</th>
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
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td><?= $row['created_at'] ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Sửa</a>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Xóa danh mục này?')">
                                            <input type="hidden" name="delete_category" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile;
                        endif;
                        ?>
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
