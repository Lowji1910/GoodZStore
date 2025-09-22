<?php
// Báo cáo & Thống kê cho admin
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo & Thống kê - GoodZStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Views/css/layout.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
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
                                    <li class="list-group-item">Áo Thun Basic Nam - 120 lượt bán</li>
                                    <li class="list-group-item">Quần Jeans Xanh Nam - 95 lượt bán</li>
                                    <li class="list-group-item">Giày Sneaker Trắng - 80 lượt bán</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Tỉ lệ sử dụng voucher</div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">SALE10 - 35 lượt</li>
                                    <li class="list-group-item">FREESHIP - 28 lượt</li>
                                    <li class="list-group-item">BLACKFRIDAY - 12 lượt</li>
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
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
            datasets: [{
                label: 'Doanh thu (triệu đồng)',
                data: [120, 95, 140, 110, 150, 130],
                backgroundColor: '#FFD700'
            }]
        }
    });
    // Biểu đồ đơn hàng
    new Chart(document.getElementById('chart-orders').getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
            datasets: [{
                label: 'Số đơn hàng',
                data: [80, 70, 90, 85, 100, 95],
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
