<?php
// Quản lý review cho admin
require_once __DIR__ . '/../../Models/db.php';
$msg = '';
// Xóa review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $review_id = intval($_POST['delete_review']);
    $conn->query("DELETE FROM reviews WHERE id = $review_id");
    header("Location: admin_reviews.php?msg=deleted");
    exit;
}
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$sql_count = "SELECT COUNT(*) as total FROM reviews";
$result_count = $conn->query($sql_count);
$total = $result_count ? intval($result_count->fetch_assoc()['total']) : 0;
$total_pages = ceil($total / $limit);
$sql = "SELECT r.*, u.full_name, p.name as product_name FROM reviews r LEFT JOIN users u ON r.user_id = u.id LEFT JOIN products p ON r.product_id = p.id ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Review - GoodZStore Admin</title>
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
                    <h2>Quản lý Review</h2>
                    <div class="d-flex align-items-center gap-3">
                        <?php include __DIR__ . '/admin_topbar_notifications.php'; ?>
                    </div>
                </div>
                <div class="p-4">
                    <?php include __DIR__ . '/admin_alerts.php'; ?>
                <div class="content">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Người dùng</th>
                                <th>Sản phẩm</th>
                                <th>Rating</th>
                                <th>Bình luận</th>
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
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= $row['rating'] ?></td>
                                    <td><?= htmlspecialchars($row['comment']) ?></td>
                                    <td><?= $row['created_at'] ?></td>
                                    <td>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Xóa review này?')">
                                            <input type="hidden" name="delete_review" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="7">Không có review nào.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <!-- Phân trang -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>">&laquo; Trước</a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item<?= $i==$page ? ' active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>">Tiếp &raquo;</a></li>
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
