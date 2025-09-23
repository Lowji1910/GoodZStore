<?php
// User authentication page (login/register)
require_once __DIR__ . '/../../Controllers/auth_controller.php';
include_once __DIR__ . '/../header.php';
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    logout_user();
    header('Location: login.php');
    exit;
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $result = register_user(trim($_POST['full_name']), trim($_POST['email']), $_POST['password'], trim($_POST['phone']));
        if ($result === true) {
            $msg = 'Đăng ký thành công! Bạn có thể đăng nhập.';
        } else {
            $msg = 'Lỗi: ' . htmlspecialchars($result);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <div class="container auth-page">
        <h2>Đăng ký</h2>
        <main>
            <?php if ($msg) { ?>
                <div class="msg"> <?= $msg ?> </div>
            <?php } ?>

            <form class="register-form" method="post" autocomplete="off">
                <input type="text" name="full_name" placeholder="Tên" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <input type="text" name="phone" placeholder="Số điện thoại">
                <button type="submit" name="register">Đăng ký</button>
            </form>

            <div style="text-align:center;margin-top:10px;display:flex;flex-direction:column;gap:8px;align-items:center;">
                <a href="login.php" class="btn btn-link" style="display:inline-block;padding:10px 14px;border-radius:10px;background:#FFD600;color:#222;text-decoration:none;font-weight:600;">Bạn đã có tài khoản? Đăng nhập</a>
                <a href="index.php">Quay lại Trang chủ</a>
            </div>
        </main>
    </div>
    <?php include_once __DIR__ . '/../footer.php'; ?>
    <script src="../ui.js"></script>
 </body>
 </html>