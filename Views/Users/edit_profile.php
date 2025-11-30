<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
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
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $sql = "UPDATE users SET full_name=?, phone_number=?, address=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $full_name, $phone, $address, $user_id);
    if ($stmt->execute()) {
        $msg = 'Cập nhật thành công!';
        // Cập nhật lại dữ liệu mới
        $user['full_name'] = $full_name;
        $user['phone_number'] = $phone;
        $user['address'] = $address;
    } else {
        $msg = 'Lỗi cập nhật: ' . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa thông tin cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5" style="max-width:500px;">
        <h2>Chỉnh sửa thông tin cá nhân</h2>
        <?php if ($msg): ?><div class="alert alert-info"> <?= $msg ?> </div><?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Họ tên</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Điện thoại</label>
                <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Địa chỉ</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            <a href="profile.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</body>
</html>
