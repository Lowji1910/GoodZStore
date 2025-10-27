<!-- Sidebar quản trị admin với icon và style nổi bật -->
<link rel="stylesheet" href="/GoodZStore/Views/css/sidebar_admin.css">
<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-sidebar">
    <div class="sidebar-heading">GoodZStore<br><span>Admin</span></div>
    <div class="sidebar-menu">
        <a href="admin_dashboard.php" class="sidebar-item<?= $current=='admin_dashboard.php' ? ' sidebar-active' : '' ?>"><span>🏠</span> Dashboard</a>
        <a href="admin_products.php" class="sidebar-item<?= $current=='admin_products.php' ? ' sidebar-active' : '' ?>"><span>🧥</span> Quản lý Sản phẩm</a>
        <a href="admin_categories.php" class="sidebar-item<?= $current=='admin_categories.php' ? ' sidebar-active' : '' ?>"><span>📁</span> Quản lý Danh mục</a>
        <a href="contents.php" class="sidebar-item<?= $current=='contents.php' ? ' sidebar-active' : '' ?>"><span>🖼️</span> Quản lý Banners</a>
    <a href="admin_orders.php" class="sidebar-item<?= $current=='admin_orders.php' ? ' sidebar-active' : '' ?>"><span>📦</span> Quản lý Đơn hàng</a>
    <a href="admin_order_history.php" class="sidebar-item<?= $current=='admin_order_history.php' ? ' sidebar-active' : '' ?>"><span>🕓</span> Lịch sử Đơn hàng</a>
        <a href="admin_users.php" class="sidebar-item<?= $current=='admin_users.php' ? ' sidebar-active' : '' ?>"><span>🧑‍💼</span> Quản lý Người dùng</a>
        <a href="admin_vouchers.php" class="sidebar-item<?= $current=='admin_vouchers.php' ? ' sidebar-active' : '' ?>"><span>🎫</span> Quản lý Voucher</a>
        <a href="admin_reviews.php" class="sidebar-item<?= $current=='admin_reviews.php' ? ' sidebar-active' : '' ?>"><span>⭐</span> Quản lý Review</a>
        <a href="admin_ai_training.php" class="sidebar-item<?= $current=='admin_ai_training.php' ? ' sidebar-active' : '' ?>"><span>🤖</span> AI Training</a>
        <a href="admin_reports.php" class="sidebar-item<?= $current=='admin_reports.php' ? ' sidebar-active' : '' ?>"><span>📊</span> Báo cáo/Thống kê</a>
    <a href="/GoodZStore/Views/Users/login.php" class="sidebar-item" style="margin-top:24px;color:#ff6f61;font-weight:bold;"><span>🚪</span> Đăng xuất</a>
    </div>
</div>