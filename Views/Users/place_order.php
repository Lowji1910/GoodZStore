<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/vnpay_helper.php';

// Basic validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['checkout_error'] = 'Phương thức không hợp lệ.';
    header('Location: checkout.php');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$payment = trim($_POST['payment'] ?? 'cod');
$note = trim($_POST['note'] ?? '');
$voucher_code = strtoupper(trim($_POST['voucher_code'] ?? ''));

if ($full_name === '' || $address === '' || $phone === '') {
    $_SESSION['checkout_error'] = 'Vui lòng điền đầy đủ thông tin giao hàng.';
    header('Location: checkout.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    $_SESSION['checkout_error'] = 'Giỏ hàng trống.';
    header('Location: checkout.php');
    exit;
}

// Recompute totals server-side
$items = [];
$total = 0.0;
foreach ($_SESSION['cart'] as $cartItem) {
    $product_id = intval($cartItem['product_id']);
    $size_id = intval($cartItem['size_id'] ?? 0);
    $qty = intval($cartItem['quantity'] ?? 1);

    $sql = "SELECT p.id, p.name, p.price FROM products p WHERE p.id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $price = (float)$row['price'];
        $size_name = null;
        if ($size_id > 0) {
            $s = $conn->prepare('SELECT size_name FROM product_sizes WHERE id = ? LIMIT 1');
            $s->bind_param('i', $size_id);
            $s->execute();
            $sr = $s->get_result();
            if ($srow = $sr->fetch_assoc()) $size_name = $srow['size_name'];
        }
        $subtotal = $price * $qty;
        $items[] = [
            'product_id' => $product_id,
            'size_id' => $size_id,
            'size_name' => $size_name,
            'quantity' => $qty,
            'price' => $price,
            'subtotal' => $subtotal
        ];
        $total += $subtotal;
    }
}

$discount = 0.0;
if ($voucher_code !== '') {
    $stmtV = $conn->prepare('SELECT * FROM vouchers WHERE code = ? LIMIT 1');
    $stmtV->bind_param('s', $voucher_code);
    $stmtV->execute();
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
        }
    }
}

$finalTotal = max(0, $total - $discount);

// Insert order
$user_id = isset($_SESSION['user']['id']) ? intval($_SESSION['user']['id']) : 0;
$status = ($payment === 'bank') ? 'pending' : 'pending';
$shipping_address = $address;
$payment_method = $payment;

$stmtO = $conn->prepare('INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method) VALUES (?, ?, ?, ?, ?)');
if (!$stmtO) {
    $_SESSION['checkout_error'] = 'Lỗi hệ thống khi tạo đơn hàng.';
    header('Location: checkout.php');
    exit;
}
$stmtO->bind_param('idsss', $user_id, $finalTotal, $status, $shipping_address, $payment_method);
if (!$stmtO->execute()) {
    $_SESSION['checkout_error'] = 'Không thể lưu đơn hàng: ' . $stmtO->error;
    header('Location: checkout.php');
    exit;
}
$order_id = $conn->insert_id;

// Insert order items
$stmtItem = $conn->prepare('INSERT INTO order_items (order_id, product_id, size_id, size_name, quantity, price) VALUES (?, ?, ?, ?, ?, ?)');
foreach ($items as $it) {
    $p_order_id = $order_id;
    $p_product_id = intval($it['product_id']);
    $p_size_id = $it['size_id'] > 0 ? intval($it['size_id']) : 0;
    $p_size_name = $it['size_name'] ?? null;
    $p_quantity = intval($it['quantity']);
    $p_price = floatval($it['price']);
    $stmtItem->bind_param('iiisid', $p_order_id, $p_product_id, $p_size_id, $p_size_name, $p_quantity, $p_price);
    $stmtItem->execute();
}

// Update voucher usage count if applied
if ($voucher_code !== '') {
    $conn->query('UPDATE vouchers SET used_count = used_count + 1 WHERE code = "' . $conn->real_escape_string($voucher_code) . '"');
}

// Clear cart if COD, otherwise redirect to VNPAY
if ($payment === 'bank') {
    // Build VNPAY URL and redirect
    $desc = 'Thanh toan don #' . $order_id;
    $vnpUrl = build_vnpay_url($order_id, $finalTotal, $desc);
    // Redirect to VNPAY (or show error URL)
    header('Location: ' . $vnpUrl);
    exit;
} else {
    // COD: mark order as pending and clear cart
    unset($_SESSION['cart']);
    header('Location: order_success.php?order_id=' . $order_id);
    exit;
}

?>
<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/notifications.php';

// Ensure cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: checkout.php');
    exit;
}

// Validate form inputs
$full_name = trim($_POST['full_name'] ?? '');
$address   = trim($_POST['address'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$payment   = trim($_POST['payment'] ?? 'cod');
$note      = trim($_POST['note'] ?? '');

if ($full_name === '' || $address === '' || $phone === '') {
    header('Location: checkout.php');
    exit;
}

// Build cart product list from DB
$productIds = array_map('intval', array_keys($_SESSION['cart']));
if (empty($productIds)) {
    $_SESSION['checkout_error'] = 'Giỏ hàng trống.';
    header('Location: checkout.php');
    exit;
}
$idsSql = implode(',', $productIds);
$sql = "SELECT id, name, price, stock_quantity FROM products WHERE id IN ($idsSql)";
$res = $conn->query($sql);
if ($res === false) {
    $_SESSION['checkout_error'] = 'Lỗi lấy sản phẩm: ' . $conn->error;
    header('Location: checkout.php');
    exit;
}
$items = [];
$total = 0;

if ($res) {
    while ($p = $res->fetch_assoc()) {
        $pid = (int)$p['id'];
        $qty = (int)($_SESSION['cart'][$pid] ?? 0);
        if ($qty <= 0) continue;
        // Ensure stock is available
        if ((int)$p['stock_quantity'] < $qty) {
            // Not enough stock; redirect back with message (basic)
            $_SESSION['checkout_error'] = 'Sản phẩm "' . $p['name'] . '" không đủ số lượng trong kho.';
            header('Location: checkout.php');
            exit;
        }
        $subtotal = $qty * (float)$p['price'];
        $items[] = [
            'id' => $pid,
            'name' => $p['name'],
            'price' => (float)$p['price'],
            'qty' => $qty,
            'subtotal' => $subtotal,
        ];
        $total += $subtotal;
    }
}

if (empty($items)) {
    header('Location: checkout.php');
    exit;
}

// Helper: get columns for a table in current DB
function table_columns($conn, $table) {
    $cols = [];
    $q = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $conn->real_escape_string($table) . "'";
    if ($r = $conn->query($q)) {
        while ($row = $r->fetch_assoc()) $cols[] = $row['COLUMN_NAME'];
    }
    return $cols;
}

function table_exists($conn, $table) {
    $q = "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $conn->real_escape_string($table) . "'";
    if ($r = $conn->query($q)) {
        return $r->num_rows > 0;
    }
    return false;
}

$conn->begin_transaction();
try {
    // Optional voucher handling
    $voucher_id = null; $voucher_code = null; $discount = 0.0; $final_total = $total;
    if (!empty($_POST['voucher_code'])) {
        $voucher_code = strtoupper(trim($_POST['voucher_code']));
        $stmtV = $conn->prepare("SELECT * FROM vouchers WHERE code = ? LIMIT 1");
        if (!$stmtV) throw new Exception('Prepare voucher failed: ' . $conn->error);
        $stmtV->bind_param('s', $voucher_code);
        if (!$stmtV->execute()) throw new Exception('Execute voucher failed: ' . $stmtV->error);
        $rsV = $stmtV->get_result();
        if ($rsV && $rsV->num_rows > 0) {
            $v = $rsV->fetch_assoc();
            // Validate timeframe, min amount, usage
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
                // Enforce: each user can use a voucher code only once
                if ($user_id > 0) {
                    $v_id_tmp = (int)$v['id'];
                    $orders_has_voucher = in_array('voucher_id', table_columns($conn, 'orders'));
                    $used_once = false;
                    if ($orders_has_voucher) {
                        $q = $conn->prepare("SELECT 1 FROM orders WHERE user_id = ? AND voucher_id = ? LIMIT 1");
                        if ($q) { $q->bind_param('ii', $user_id, $v_id_tmp); $q->execute(); $r=$q->get_result(); $used_once = ($r && $r->num_rows>0); }
                    } elseif (table_exists($conn, 'voucher_usages')) {
                        $q = $conn->prepare("SELECT 1 FROM voucher_usages WHERE user_id = ? AND voucher_id = ? LIMIT 1");
                        if ($q) { $q->bind_param('ii', $user_id, $v_id_tmp); $q->execute(); $r=$q->get_result(); $used_once = ($r && $r->num_rows>0); }
                    }
                    if ($used_once) {
                        throw new Exception('Bạn đã sử dụng voucher này trước đó.');
                    }
                }
                if ($v['discount_type'] === 'percentage') {
                    $discount = $total * ((float)$v['discount_value'] / 100.0);
                    if (!is_null($v['max_discount'])) {
                        $discount = min($discount, (float)$v['max_discount']);
                    }
                } else { // fixed
                    $discount = (float)$v['discount_value'];
                }
                $discount = max(0, min($discount, $total));
                $voucher_id = (int)$v['id'];
                $final_total = max(0, $total - $discount);
            }
        }
    }

    // Insert into orders using available columns
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; // 0 if guest
    $status = 'pending';
    $created_at = date('Y-m-d H:i:s');
    $orders_cols = table_columns($conn, 'orders');

    $cols = ['user_id', 'total_amount', 'status', 'created_at'];
    $vals = [$user_id, ($final_total ?? $total), $status, $created_at];
    $types = 'idss';

    // Optional columns if they exist
    if (in_array('full_name', $orders_cols)) { $cols[] = 'full_name'; $vals[] = $full_name; $types .= 's'; }
    if (in_array('address', $orders_cols))   { $cols[] = 'address';   $vals[] = $address;   $types .= 's'; }
    if (in_array('shipping_address', $orders_cols)) { $cols[] = 'shipping_address'; $vals[] = $address; $types .= 's'; }
    if (in_array('phone', $orders_cols))     { $cols[] = 'phone';     $vals[] = $phone;     $types .= 's'; }
    if (in_array('phone_number', $orders_cols)) { $cols[] = 'phone_number'; $vals[] = $phone; $types .= 's'; }
    if (in_array('payment_method', $orders_cols)) { $cols[] = 'payment_method'; $vals[] = $payment; $types .= 's'; }
    if (in_array('note', $orders_cols))      { $cols[] = 'note';      $vals[] = $note;      $types .= 's'; }
    if (in_array('updated_at', $orders_cols)) { $cols[] = 'updated_at'; $vals[] = $created_at; $types .= 's'; }
    if ($voucher_id && in_array('voucher_id', $orders_cols)) { $cols[] = 'voucher_id'; $vals[] = $voucher_id; $types .= 'i'; }

    $cols_sql = implode(',', $cols);
    $placeholders = rtrim(str_repeat('?,', count($cols)), ',');
    $stmt = $conn->prepare("INSERT INTO orders ($cols_sql) VALUES ($placeholders)");
    if (!$stmt) throw new Exception('Prepare orders failed: ' . $conn->error);
    $stmt->bind_param($types, ...$vals);
    if (!$stmt->execute()) {
        throw new Exception('Không thể tạo đơn hàng: ' . $stmt->error);
    }
    $order_id = $conn->insert_id;

    // Insert order details and decrement stock
    // Detect details table name: order_details or order_items
    $details_table = table_exists($conn, 'order_details') ? 'order_details' : (table_exists($conn, 'order_items') ? 'order_items' : null);
    if ($details_table === null) {
        throw new Exception("Không tìm thấy bảng chi tiết đơn hàng ('order_details' hoặc 'order_items').");
    }
    $details_cols = table_columns($conn, $details_table);
    $hasPrice = in_array('price', $details_cols) || in_array('unit_price', $details_cols) || in_array('total_price', $details_cols);

    // Build detail insert dynamically
    foreach ($items as $it) {
        $pid = $it['id'];
        $qty = $it['qty'];
        $unit = (float)$it['price'];
        $total_line = $unit * $qty;

        $dCols = ['order_id','product_id','quantity'];
        $dVals = [$order_id, $pid, $qty];
        $dTypes = 'iii';
        if (in_array('price', $details_cols))      { $dCols[]='price';       $dVals[]=$unit;       $dTypes.='d'; }
        if (in_array('unit_price', $details_cols)) { $dCols[]='unit_price';  $dVals[]=$unit;       $dTypes.='d'; }
        if (in_array('total_price', $details_cols)){ $dCols[]='total_price'; $dVals[]=$total_line; $dTypes.='d'; }

        $dColsSql = implode(',', $dCols);
        $dPlace = rtrim(str_repeat('?,', count($dCols)), ',');
        $stmt_detail = $conn->prepare("INSERT INTO $details_table ($dColsSql) VALUES ($dPlace)");
        if (!$stmt_detail) throw new Exception('Prepare order_details failed: ' . $conn->error);
        $stmt_detail->bind_param($dTypes, ...$dVals);
        if (!$stmt_detail->execute()) {
            throw new Exception('Không thể thêm chi tiết đơn hàng: ' . $stmt_detail->error);
        }

        // Decrement stock
        $stmt_stock  = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        if (!$stmt_stock) throw new Exception('Prepare stock update failed: ' . $conn->error);
        $stmt_stock->bind_param('ii', $qty, $pid);
        if (!$stmt_stock->execute()) {
            throw new Exception('Không thể cập nhật kho: ' . $stmt_stock->error);
        }
    }

    // Increase voucher used_count if applied
    if ($voucher_id) {
        $stmtVU = $conn->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?");
        if ($stmtVU) { $stmtVU->bind_param('i', $voucher_id); $stmtVU->execute(); }
        // Record per-user usage if possible
        if ($user_id > 0 && table_exists($conn, 'voucher_usages')) {
            $stmtVU2 = $conn->prepare("INSERT INTO voucher_usages (voucher_id, user_id, used_at) VALUES (?, ?, NOW())");
            if ($stmtVU2) { $stmtVU2->bind_param('ii', $voucher_id, $user_id); $stmtVU2->execute(); }
        }
    }

    $conn->commit();

    // Clear cart
    $_SESSION['cart'] = [];

    // Add admin notification for new order
    if (function_exists('add_notification')) {
        $msg = 'Đơn hàng mới #' . $order_id . ' từ ' . ($full_name ?: 'khách hàng');
        $link = '/GoodZStore/Views/Admins/admin_orders.php?order_id=' . $order_id;
        add_notification('Đơn hàng', $msg, $link);
    }

} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['checkout_error'] = 'Lỗi đặt hàng: ' . $e->getMessage();
    // Optionally log $e->getMessage()
    header('Location: checkout.php');
    exit;
}

// Success page (no extra PHP endpoint required)
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công - GoodZStore</title>
    <link rel="stylesheet" href="../css/checkout.css">
    <link rel="stylesheet" href="/GoodZStore/Views/css/layout.css">
</head>
<body>
<?php include_once __DIR__ . '/../header.php'; ?>
<main class="container" style="max-width:720px;margin:48px auto;">
    <div style="background:#fff;border:1px solid #e6e6e6;border-radius:16px;padding:24px;box-shadow:0 8px 24px rgba(0,0,0,.06);text-align:center;">
        <div style="font-size:64px;line-height:1;margin-bottom:12px;">✅</div>
        <h2 style="margin:0 0 8px 0;">Đặt hàng thành công!</h2>
        <p style="color:#555;margin:0 0 16px 0;">Mã đơn hàng của bạn: <b>#<?php echo htmlspecialchars($order_id); ?></b>. Chúng tôi sẽ xử lý trong thời gian sớm nhất.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="/GoodZStore/Views/Users/index.php" class="btn" style="background:#FFD600;color:#222;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:600;">Về trang chủ</a>
            <a href="/GoodZStore/Views/Users/products.php" class="btn" style="background:#FFB088;color:#222;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:600;">Tiếp tục mua sắm</a>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../footer.php'; ?>
</body>
</html>
