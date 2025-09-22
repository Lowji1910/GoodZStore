<?php
// Quản lý voucher cho admin
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Voucher - GoodZStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Views/css/layout.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto px-0">
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3">
                    <h2>Quản lý Voucher</h2>
                    <a href="#" class="btn btn-warning">+ Thêm voucher</a>
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
                        require_once __DIR__ . '/../../Models/db.php';
                        $sql = "SELECT * FROM vouchers ORDER BY start_date DESC";
                        $result = $conn->query($sql);
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
                                        <a href="#" class="btn btn-sm btn-danger" onclick="return confirm('Xóa voucher này?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="7">Không có voucher nào.</td></tr>
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
