<?php
// Admin Dashboard Layout
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>GoodZStore Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Views/css/layout.css">
    <link rel="stylesheet" href="/Views/css/sidebar_admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-0">
                <!-- Topbar -->
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3">
                    <form class="d-flex" style="max-width:350px;">
                        <input class="form-control me-2" type="search" placeholder="Tìm kiếm sản phẩm, đơn hàng..." aria-label="Search">
                        <button class="btn btn-outline-warning" type="submit">🔍</button>
                    </form>
                    <div class="d-flex align-items-center gap-4">
                        <span class="badge bg-danger">3</span> <!-- Thông báo mẫu -->
                        <span class="admin-avatar">A</span>
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">Admin</button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Profile</a></li>
                                <li><a class="dropdown-item" href="#">Đổi mật khẩu</a></li>
                                <li><a class="dropdown-item" href="/Views/Users/auth.php?logout=1">Đăng xuất</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Content -->
                <div class="content">
                    <h2>Dashboard Tổng quan</h2>
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Doanh thu hôm nay</h5>
                                    <p class="card-text fs-4 text-success">₫12,500,000</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Đơn hàng mới</h5>
                                    <p class="card-text fs-4 text-primary">15</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Khách hàng mới</h5>
                                    <p class="card-text fs-4 text-info">7</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Sản phẩm bán chạy</h5>
                                    <p class="card-text fs-5">Áo Thun Basic Nam</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <canvas id="chart-revenue" height="180"></canvas>
                        </div>
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header">Top khách hàng mua nhiều</div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Nguyễn Văn A - 5 đơn</li>
                                    <li class="list-group-item">Trần Thị B - 4 đơn</li>
                                    <li class="list-group-item">Lê Văn C - 3 đơn</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="footer">
                    &copy; 2025 GoodZStore Admin. Phiên bản 1.0. Liên hệ hỗ trợ: support@goodzstore.com
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    // Biểu đồ doanh thu mẫu
    const ctx = document.getElementById('chart-revenue').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'],
            datasets: [{
                label: 'Doanh thu (triệu đồng)',
                data: [12, 9, 14, 8, 15, 10, 13],
                borderColor: '#FFD700',
                backgroundColor: 'rgba(255,215,0,0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } }
        }
    });
    </script>
</body>
</html>
