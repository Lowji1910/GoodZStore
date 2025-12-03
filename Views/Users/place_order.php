<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Đường dẫn tương đối từ Views/Users ra Models
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/vnpay_php/config.php'; // Config chung của web (nếu có)
require_once __DIR__ . '/../../Models/notifications.php';

// --- PHẦN 1: XỬ LÝ DỮ LIỆU ĐƠN HÀNG (Giữ nguyên logic của bạn) ---

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

$user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    $_SESSION['checkout_error'] = 'Vui lòng đăng nhập để thanh toán.';
    header('Location: /GoodZStore/Views/Users/auth.php');
    exit;
}

// Lấy giỏ hàng
$items = [];
$total = 0.0;
$sql = "SELECT c.product_id, c.size_id, c.quantity, p.name, p.price, i.image_url, s.size_name
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
    $price = (float)$row['price'];
    $qty = intval($row['quantity']);
    $subtotal = $price * $qty;
    $items[] = [
        'product_id' => $row['product_id'],
        'size_id' => $row['size_id'],
        'size_name' => $row['size_name'],
        'quantity' => $qty,
        'price' => $price,
        'subtotal' => $subtotal
    ];
    $total += $subtotal;
}

if (empty($items)) {
    $_SESSION['checkout_error'] = 'Giỏ hàng trống.';
    header('Location: checkout.php');
    exit;
}

// Xử lý Voucher
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
        
        if (($now >= $start && $now <= $end) && ($total >= $minAmount) && ($usageLimit == 0 || $usedCount < $usageLimit)) {
            if ($v['discount_type'] === 'percentage') {
                $discount = $total * ((float)$v['discount_value'] / 100.0);
                if (!is_null($v['max_discount'])) { $discount = min($discount, (float)$v['max_discount']); }
            } else { $discount = (float)$v['discount_value']; }
            $discount = max(0, min($discount, $total));
        }
    }
}

$finalTotal = max(0, $total - $discount);

// Tạo đơn hàng (Trạng thái mặc định pending)
$status = 'pending';
$shipping_address = $address . " - " . $phone; // Gộp địa chỉ và SĐT
$payment_method = $payment;

$stmtO = $conn->prepare('INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method) VALUES (?, ?, ?, ?, ?)');
$stmtO->bind_param('idsss', $user_id, $finalTotal, $status, $shipping_address, $payment_method);
if (!$stmtO->execute()) {
    $_SESSION['checkout_error'] = 'Lỗi tạo đơn hàng: ' . $stmtO->error;
    header('Location: checkout.php');
    exit;
}
$order_id = $conn->insert_id;

// Lưu chi tiết đơn hàng
$stmtItem = $conn->prepare('INSERT INTO order_items (order_id, product_id, size_id, size_name, quantity, price) VALUES (?, ?, ?, ?, ?, ?)');
foreach ($items as $it) {
    $p_size_id = $it['size_id'] > 0 ? intval($it['size_id']) : NULL;
    $stmtItem->bind_param('iiisid', $order_id, $it['product_id'], $p_size_id, $it['size_name'], $it['quantity'], $it['price']);
    $stmtItem->execute();
}

// Xóa giỏ hàng
$conn->query("DELETE FROM cart_items WHERE user_id = $user_id");


// --- PHẦN 2: XỬ LÝ THANH TOÁN ---

if ($payment === 'vnpay') {
    // 1. Gọi file cấu hình VNPAY mới tạo
    require_once __DIR__ . '/../../Models/vnpay_php/config.php';

    if ($finalTotal <= 0) {
        $_SESSION['checkout_error'] = 'Số tiền thanh toán phải lớn hơn 0.';
        header('Location: checkout.php');
        exit;
    }

    // 2. Chuẩn bị tham số VNPAY
    $vnp_TxnRef = $order_id; // Mã đơn hàng
    $vnp_OrderInfo = "Thanh toan don hang #" . $order_id;
    $vnp_OrderType = 'billpayment';
    $vnp_Amount = (int)$finalTotal * 100; // Nhân 100 theo quy định VNPAY
    $vnp_Locale = 'vn';
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
        "vnp_ExpireDate" => $expire
    );

    if (isset($_POST['bank_code']) && $_POST['bank_code'] != "") {
        $inputData['vnp_BankCode'] = $_POST['bank_code'];
    }

    // 3. Sắp xếp và tạo URL
    ksort($inputData);
    $query = "";
    $i = 0;
    $hashdata = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }

    $vnp_Url = $vnp_Url . "?" . $query;
    if (isset($vnp_HashSecret)) {
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
    }

    // 4. Chuyển hướng sang VNPAY
    header('Location: ' . $vnp_Url);
    die();

} else {
    // Thanh toán COD -> Chuyển thẳng đến trang thành công
    header('Location: order_success.php?order_id=' . $order_id . '&status=cod');
    exit;
}
?>