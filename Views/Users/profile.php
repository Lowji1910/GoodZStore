<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result ? $result->fetch_assoc() : null;
if (!$user) {
    echo '<div class="container mt-5">Không tìm thấy thông tin người dùng.</div>';
    exit;
}
include_once __DIR__ . '/../header.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container profile-page mt-5" style="max-width:600px;">
        <h2 class="mb-4">Thông tin cá nhân</h2>
        <table class="table table-bordered">
            <tr><th>Họ tên</th><td><?= htmlspecialchars($user['full_name']) ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
            <tr><th>Điện thoại</th><td><?= htmlspecialchars($user['phone_number']) ?></td></tr>
            <tr><th>Địa chỉ</th><td><?= htmlspecialchars($user['address']) ?></td></tr>
            <tr><th>Quyền</th><td><?= htmlspecialchars($user['role']) ?></td></tr>
            <tr><th>Ngày tạo</th><td><?= $user['created_at'] ?></td></tr>
        </table>
        <div class="d-flex gap-2 justify-content-start">
            <a href="edit_profile.php" class="btn btn-primary">Chỉnh sửa thông tin</a>
            <a href="index.php" class="btn btn-secondary">Về trang chủ</a>
            <a href="login.php?logout=1" class="btn btn-danger">Đăng xuất</a>
        </div>
    </div>
<?php include_once __DIR__ . '/../footer.php'; ?>
</body>
</html>