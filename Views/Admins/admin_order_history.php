<?php
require_once __DIR__ . '/../../Models/db.php';
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = "(o.status = 'completed' OR o.status = 'cancelled')";
if ($q !== '') {
    $qEsc = $conn->real_escape_string($q);
    $where .= " AND (CAST(o.id AS CHAR) LIKE '%$qEsc%' OR u.full_name LIKE '%$qEsc%' OR u.email LIKE '%$qEsc%' OR u.phone_number LIKE '%$qEsc%' OR CAST(o.total_amount AS CHAR) LIKE '%$qEsc%' OR o.created_at LIKE '%$qEsc%')";
}
$sql_count = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where";
$result_count = $conn->query($sql_count);
$total = $result_count ? intval($result_count->fetch_assoc()['total']) : 0;
$total_pages = max(1, ceil($total / $limit));
$sql = "SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử Đơn hàng - GoodZStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/admin.css">
</head>
<body class="admin">
    <div class="container-fluid">
        <div class="row">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto px-0">
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3">
                    <h2>Lịch sử Đơn hàng</h2>
                    <form method="get" class="d-flex" style="gap:8px;">
                        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Tìm ID, khách, email, sđt, tổng tiền..." style="min-width:320px;">
                        <button class="btn btn-outline-warning" type="submit">Tìm</button>
                    </form>
                </div>
                <div class="content">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($result && $result->num_rows > 0):
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= number_format($row['total_amount'], 0, ',', '.') ?>đ</td>
                                    <td><?= $row['status'] ?></td>
                                    <td><?= $row['created_at'] ?></td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="5">Không có đơn hàng nào.</td></tr>
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
