<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy();
    session_start();
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        // So sánh bằng password_verify với mật khẩu đã băm (bcrypt)
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            $_SESSION['user_id'] = $user['id'];
            if ($user['role'] === 'admin') {
                header('Location: http://localhost/GoodZStore/Views/Admins/admin_dashboard.php');
                exit;
            } else {
                header('Location: http://localhost/GoodZStore/Views/Users/index.php');
                exit;
            }
        } else {
            $msg = 'Sai email hoặc mật khẩu.';
        }
    } else {
        $msg = 'Vui lòng nhập đầy đủ thông tin.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - GoodZStore</title>
    <link rel="stylesheet" href="../css/layout.css">
    <link rel="stylesheet" href="../css/auth.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container" style="max-width:400px;margin:64px auto;">
        <h2 class="mb-4 text-center">Đăng nhập</h2>
        <?php if ($msg): ?><div class="alert alert-danger"> <?= $msg ?> </div><?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-warning w-100">Đăng nhập</button>
        </form>
        <div class="mt-3 text-center">
            <a href="auth.php">Đăng ký tài khoản mới</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
