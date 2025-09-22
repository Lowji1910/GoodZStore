<?php
// Trang đăng ký
session_start();
require_once __DIR__ . '/../../Controllers/auth_controller.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = register_user(trim($_POST['full_name']), trim($_POST['email']), $_POST['password'], trim($_POST['phone']));
    if ($result === true) {
        $msg = 'Đăng ký thành công! Bạn có thể đăng nhập.';
    } else {
        $msg = 'Lỗi: ' . htmlspecialchars($result);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký - GoodZStore</title>
    <link rel="stylesheet" href="../css/layout.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container" style="max-width:400px;margin:64px auto;">
        <h2 class="mb-4 text-center">Đăng ký</h2>
        <?php if ($msg): ?><div class="alert alert-success"> <?= $msg ?> </div><?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="full_name" class="form-label">Họ tên</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" id="phone" name="phone">
            </div>
            <button type="submit" class="btn btn-success w-100">Đăng ký</button>
        </form>
        <div class="mt-3 text-center">
            <a href="login.php">Đã có tài khoản? Đăng nhập</a>
        </div>
    </div>
</body>
</html>
