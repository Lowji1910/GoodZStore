<?php
// Admin Dashboard Layout
require_once __DIR__ . '/../../Models/db.php';
// Doanh thu hôm nay
$sql = "SELECT SUM(total_amount) as revenue FROM orders WHERE DATE(created_at) = CURDATE()";
$result = $conn->query($sql);
$revenue_today = $result ? number_format($result->fetch_assoc()['revenue'] ?? 0, 0, ',', '.') : '0';
// Đơn hàng mới hôm nay
$sql = "SELECT COUNT(*) as orders FROM orders WHERE DATE(created_at) = CURDATE()";
$result = $conn->query($sql);
$orders_today = $result ? $result->fetch_assoc()['orders'] : 0;
// Khách hàng mới hôm nay
$sql = "SELECT COUNT(*) as users FROM users WHERE DATE(created_at) = CURDATE()";
$result = $conn->query($sql);
$users_today = $result ? $result->fetch_assoc()['users'] : 0;
// Sản phẩm bán chạy nhất
$sql = "SELECT p.name, SUM(od.quantity) as sold FROM order_details od JOIN products p ON od.product_id = p.id GROUP BY p.id ORDER BY sold DESC LIMIT 1";
$result = $conn->query($sql);
$best_product = $result ? $result->fetch_assoc()['name'] ?? '' : '';
// Top khách hàng mua nhiều
$top_customers = [];
$sql = "SELECT u.full_name, COUNT(o.id) as orders FROM orders o JOIN users u ON o.user_id = u.id GROUP BY u.id ORDER BY orders DESC LIMIT 3";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $top_customers[] = $row['full_name'] . ' - ' . $row['orders'] . ' đơn';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GoodZStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="d-flex align-items-center gap-3">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                        <div class="vr"></div>
                        <?php include __DIR__ . '/admin_topbar_notifications.php'; ?>
                    </div>
                </div>
                <div class="p-4">
                    <?php include __DIR__ . '/admin_alerts.php'; ?>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">Doanh thu hôm nay</h5>
                                <p class="card-text fs-4 fw-bold"><?= $revenue_today ?>đ</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">Đơn hàng mới</h5>
                                <p class="card-text fs-4 fw-bold"><?= $orders_today ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">Khách hàng mới</h5>
                                <p class="card-text fs-4 fw-bold"><?= $users_today ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">SP Bán chạy nhất</h5>
                                <p class="card-text fw-bold text-truncate"><?= htmlspecialchars($best_product ?: 'Chưa có') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts & Tables -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white fw-bold">Biểu đồ doanh thu (Tuần này)</div>
                            <div class="card-body">
                                <canvas id="chart-revenue" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white fw-bold">Top khách hàng</div>
                            <ul class="list-group list-group-flush">
                                <?php if (!empty($top_customers)): ?>
                                    <?php foreach ($top_customers as $item): ?>
                                        <li class="list-group-item"><?= htmlspecialchars($item) ?></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item text-muted">Chưa có dữ liệu</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <footer class="pt-3 mt-4 text-muted border-top text-center small">
                    &copy; 2025 GoodZStore Admin. Phiên bản 1.0.
                </footer>
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
                data: [12, 9, 14, 8, 15, 10, 13], // Mock data for demo
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true } }
        }
    });
    </script>
</body>
</html>
