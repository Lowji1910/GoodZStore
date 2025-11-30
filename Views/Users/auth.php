<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../../Models/db.php';

$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // LOGIN
    if ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;
                // Merge cart if needed
                require_once __DIR__ . '/../../Models/cart_functions.php';
                // Logic to merge session cart to DB would go here
                
                $redirect = $_GET['redirect'] ?? 'index.php';
                header("Location: $redirect");
                exit;
            } else {
                $error = "Mật khẩu không chính xác!";
            }
        } else {
            $error = "Email không tồn tại!";
        }
    }
    
    // REGISTER
    elseif ($action === 'register') {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        
        if ($password !== $confirm) {
            $error = "Mật khẩu xác nhận không khớp!";
        } else {
            // Check email
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Email đã được sử dụng!";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->bind_param("sss", $fullname, $email, $hashed);
                
                if ($stmt->execute()) {
                    $success = "Đăng ký thành công! Vui lòng đăng nhập.";
                } else {
                    $error = "Lỗi hệ thống: " . $conn->error;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập / Đăng ký - GoodZStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
    <style>
        body { overflow-x: hidden; }
        .auth-container { min-height: 100vh; }
        .auth-banner {
            background: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            position: relative;
        }
        .auth-banner::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.4));
        }
        .auth-content {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .nav-pills .nav-link {
            color: var(--text-muted);
            font-weight: 600;
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary);
            color: white;
        }
    </style>
</head>
<body>

<div class="row auth-container g-0">
    <!-- Left Side: Banner -->
    <div class="col-lg-7 d-none d-lg-block auth-banner">
        <div class="position-absolute bottom-0 start-0 p-5 text-white" style="z-index: 2;">
            <h1 class="display-4 fw-bold">GoodZ<span class="text-warning">Store</span></h1>
            <p class="lead">Khám phá phong cách thời trang đẳng cấp của riêng bạn.</p>
        </div>
    </div>

    <!-- Right Side: Form -->
    <div class="col-lg-5 auth-content bg-white">
        <div class="auth-card">
            <div class="text-center mb-4">
                <a href="index.php" class="text-decoration-none">
                    <h2 class="fw-bold text-dark">Chào mừng trở lại! 👋</h2>
                </a>
                <p class="text-muted">Vui lòng nhập thông tin để tiếp tục.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success rounded-3 border-0 shadow-sm mb-4">
                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <ul class="nav nav-pills nav-fill mb-4 bg-light p-1 rounded-3" id="authTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="login-tab" data-bs-toggle="pill" data-bs-target="#login" type="button" role="tab">Đăng nhập</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="register-tab" data-bs-toggle="pill" data-bs-target="#register" type="button" role="tab">Đăng ký</button>
                </li>
            </ul>

            <div class="tab-content" id="authTabContent">
                <!-- Login Form -->
                <div class="tab-pane fade show active" id="login" role="tabpanel">
                    <form method="post">
                        <input type="hidden" name="action" value="login">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="loginEmail" name="email" placeholder="name@example.com" required>
                            <label for="loginEmail">Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Password" required>
                            <label for="loginPassword">Mật khẩu</label>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label text-muted" for="remember">Ghi nhớ</label>
                            </div>
                            <a href="#" class="text-warning text-decoration-none small fw-bold">Quên mật khẩu?</a>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100 py-3 mb-3">Đăng nhập</button>
                        
                        <div class="text-center text-muted small">
                            Hoặc đăng nhập bằng
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-outline-secondary w-50"><i class="fab fa-google text-danger me-2"></i>Google</button>
                            <button type="button" class="btn btn-outline-secondary w-50"><i class="fab fa-facebook text-primary me-2"></i>Facebook</button>
                        </div>
                    </form>
                </div>

                <!-- Register Form -->
                <div class="tab-pane fade" id="register" role="tabpanel">
                    <form method="post">
                        <input type="hidden" name="action" value="register">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="regName" name="fullname" placeholder="Họ tên" required>
                            <label for="regName">Họ và tên</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="regEmail" name="email" placeholder="name@example.com" required>
                            <label for="regEmail">Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="regPass" name="password" placeholder="Password" required>
                            <label for="regPass">Mật khẩu</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="regConfirm" name="confirm_password" placeholder="Confirm Password" required>
                            <label for="regConfirm">Xác nhận mật khẩu</label>
                        </div>
                        <button type="submit" class="btn btn-accent w-100 py-3">Tạo tài khoản</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>