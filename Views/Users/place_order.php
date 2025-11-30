<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/config.php';
require_once __DIR__ . '/../../Models/notifications.php';

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

// Get user ID
$user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    $_SESSION['checkout_error'] = 'Vui lòng đăng nhập để thanh toán.';
    header('Location: /GoodZStore/Views/Users/auth.php');
    exit;
}

// Fetch cart items from database
$items = [];
$total = 0.0;

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
if ($payment === 'vnpay') {
    
    // 1. Kiểm tra số tiền (QUAN TRỌNG)
    // Nếu đơn hàng 0 đồng thì không thanh toán qua VNPAY được -> báo lỗi hoặc auto success
    if ($finalTotal <= 0) {
        $_SESSION['checkout_error'] = 'Số tiền thanh toán phải lớn hơn 0 để dùng VNPAY.';
        header('Location: checkout.php');
        exit;
    }

    // 2. Chuẩn bị dữ liệu
    $vnp_TxnRef = $order_id; 
    
    // SỬA: Nội dung viết liền không dấu, bỏ ký tự #
    $vnp_OrderInfo = "ThanhToanDonHang_" . $order_id; 
    
    $vnp_OrderType = 'other';
    $vnp_Amount = (int)$finalTotal * 100; 
    $vnp_Locale = 'vn';
    $vnp_IpAddr = "127.0.0.1"; // Cứng IP Localhost
    
    // SỬA: Lấy thời gian expire từ config hoặc tự tạo lại nếu thiếu
    $startTime = date("YmdHis");
    $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

    // Tạo mảng dữ liệu
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
        "vnp_ExpireDate" => $expire // THÊM DÒNG NÀY
    );

    // 3. Sắp xếp và tạo URL (Giữ nguyên logic chuẩn)
    if (isset($vnp_BankCode) && $vnp_BankCode != "") {
        $inputData['vnp_BankCode'] = $vnp_BankCode;
    }
    
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
    }
    
    // Gán query bằng hashdata luôn (vì hashdata đã chuẩn format urlencoded rồi)
    $query = $hashdata; 

    $vnp_Url = $vnp_Url . "?" . $query;
    if (isset($vnp_HashSecret)) {
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
    }

    // DEBUG: Bỏ comment dòng dưới để kiểm tra link nếu vẫn lỗi
    // echo $vnp_Url; die();

    // 4. Chuyển hướng
    unset($_SESSION['cart']);
    header('Location: ' . $vnp_Url);
    die();

} else {
    // Xử lý COD
    unset($_SESSION['cart']); 
    header('Location: order_success.php?order_id=' . $order_id);
    exit;
}