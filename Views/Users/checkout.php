
<?php
// User checkout page
include_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../Models/db.php';
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Convert old cart format to new format
if (!empty($_SESSION['cart'])) {
    $firstItem = reset($_SESSION['cart']);
    // Check if old format (array keys are product_ids, values are quantities)
    if (is_int($firstItem)) {
        $oldCart = $_SESSION['cart'];
        $_SESSION['cart'] = [];
        foreach ($oldCart as $pid => $qty) {
            $_SESSION['cart'][] = [
                'product_id' => $pid,
                'size_id' => 0,
                'quantity' => $qty
            ];
        }
    }
}

// Lấy dữ liệu giỏ hàng từ DB với ảnh sản phẩm và size
$items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cartItem) {
        $product_id = $cartItem['product_id'];
        $size_id = $cartItem['size_id'];
        $qty = $cartItem['quantity'];
        
        $sql = "SELECT p.id, p.name, p.price, i.image_url 
                FROM products p 
                LEFT JOIN product_images i ON p.id = i.product_id AND i.is_main = 1
                WHERE p.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $row['qty'] = $qty;
            $row['size_id'] = $size_id;
            $row['size_name'] = null;
            
            // Lấy size name
            if ($size_id > 0) {
                $sqlSize = "SELECT size_name FROM product_sizes WHERE id = ?";
                $stmtSize = $conn->prepare($sqlSize);
                $stmtSize->bind_param("i", $size_id);
                $stmtSize->execute();
                $resSize = $stmtSize->get_result();
                if ($sizeRow = $resSize->fetch_assoc()) {
                    $row['size_name'] = $sizeRow['size_name'];
                }
            }
            
            $row['subtotal'] = $qty * $row['price'];
            $items[] = $row;
            $total += $row['subtotal'];
        }
    }
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

?>
<main>
    <h2>Thông tin thanh toán</h2>
    <?php if (!empty($_SESSION['checkout_error'])): ?>
        <div class="alert alert-danger" role="alert" style="margin:10px 0;">
            <?= htmlspecialchars($_SESSION['checkout_error']) ?>
        </div>
        <?php $_SESSION['checkout_error'] = null; unset($_SESSION['checkout_error']); ?>
    <?php endif; ?>
    <?php if (empty($items)): ?>
        <p>Giỏ hàng trống. <a href="products.php">Tiếp tục mua sắm</a></p>
    <?php else: ?>
    <div class="row" style="display:grid;grid-template-columns:1.2fr .8fr;gap:24px;">
        <form class="checkout-form" method="post" action="place_order.php">
            <input type="text" name="full_name" placeholder="Tên người mua" required>
            <input type="text" name="address" placeholder="Địa chỉ nhận hàng" required>
            <input type="tel" name="phone" placeholder="Số điện thoại" required>
            <select name="payment" required>
                <option value="cod">Thanh toán khi nhận hàng</option>
                <option value="bank">Chuyển khoản ngân hàng</option>
            </select>
            <textarea name="note" placeholder="Ghi chú đơn hàng" rows="2"></textarea>
            <?php if ($appliedCode !== ''): ?>
                <input type="hidden" name="voucher_code" value="<?= htmlspecialchars($appliedCode) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-order" id="btn-place-order">Đặt hàng</button>
        </form>
        <div class="checkout-summary">
            <h3>Đơn hàng của bạn</h3>
            <div class="checkout-items">
                <?php foreach ($items as $it): ?>
                    <div class="checkout-item">
                        <img src="/GoodZStore/uploads/<?= htmlspecialchars($it['image_url'] ?? 'no-image.jpg') ?>" 
                             alt="<?= htmlspecialchars($it['name']) ?>" 
                             class="checkout-item-img">
                        <div class="checkout-item-info">
                            <h4><?= htmlspecialchars($it['name']) ?></h4>
                            <?php if ($it['size_name']): ?>
                                <p class="item-size">Size: <strong><?= htmlspecialchars($it['size_name']) ?></strong></p>
                            <?php endif; ?>
                            <p class="item-quantity">Số lượng: <?= $it['qty'] ?></p>
                            <p class="item-price"><?= number_format($it['subtotal'], 0, ',', '.') ?>đ</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p>Tổng tiền: <span><?= number_format($total, 0, ',', '.') ?>đ</span></p>
            <form method="post" style="display:flex;gap:8px;align-items:center;margin:8px 0;">
                <input type="text" name="voucher_code" value="<?= htmlspecialchars($appliedCode) ?>" placeholder="Nhập mã voucher" class="form-control" style="max-width:200px;">
                <button class="btn btn-warning" type="submit" name="voucher_action" value="apply">Áp dụng</button>
                <?php if ($appliedCode !== ''): ?>
                    <button class="btn btn-secondary" type="submit" name="voucher_action" value="remove">Gỡ</button>
                <?php endif; ?>
            </form>
            <?php if ($voucherMsg): ?>
                <div class="alert alert-info" style="padding:8px 12px; border-radius:8px;"> <?= htmlspecialchars($voucherMsg) ?> </div>
            <?php endif; ?>
            <?php if ($discount > 0): ?>
                <p>Giảm giá: <span>-<?= number_format($discount, 0, ',', '.') ?>đ</span></p>
            <?php endif; ?>
            <p><b>Thành tiền:</b> <span><b><?= number_format($finalTotal, 0, ',', '.') ?>đ</b></span></p>
        </div>
    </div>
    <?php endif; ?>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
<link rel="stylesheet" href="../css/checkout.css">
<script src="../ui.js"></script>