<!-- Dependencies -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
<link rel="stylesheet" href="/GoodZStore/Views/css/admin.css">
<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-3 col-lg-2 px-0 position-fixed h-100 bg-dark admin-sidebar">
    <div class="p-4 text-center border-bottom border-secondary">
        <h4 class="fw-bold text-white mb-0">GoodZ<span class="text-warning">Admin</span></h4>
    </div>
    <div class="p-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>" href="admin_dashboard.php">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_products.php' ? 'active' : '' ?>" href="admin_products.php">
                    <i class="fas fa-tshirt me-2"></i> Sản phẩm
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_categories.php' ? 'active' : '' ?>" href="admin_categories.php">
                    <i class="fas fa-tags me-2"></i> Danh mục
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : '' ?>" href="admin_orders.php">
                    <i class="fas fa-shopping-cart me-2"></i> Đơn hàng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : '' ?>" href="admin_users.php">
                    <i class="fas fa-users me-2"></i> Người dùng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contents.php' ? 'active' : '' ?>" href="contents.php">
                    <i class="fas fa-images me-2"></i> Banner & Content
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_reviews.php' ? 'active' : '' ?>" href="admin_reviews.php">
                    <i class="fas fa-star me-2"></i> Đánh giá
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_vouchers.php' ? 'active' : '' ?>" href="admin_vouchers.php">
                    <i class="fas fa-ticket-alt me-2"></i> Voucher
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_reports.php' ? 'active' : '' ?>" href="admin_reports.php">
                    <i class="fas fa-chart-bar me-2"></i> Báo cáo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_ai_training.php' ? 'active' : '' ?>" href="admin_ai_training.php">
                    <i class="fas fa-robot me-2"></i> AI Training
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-warning" href="../Users/index.php">
                    <i class="fas fa-home me-2"></i> Về trang chủ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="../Users/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- Spacer for sidebar -->
<div class="col-md-3 col-lg-2"></div>