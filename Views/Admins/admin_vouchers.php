<?php
// Quản lý voucher cho admin
require_once __DIR__ . '/../../Models/db.php';
$msg = '';
// Thêm voucher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_voucher'])) {
    $code = trim($_POST['code']);
    $discount_type = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $usage_limit = intval($_POST['usage_limit']);
    $created_at = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO vouchers (code, discount_type, discount_value, start_date, end_date, usage_limit, used_count, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
    $stmt->bind_param("ssdssis", $code, $discount_type, $discount_value, $start_date, $end_date, $usage_limit, $created_at);
    if ($stmt->execute()) {
        header("Location: admin_vouchers.php?msg=added");
        exit;
    } else {
        $msg = "Lỗi thêm voucher: " . $stmt->error;
    }
}
// Xóa voucher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_voucher'])) {
    $voucher_id = intval($_POST['delete_voucher']);
    $conn->query("DELETE FROM vouchers WHERE id = $voucher_id");
    header("Location: admin_vouchers.php?msg=deleted");
    exit;
}
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '1=1';
if ($q !== '') {
    $qEsc = $conn->real_escape_string($q);
    $where = "(CAST(id AS CHAR) LIKE '%$qEsc%' OR code LIKE '%$qEsc%' OR discount_type LIKE '%$qEsc%' OR CAST(discount_value AS CHAR) LIKE '%$qEsc%' OR start_date LIKE '%$qEsc%' OR end_date LIKE '%$qEsc%' OR CAST(usage_limit AS CHAR) LIKE '%$qEsc%' OR CAST(used_count AS CHAR) LIKE '%$qEsc%')";
}
$sql_count = "SELECT COUNT(*) as total FROM vouchers WHERE $where";
$result_count = $conn->query($sql_count);
$total = $result_count ? intval($result_count->fetch_assoc()['total']) : 0;
$total_pages = max(1, ceil($total / $limit));
$sql = "SELECT * FROM vouchers WHERE $where ORDER BY start_date DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Voucher - GoodZStore Admin</title>
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
                    <h2>Quản lý Voucher</h2>
                    <div class="d-flex align-items-center gap-3">
                        <form method="get" class="d-flex" style="gap:8px;">
                            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Tìm voucher..." style="min-width:200px;">
                            <button class="btn btn-outline-warning" type="submit"><i class="fas fa-search"></i></button>
                        </form>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addVoucherModal"><i class="fas fa-plus"></i> Thêm</button>
                        <div class="vr"></div>
                        <?php include __DIR__ . '/admin_topbar_notifications.php'; ?>
                    </div>
                </div>
                <div class="p-4">
                    <?php include __DIR__ . '/admin_alerts.php'; ?>
                <!-- Modal thêm voucher -->
                <div class="modal fade" id="addVoucherModal" tabindex="-1" aria-labelledby="addVoucherModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addVoucherModalLabel">Thêm voucher mới</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Mã voucher</label>
                                        <input type="text" name="code" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Loại giảm giá</label>
                                        <select name="discount_type" class="form-select" required>
                                            <option value="percentage">Phần trăm (%)</option>
                                            <option value="fixed">Số tiền (VNĐ)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Giá trị</label>
                                        <input type="number" name="discount_value" class="form-control" min="0" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Thời gian hiệu lực</label>
                                        <div class="d-flex gap-2">
                                            <input type="date" name="start_date" class="form-control" required>
                                            <input type="date" name="end_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Số lần dùng</label>
                                        <input type="number" name="usage_limit" class="form-control" min="1" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    <button type="submit" name="add_voucher" class="btn btn-primary">Thêm</button>
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
                                <th>Mã voucher</th>
                                <th>Loại giảm giá</th>
                                <th>Giá trị</th>
                                <th>Thời gian hiệu lực</th>
                                <th>Số lần dùng còn lại</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($result && $result->num_rows > 0):
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['code']) ?></td>
                                    <td><?= $row['discount_type'] ?></td>
                                    <td><?= number_format($row['discount_value'], 0, ',', '.') ?><?= $row['discount_type'] == 'percentage' ? '%' : 'đ' ?></td>
                                    <td><?= $row['start_date'] ?> - <?= $row['end_date'] ?></td>
                                    <td><?= $row['usage_limit'] - $row['used_count'] ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Sửa</a>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Xóa voucher này?')">
                                            <input type="hidden" name="delete_voucher" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="7">Không có voucher nào.</td></tr>
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
