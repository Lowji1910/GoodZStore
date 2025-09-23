<?php
// Báo cáo & Thống kê cho admin
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo & Thống kê - GoodZStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="admin">
    <?php
    require_once __DIR__ . '/../../Models/db.php';
    // Doanh thu từng tháng
    $revenueData = [];
    $orderData = [];
    $labels = [];
    $sql = "SELECT MONTH(created_at) as month, SUM(total_amount) as revenue, COUNT(*) as orders FROM orders GROUP BY MONTH(created_at) ORDER BY month";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $labels[] = 'Tháng ' . $row['month'];
            $revenueData[] = round($row['revenue']/1000000, 2); // triệu đồng
            $orderData[] = $row['orders'];
        }
    }
    // Top sản phẩm bán chạy
    $topProducts = [];
    $sql = "SELECT p.name, SUM(od.quantity) as sold FROM order_details od JOIN products p ON od.product_id = p.id GROUP BY p.id ORDER BY sold DESC LIMIT 3";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $topProducts[] = $row['name'] . ' - ' . $row['sold'] . ' lượt bán';
        }
    }
    // Tỉ lệ sử dụng voucher
    $voucherStats = [];
    $sql = "SELECT v.code, COUNT(*) as used FROM orders o JOIN vouchers v ON o.voucher_id = v.id GROUP BY v.code ORDER BY used DESC LIMIT 3";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $voucherStats[] = $row['code'] . ' - ' . $row['used'] . ' lượt';
        }
    }
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto px-0">
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3">
                    <h2>Báo cáo & Thống kê</h2>
                </div>
                <div class="content">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <canvas id="chart-revenue" height="180"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="chart-orders" height="180"></canvas>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Top sản phẩm bán chạy</div>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($topProducts as $item): ?>
                                    <li class="list-group-item"><?= htmlspecialchars($item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Tỉ lệ sử dụng voucher</div>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($voucherStats as $item): ?>
                                    <li class="list-group-item"><?= htmlspecialchars($item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
    // Biểu đồ doanh thu
    new Chart(document.getElementById('chart-revenue').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Doanh thu (triệu đồng)',
                data: <?= json_encode($revenueData) ?>,
                backgroundColor: '#FFD700'
            }]
        }
    });
    // Biểu đồ đơn hàng
    new Chart(document.getElementById('chart-orders').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Số đơn hàng',
                data: <?= json_encode($orderData) ?>,
                borderColor: '#ff6f61',
                backgroundColor: 'rgba(255,111,97,0.2)',
                tension: 0.3,
                fill: true
            }]
        }
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
