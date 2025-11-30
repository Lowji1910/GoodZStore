<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/../../Models/db.php';
include_once __DIR__ . '/../header.php';

$user = $_SESSION['user'];
$msg = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $fullname, $phone, $address, $user['id']);
    
    if ($stmt->execute()) {
        $_SESSION['user']['full_name'] = $fullname;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['address'] = $address;
        $user = $_SESSION['user'];
        $msg = "Cập nhật thông tin thành công!";
    } else {
        $msg = "Lỗi: " . $conn->error;
    }
}

// Get Orders
$orders = [];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<main class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    <div class="list-group list-group-flush rounded-bottom-4">
                        <a href="#profile" class="list-group-item list-group-item-action active py-3" data-bs-toggle="list">
                            <i class="fas fa-user-circle me-2"></i> Thông tin tài khoản
                        </a>
                        <a href="#orders" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                            <i class="fas fa-box me-2"></i> Lịch sử đơn hàng
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action py-3 text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="col-lg-9">
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="fw-bold mb-0">Cập nhật thông tin</h5>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($msg): ?>
                                    <div class="alert alert-success rounded-3"><?= $msg ?></div>
                                <?php endif; ?>
                                
                                <form method="post">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Họ và tên</label>
                                            <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Số điện thoại</label>
                                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Địa chỉ giao hàng</label>
                                            <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-12 text-end mt-4">
                                            <button type="submit" name="update_profile" class="btn btn-primary-custom">
                                                Lưu thay đổi
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Tab -->
                    <div class="tab-pane fade" id="orders">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="fw-bold mb-0">Lịch sử đơn hàng</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($orders)): ?>
                                    <div class="text-center p-5">
                                        <img src="https://cdn-icons-png.flaticon.com/512/2038/2038854.png" alt="Empty" width="100" class="mb-3 opacity-50">
                                        <p class="text-muted">Bạn chưa có đơn hàng nào.</p>
                                        <a href="products.php" class="btn btn-accent">Mua sắm ngay</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="ps-4">Mã đơn</th>
                                                    <th>Ngày đặt</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Trạng thái</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orders as $order): ?>
                                                    <tr>
                                                        <td class="ps-4 fw-bold">#<?= $order['id'] ?></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                                        <td class="fw-bold text-primary"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</td>
                                                        <td>
                                                            <?php
                                                            $statusClass = match($order['status']) {
                                                                'pending' => 'bg-warning text-dark',
                                                                'processing' => 'bg-info text-white',
                                                                'completed' => 'bg-success text-white',
                                                                'cancelled' => 'bg-danger text-white',
                                                                default => 'bg-secondary text-white'
                                                            };
                                                            $statusLabel = match($order['status']) {
                                                                'pending' => 'Chờ xử lý',
                                                                'processing' => 'Đang xử lý',
                                                                'completed' => 'Hoàn thành',
                                                                'cancelled' => 'Đã hủy',
                                                                default => $order['status']
                                                            };
                                                            ?>
                                                            <span class="badge rounded-pill <?= $statusClass ?>"><?= $statusLabel ?></span>
                                                        </td>
                                                        <td class="text-end pe-4">
                                                            <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                Chi tiết
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include_once __DIR__ . '/../footer.php'; ?>
</body>
</html>