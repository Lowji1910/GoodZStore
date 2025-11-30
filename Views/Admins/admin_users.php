<?php
// Admin users management page
require_once __DIR__ . '/../../Models/db.php';
$msg = '';
// Thêm user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $created_at = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone_number, address, role, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $full_name, $email, $phone, $address, $role, $password, $created_at);
    if ($stmt->execute()) {
        header("Location: admin_users.php?msg=added");
        exit;
    } else {
        $msg = "Lỗi thêm user: " . $stmt->error;
    }
}
// Xóa user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = intval($_POST['delete_user']);
    $conn->query("DELETE FROM users WHERE id = $user_id");
    header("Location: admin_users.php?msg=deleted");
    exit;
}
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '1=1';
if ($q !== '') {
    $qEsc = $conn->real_escape_string($q);
    $where = "(CAST(id AS CHAR) LIKE '%$qEsc%' OR full_name LIKE '%$qEsc%' OR email LIKE '%$qEsc%' OR phone_number LIKE '%$qEsc%' OR address LIKE '%$qEsc%' OR role LIKE '%$qEsc%' OR created_at LIKE '%$qEsc%')";
}
$sql_count = "SELECT COUNT(*) as total FROM users WHERE $where";
$result_count = $conn->query($sql_count);
$total = $result_count ? intval($result_count->fetch_assoc()['total']) : 0;
$total_pages = max(1, ceil($total / $limit));
$sql = "SELECT * FROM users WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng - GoodZStore Admin</title>
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
                                        <h2>Quản lý người dùng</h2>
                                        <div class="d-flex align-items-center gap-3">
                                            <form method="get" class="d-flex" style="gap:8px;">
                                                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Tìm người dùng..." style="min-width:200px;">
                                                <button class="btn btn-outline-warning" type="submit"><i class="fas fa-search"></i></button>
                                            </form>
                                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-plus"></i> Thêm</button>
                                            <div class="vr"></div>
                                            <?php include __DIR__ . '/admin_topbar_notifications.php'; ?>
                                        </div>
                                </div>
                                <div class="p-4">
                                    <?php include __DIR__ . '/admin_alerts.php'; ?>
                                <!-- Modal thêm user -->
                                <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="addUserModalLabel">Thêm người dùng mới</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Họ tên</label>
                                                        <input type="text" name="full_name" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="email" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Điện thoại</label>
                                                        <input type="text" name="phone_number" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Địa chỉ</label>
                                                        <input type="text" name="address" class="form-control">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Quyền</label>
                                                        <select name="role" class="form-select" required>
                                                            <option value="customer">Khách hàng</option>
                                                            <option value="admin">Admin</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Mật khẩu</label>
                                                        <input type="password" name="password" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                    <button type="submit" name="add_user" class="btn btn-primary">Thêm</button>
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
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                                <th>Địa chỉ</th>
                                <th>Quyền</th>
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
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone_number']) ?></td>
                                    <td><?= htmlspecialchars($row['address']) ?></td>
                                    <td><?= $row['role'] ?></td>
                                    <td><?= $row['created_at'] ?></td>
                                    <td>
                                        <?php if ($row['role'] !== 'admin'): ?>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa user này?')">
                                            <input type="hidden" name="delete_user" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                        <?php else: ?>
                                        <span style="color:#888;">Admin</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="8">Không có người dùng nào.</td></tr>
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

