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
    } elseif (isset($_POST['login'])) {
        $role = login_user(trim($_POST['email']), $_POST['password']);
        if ($role === 'admin') {
            header('Location: /Views/Admins/admin_dashboard.php');
            exit;
        } elseif ($role === 'customer') {
            header('Location: index.php');
            exit;
        } else {
            $msg = 'Sai email hoặc mật khẩu, hoặc tài khoản bị khóa.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký / Đăng nhập</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container auth-page">
        <h2>Đăng nhập / Đăng ký</h2>
        <!-- Form đăng nhập/đăng ký sẽ được thêm ở đây -->
        <main>
            <?php if ($msg) { ?>
                <div class="msg"> <?php echo $msg; ?> </div>
            <?php } ?>
            <a href="index.php">Quay lại Trang chủ</a>
        </main>
    </div>
<?php include_once __DIR__ . '/../footer.php'; ?>
</body>
</html>
            <div style="color: red; margin-bottom: 16px; text-align:center;"> <?= $msg ?> </div>
        <?php endif; ?>
        <form class="register-form" method="post" autocomplete="off">
            <h2>Đăng ký</h2>
            <input type="text" name="full_name" placeholder="Tên" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="text" name="phone" placeholder="Số điện thoại">
            <button type="submit" name="register">Đăng ký</button>
        </form>
        <form class="login-form" method="post" autocomplete="off">
            <h2>Đăng nhập</h2>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit" name="login">Đăng nhập</button>
        </form>
        <a href="#">Quên mật khẩu?</a>
    </main>
    <script src="ui.js"></script>
</body>
</html>