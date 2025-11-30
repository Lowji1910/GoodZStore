<?php
// Quản lý đơn hàng cho admin
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/notifications.php'; // Include notifications model

$msg = '';
// Cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    
    // Get user_id before update
    $uid = 0;
    $resU = $conn->query("SELECT user_id FROM orders WHERE id = $order_id");
    if ($resU && $resU->num_rows > 0) {
        $uid = intval($resU->fetch_assoc()['user_id']);
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    if ($stmt->execute()) {
        // Notify user
        if ($uid > 0) {
            $statusText = [
                'pending' => 'Chờ xử lý',
                'processing' => 'Đang xử lý',
                'completed' => 'Hoàn thành',
                'cancelled' => 'Đã hủy'
            ][$status] ?? $status;
            
            $notiMsg = "Đơn hàng #$order_id đã được cập nhật trạng thái: $statusText";
            add_notification('Cập nhật đơn hàng', $notiMsg, '/GoodZStore/Views/Users/orders.php', $uid);
        }
        header("Location: admin_orders.php?msg=updated");
        exit;
    }
}
// Xóa đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $order_id = intval($_POST['delete_order']);
    $conn->query("DELETE FROM orders WHERE id = $order_id");
    header("Location: admin_orders.php?msg=deleted");
    exit;
}
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '1=1';
if ($q !== '') {
    $qEsc = $conn->real_escape_string($q);
    $where = "(CAST(o.id AS CHAR) LIKE '%$qEsc%' OR u.full_name LIKE '%$qEsc%' OR u.email LIKE '%$qEsc%' OR u.phone_number LIKE '%$qEsc%' OR o.status LIKE '%$qEsc%' OR CAST(o.total_amount AS CHAR) LIKE '%$qEsc%' OR o.created_at LIKE '%$qEsc%')";
}
$sql_count = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where";
$result_count = $conn->query($sql_count);
$total = $result_count ? intval($result_count->fetch_assoc()['total']) : 0;
$total_pages = max(1, ceil($total / $limit));
$sql = "SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng - GoodZStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/admin.css">
</head>
<body class="admin">
    <div class="container-fluid">
        <div class="row">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="topbar d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                    <h2>Quản lý Đơn hàng</h2>
                    <div class="d-flex align-items-center gap-3">
                        <form method="get" class="d-flex" style="gap:8px;">
                            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Tìm đơn hàng..." style="min-width:200px;">
                            <button class="btn btn-outline-warning" type="submit"><i class="fas fa-search"></i></button>
                        </form>
                        <div class="vr"></div>
                        <?php include __DIR__ . '/admin_topbar_notifications.php'; ?>
                    </div>
                </div>
                <div class="p-4">
                    <?php include __DIR__ . '/admin_alerts.php'; ?>
                <div class="content">
                    <table class="table table-bordered table-hover">
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
                        if ($result && $result->num_rows > 0):
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= number_format($row['total_amount'], 0, ',', '.') ?>đ</td>
                                    <td><?= $row['status'] ?></td>
                                    <td><?= $row['created_at'] ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info text-white" onclick="viewOrder(<?= $row['id'] ?>)">Chi tiết</button>
                                        <form method="post" style="display:inline-block;">
                                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" style="min-width:120px;display:inline-block;">
                                                <option value="pending"<?= $row['status']=='pending'?' selected':'' ?>>Chờ xử lý</option>
                                                <option value="processing"<?= $row['status']=='processing'?' selected':'' ?>>Đang xử lý</option>
                                                <option value="completed"<?= $row['status']=='completed'?' selected':'' ?>>Hoàn thành</option>
                                                <option value="cancelled"<?= $row['status']=='cancelled'?' selected':'' ?>>Đã hủy</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-warning">Cập nhật</button>
                                        </form>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Xóa đơn hàng này?')">
                                            <input type="hidden" name="delete_order" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="6">Không có đơn hàng nào.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <!-- Phân trang -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
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
