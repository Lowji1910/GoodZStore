<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
include_once __DIR__ . '/../header.php';

// Enforce login
if (!isset($_SESSION['user']['id'])) {
    header('Location: auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user']['id'];

// Fetch orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<main class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0">Lịch sử đơn hàng</h2>
                    <a href="products.php" class="btn btn-outline-primary rounded-pill">
                        <i class="fas fa-shopping-bag me-2"></i>Mua sắm tiếp
                    </a>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                        <div class="mb-3">
                            <i class="fas fa-box-open text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="fw-bold text-muted">Chưa có đơn hàng nào</h4>
                        <p class="text-muted mb-4">Bạn chưa mua sản phẩm nào từ cửa hàng của chúng tôi.</p>
                        <a href="products.php" class="btn btn-primary-custom px-4">Khám phá ngay</a>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">Mã đơn</th>
                                        <th class="py-3">Ngày đặt</th>
                                        <th class="py-3">Tổng tiền</th>
                                        <th class="py-3">Trạng thái</th>
                                        <th class="py-3 text-end pe-4">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold">#<?= $order['id'] ?></td>
                                            <td class="text-muted"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td class="fw-bold text-primary"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</td>
                                            <td>
                                                <?php
                                                $statusClass = 'bg-secondary';
                                                $statusText = $order['status'];
                                                switch (strtolower($order['status'])) {
                                                    case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                                    case 'processing': $statusClass = 'bg-info text-white'; break;
                                                    case 'shipped': $statusClass = 'bg-primary text-white'; break;
                                                    case 'completed': $statusClass = 'bg-success text-white'; break;
                                                    case 'cancelled': $statusClass = 'bg-danger text-white'; break;
                                                }
                                                ?>
                                                <span class="badge rounded-pill <?= $statusClass ?> px-3 py-2">
                                                    <?= htmlspecialchars(ucfirst($statusText)) ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="order_success.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-light border rounded-pill px-3">
                                                    Chi tiết <i class="fas fa-arrow-right ms-1 small"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.table > :not(caption) > * > * {
    padding: 1rem 0.5rem;
    border-bottom-color: #f0f0f0;
}
.table tbody tr:hover {
    background-color: #f8f9fa;
}
</style>

<?php include_once __DIR__ . '/../footer.php'; ?>
