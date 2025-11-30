
<?php
// User checkout page
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';
if (!isset($_SESSION)) session_start();
// Get user ID
$user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    // Redirect to login if not logged in
    header('Location: /GoodZStore/Views/Users/auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Fetch cart items from database
$items = [];
$total = 0;

$sql = "SELECT c.product_id, c.size_id, c.quantity, 
               p.name, p.price, i.image_url,
               s.size_name
        FROM cart_items c
        JOIN products p ON c.product_id = p.id
        LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1
        LEFT JOIN product_sizes s ON c.size_id = s.id
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $row['qty'] = $row['quantity']; // Map quantity to qty for compatibility
    $row['subtotal'] = $row['qty'] * $row['price'];
    $items[] = $row;
    $total += $row['subtotal'];
}

// Voucher per-order only (no session persistence)
$voucherMsg = '';
$discount = 0;
$appliedCode = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_action'])) {
    if ($_POST['voucher_action'] === 'apply') {
        $code = strtoupper(trim($_POST['voucher_code'] ?? ''));
        if ($code !== '') {
            $stmtV = $conn->prepare("SELECT * FROM vouchers WHERE code = ? LIMIT 1");
            $stmtV->bind_param('s', $code);
            if ($stmtV->execute()) {
                $vr = $stmtV->get_result();
                if ($vr && $vr->num_rows > 0) {
                    $v = $vr->fetch_assoc();
                    $now = new DateTime('now');
                    $start = new DateTime($v['start_date']);
                    $end = new DateTime($v['end_date']);
                    $minAmount = (float)($v['min_order_amount'] ?? 0);
                    $usageLimit = (int)($v['usage_limit'] ?? 0);
                    $usedCount = (int)($v['used_count'] ?? 0);
                    $validTime = ($now >= $start && $now <= $end);
                    $validMin = ($total >= $minAmount);
                    $validUsage = ($usageLimit == 0 || $usedCount < $usageLimit);
                    if ($validTime && $validMin && $validUsage) {
                        if ($v['discount_type'] === 'percentage') {
                            $discount = $total * ((float)$v['discount_value'] / 100.0);
                            if (!is_null($v['max_discount'])) { $discount = min($discount, (float)$v['max_discount']); }
                        } else { $discount = (float)$v['discount_value']; }
                        $discount = max(0, min($discount, $total));
                        $appliedCode = $code;
                        $voucherMsg = 'Áp dụng voucher thành công: ' . htmlspecialchars($code);
                    } else {
                        $voucherMsg = 'Voucher không hợp lệ hoặc không còn hiệu lực.';
                    }
                } else {
                    $voucherMsg = 'Không tìm thấy voucher.';
                }
            }
        }
    } else { // remove
        $appliedCode = '';
        $discount = 0;
        $voucherMsg = 'Đã gỡ voucher.';
    }
}
$finalTotal = max(0, $total - $discount);

// Get user info if logged in
$user_info = ['full_name' => '', 'address' => '', 'phone' => ''];
if (isset($_SESSION['user'])) {
    $user_info['full_name'] = $_SESSION['user']['full_name'] ?? '';
    $user_info['address'] = $_SESSION['user']['address'] ?? '';
    $user_info['phone'] = $_SESSION['user']['phone'] ?? '';
}
?>
<main class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Thanh toán</h2>
            <p class="text-muted">Hoàn tất đơn hàng của bạn</p>
        </div>

        <?php if (!empty($_SESSION['checkout_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['checkout_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php $_SESSION['checkout_error'] = null; unset($_SESSION['checkout_error']); ?>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                <p class="lead text-muted">Giỏ hàng của bạn đang trống.</p>
                <a href="products.php" class="btn btn-primary-custom px-4">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
        <form method="post" action="place_order.php" id="checkoutForm">
            <div class="row g-4">
                <!-- Left Column: Shipping & Payment -->
                <div class="col-lg-7">
                    <!-- Shipping Info -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0"><i class="fas fa-map-marker-alt me-2 text-warning"></i>Thông tin giao hàng</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Họ và tên</label>
                                <input type="text" name="full_name" class="form-control form-control-lg bg-light border-0" placeholder="Nhập họ tên người nhận" value="<?= htmlspecialchars($user_info['full_name']) ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Số điện thoại</label>
                                    <input type="tel" name="phone" class="form-control form-control-lg bg-light border-0" placeholder="Nhập số điện thoại" value="<?= htmlspecialchars($user_info['phone']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Email (Tùy chọn)</label>
                                    <input type="email" name="email" class="form-control form-control-lg bg-light border-0" placeholder="Nhập email để nhận thông báo">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Địa chỉ nhận hàng</label>
                                <input type="text" name="address" class="form-control form-control-lg bg-light border-0" placeholder="Số nhà, tên đường, phường/xã..." value="<?= htmlspecialchars($user_info['address']) ?>" required>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-medium">Ghi chú đơn hàng</label>
                                <textarea name="note" class="form-control bg-light border-0" rows="2" placeholder="Ví dụ: Giao hàng giờ hành chính..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0"><i class="fas fa-credit-card me-2 text-warning"></i>Phương thức thanh toán</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="payment-methods">
                                <div class="form-check p-3 border rounded-3 mb-2 payment-option selected">
                                    <input class="form-check-input" type="radio" name="payment" id="paymentCOD" value="cod" checked>
                                    <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="paymentCOD">
                                        <span>
                                            <i class="fas fa-money-bill-wave me-2 text-success"></i> Thanh toán khi nhận hàng (COD)
                                        </span>
                                    </label>
                                </div>
                                <div class="form-check p-3 border rounded-3 payment-option">
                                    <input class="form-check-input" type="radio" name="payment" id="paymentVNPAY" value="vnpay">
                                    <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="paymentVNPAY">
                                        <span>
                                            <i class="fas fa-qrcode me-2 text-primary"></i> Thanh toán qua VNPAY
                                        </span>
                                        <img src="https://vnpay.vn/assets/images/logo-icon/logo-primary.svg" alt="VNPAY" height="20">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Order Summary -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">Đơn hàng của bạn</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="checkout-items mb-4" style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($items as $it): ?>
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                        <div class="position-relative">
                                            <img src="/GoodZStore/uploads/<?= htmlspecialchars($it['image_url'] ?? 'no-image.jpg') ?>" 
                                                 alt="<?= htmlspecialchars($it['name']) ?>" 
                                                 class="rounded-3 object-fit-cover" style="width: 64px; height: 64px;">
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                                                <?= $it['qty'] ?>
                                            </span>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <h6 class="mb-0 text-truncate" style="max-width: 180px;"><?= htmlspecialchars($it['name']) ?></h6>
                                            <?php if ($it['size_name']): ?>
                                                <small class="text-muted">Size: <?= htmlspecialchars($it['size_name']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fw-bold"><?= number_format($it['subtotal'], 0, ',', '.') ?>đ</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Voucher Form -->
                            <div class="mb-4">
                                <div class="input-group">
                                    <input type="text" form="voucherForm" name="voucher_code" value="<?= htmlspecialchars($appliedCode) ?>" class="form-control" placeholder="Mã giảm giá">
                                    <?php if ($appliedCode !== ''): ?>
                                        <button class="btn btn-outline-danger" type="submit" form="voucherForm" name="voucher_action" value="remove">Gỡ</button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-primary" type="submit" form="voucherForm" name="voucher_action" value="apply">Áp dụng</button>
                                    <?php endif; ?>
                                </div>
                                <?php if ($voucherMsg): ?>
                                    <div class="form-text <?= strpos($voucherMsg, 'thành công') !== false ? 'text-success' : 'text-danger' ?>">
                                        <?= htmlspecialchars($voucherMsg) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Totals -->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tạm tính</span>
                                <span class="fw-bold"><?= number_format($total, 0, ',', '.') ?>đ</span>
                            </div>
                            <?php if ($discount > 0): ?>
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Giảm giá</span>
                                <span>-<?= number_format($discount, 0, ',', '.') ?>đ</span>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phí vận chuyển</span>
                                <span class="text-success">Miễn phí</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold fs-5">Tổng cộng</span>
                                <span class="fw-bold fs-4 text-primary"><?= number_format($finalTotal, 0, ',', '.') ?>đ</span>
                            </div>

                            <?php if ($appliedCode !== ''): ?>
                                <input type="hidden" name="voucher_code" value="<?= htmlspecialchars($appliedCode) ?>">
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary-custom w-100 py-3 fw-bold shadow-sm">
                                Đặt hàng ngay
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Separate form for voucher submission to avoid conflict with main form -->
        <form id="voucherForm" method="post" style="display:none;"></form>
        
        <?php endif; ?>
    </div>
</main>

<style>
.payment-option {
    cursor: pointer;
    transition: all 0.2s;
}
.payment-option:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6 !important;
}
.payment-option.selected {
    border-color: #0d6efd !important;
    background-color: #f0f7ff;
}
.form-control:focus {
    box-shadow: none;
    border-color: #dee2e6;
    background-color: #fff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Highlight selected payment method
    const radios = document.querySelectorAll('input[name="payment"]');
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
            if(this.checked) {
                this.closest('.payment-option').classList.add('selected');
            }
        });
    });
});
</script>

<?php include_once __DIR__ . '/../footer.php'; ?>