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
    
    // Thống kê trạng thái đơn hàng (Pie Chart)
    $statusData = [];
    $statusLabels = [];
    $sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $statusLabels[] = ucfirst($row['status']);
            $statusData[] = $row['count'];
        }
    }

    // Phân trang và Tìm kiếm cho Lịch sử đơn hàng
    $limit = 10;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $where = '1=1';
    if ($q !== '') {
        $qEsc = $conn->real_escape_string($q);
        $where = "(CAST(o.id AS CHAR) LIKE '%$qEsc%' OR u.full_name LIKE '%$qEsc%' OR u.email LIKE '%$qEsc%' OR u.phone_number LIKE '%$qEsc%' OR o.status LIKE '%$qEsc%')";
    }
    
    $sql_count = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where";
    $result_count = $conn->query($sql_count);
    $total = $result_count ? intval($result_count->fetch_assoc()['total']) : 0;
    $total_pages = max(1, ceil($total / $limit));
    
    $sql_orders = "SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";
    $res_orders = $conn->query($sql_orders);
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                    <h2>Báo cáo & Thống kê</h2>
                    <div class="d-flex align-items-center gap-3">
                        <?php include __DIR__ . '/admin_topbar_notifications.php'; ?>
                    </div>
                </div>
                <div class="p-4">
                    <?php include __DIR__ . '/admin_alerts.php'; ?>
                <div class="content">
                    <!-- Charts Section -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Doanh thu</span>
                                    <select class="form-select form-select-sm w-auto" onchange="updateChartType(revenueChart, this.value)">
                                        <option value="bar">Bar</option>
                                        <option value="line">Line</option>
                                    </select>
                                </div>
                                <div class="card-body">
                                    <canvas id="chart-revenue"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Số đơn hàng</span>
                                    <select class="form-select form-select-sm w-auto" onchange="updateChartType(ordersChart, this.value)">
                                        <option value="line">Line</option>
                                        <option value="bar">Bar</option>
                                    </select>
                                </div>
                                <div class="card-body">
                                    <canvas id="chart-orders"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Trạng thái đơn hàng</span>
                                    <select class="form-select form-select-sm w-auto" onchange="updateChartType(statusChart, this.value)">
                                        <option value="pie">Pie</option>
                                        <option value="doughnut">Doughnut</option>
                                        <option value="bar">Bar</option>
                                    </select>
                                </div>
                                <div class="card-body">
                                    <canvas id="chart-status"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
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

                    <!-- Lịch sử đơn hàng gần đây -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Lịch sử đơn hàng</span>
                            <form method="get" class="d-flex" style="gap:8px;">
                                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control form-control-sm" placeholder="Tìm kiếm..." style="min-width:200px;">
                                <button class="btn btn-sm btn-outline-primary" type="submit">Tìm</button>
                            </form>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Khách hàng</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($res_orders && $res_orders->num_rows > 0):
                                            while ($row = $res_orders->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                                <td><?= number_format($row['total_amount'], 0, ',', '.') ?>đ</td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'bg-secondary';
                                                    if ($row['status'] == 'completed') $statusClass = 'bg-success';
                                                    elseif ($row['status'] == 'processing') $statusClass = 'bg-primary';
                                                    elseif ($row['status'] == 'cancelled') $statusClass = 'bg-danger';
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>"><?= $row['status'] ?></span>
                                                </td>
                                                <td><?= $row['created_at'] ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info text-white" onclick="viewOrder(<?= $row['id'] ?>)">Chi tiết</button>
                                                </td>
                                            </tr>
                                            <?php endwhile;
                                        else: ?>
                                            <tr><td colspan="6" class="text-center">Chưa có đơn hàng nào.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>&q=<?= urlencode($q) ?>">&laquo; Trước</a></li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item<?= $i==$page ? ' active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($q) ?>"><?= $i ?></a></li>
                                    <?php endfor; ?>
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>">Tiếp &raquo;</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Chi tiết đơn hàng -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết đơn hàng #<span id="modalOrderId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Thông tin khách hàng</h6>
                            <p><strong>Tên:</strong> <span id="modalCustomerName"></span></p>
                            <p><strong>Email:</strong> <span id="modalCustomerEmail"></span></p>
                            <p><strong>SĐT:</strong> <span id="modalCustomerPhone"></span></p>
                            <p><strong>Địa chỉ:</strong> <span id="modalCustomerAddress"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Thông tin đơn hàng</h6>
                            <p><strong>Ngày đặt:</strong> <span id="modalOrderDate"></span></p>
                            <p><strong>Trạng thái:</strong> <span id="modalOrderStatus"></span></p>
                            <p><strong>Tổng tiền:</strong> <span id="modalOrderTotal" class="text-danger fw-bold"></span></p>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="modalOrderItems"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let revenueChart, ordersChart, statusChart;

    const revenueConfig = {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Doanh thu (triệu đồng)',
                data: <?= json_encode($revenueData) ?>,
                backgroundColor: '#FFD700',
                borderColor: '#FFD700',
                tension: 0.3
            }]
        }
    };

    const ordersConfig = {
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
    };

    const statusConfig = {
        type: 'pie',
        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                label: 'Số lượng',
                data: <?= json_encode($statusData) ?>,
                backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d']
            }]
        }
    };

    function initCharts() {
        revenueChart = new Chart(document.getElementById('chart-revenue'), revenueConfig);
        ordersChart = new Chart(document.getElementById('chart-orders'), ordersConfig);
        statusChart = new Chart(document.getElementById('chart-status'), statusConfig);
    }

    function updateChartType(chart, newType) {
        const config = chart.config;
        config.type = newType;
        
        // Adjust options based on type
        if (newType === 'pie' || newType === 'doughnut') {
            config.data.datasets[0].backgroundColor = ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'];
            delete config.data.datasets[0].borderColor;
            delete config.data.datasets[0].fill;
        } else {
             // Restore default colors if switching back to bar/line
             if (chart === revenueChart) {
                 config.data.datasets[0].backgroundColor = '#FFD700';
                 config.data.datasets[0].borderColor = '#FFD700';
             } else if (chart === ordersChart) {
                 config.data.datasets[0].borderColor = '#ff6f61';
                 config.data.datasets[0].backgroundColor = 'rgba(255,111,97,0.2)';
                 config.data.datasets[0].fill = true;
             }
        }
        
        chart.destroy();
        
        // Re-create chart with new config
        if (chart === revenueChart) revenueChart = new Chart(document.getElementById('chart-revenue'), config);
        if (chart === ordersChart) ordersChart = new Chart(document.getElementById('chart-orders'), config);
        if (chart === statusChart) statusChart = new Chart(document.getElementById('chart-status'), config);
    }

    document.addEventListener('DOMContentLoaded', initCharts);

    async function viewOrder(id) {
        try {
            const res = await fetch('/GoodZStore/Views/Admins/get_order_details.php?id=' + id);
            const data = await res.json();
            if (data.error) {
                alert(data.error);
                return;
            }
            
            const order = data.order;
            document.getElementById('modalOrderId').textContent = order.id;
            document.getElementById('modalCustomerName').textContent = order.full_name;
            document.getElementById('modalCustomerEmail').textContent = order.email;
            document.getElementById('modalCustomerPhone').textContent = order.phone_number;
            document.getElementById('modalCustomerAddress').textContent = order.address;
            document.getElementById('modalOrderDate').textContent = order.created_at;
            document.getElementById('modalOrderStatus').textContent = order.status;
            document.getElementById('modalOrderTotal').textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(order.total_amount);
            
            const tbody = document.getElementById('modalOrderItems');
            tbody.innerHTML = '';
            data.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="/GoodZStore/uploads/${item.image_url}" style="width:40px;height:40px;object-fit:cover;">
                            <span>${item.name}</span>
                        </div>
                    </td>
                    <td>${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price)}</td>
                    <td>${item.quantity}</td>
                    <td>${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price * item.quantity)}</td>
                `;
                tbody.appendChild(tr);
            });
            
            new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
        } catch (e) {
            console.error(e);
            alert('Có lỗi xảy ra khi tải chi tiết đơn hàng');
        }
    }
    </script>
</body>
</html>
