<?php
// Admin users management page
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng - GoodZStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Views/css/layout.css">
    <link rel="stylesheet" href="/Views/css/admin.css">
</head>
<body>
<?php
require_once __DIR__ . '/../../Models/db.php';
include_once __DIR__ . '/admin_sidebar.php';

// Xử lý xóa user
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = intval($_POST['delete_user']);
    // Không cho xóa admin chính mình hoặc user id <= 0
    if ($user_id > 0) {
        $del = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $del->bind_param("i", $user_id);
        if ($del->execute()) {
            $msg = "Đã xóa user thành công!";
        } else {
            $msg = "Lỗi xóa user: " . $del->error;
        }
    }
}
?>
<div class="container admin-users-page">
    <h2>Quản lý người dùng</h2>
    <?php if ($msg): ?><div style="color:green; margin-bottom:12px;"> <?= $msg ?> </div><?php endif; ?>
    <table border="1" cellpadding="8" style="width:100%;margin-top:24px;border-radius:8px;box-shadow:0 2px 8px #eee;">
        <thead style="background:#f5f5f5;">
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
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $result = $conn->query($sql);
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
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="delete_user" value="<?= $row['id'] ?>">
                            <button type="submit" onclick="return confirm('Bạn có chắc muốn xóa user này?')">Xóa</button>
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
</div>
<?php
include_once __DIR__ . '/../footer.php';
?>
