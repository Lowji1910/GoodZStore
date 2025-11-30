<?php
// Views/Users/vnpay_return.php
session_start();
require_once __DIR__ . '/../../Models/vnpay_helper.php';

$config = load_vnpay_env();
$vnp_HashSecret = $config['VNPAY_HASH_SECRET'] ?? '';
$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();

foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}
unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$order_id = $_GET['vnp_TxnRef'];

if ($secureHash == $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        unset($_SESSION['cart']); 
        header("Location: /GoodZStore/Views/Users/order_success.php?order_id=$order_id&status=paid");
    } else {
        header("Location: /GoodZStore/Views/Users/order_success.php?order_id=$order_id&status=failed");
    }
} else {
    echo "Sai chữ ký bảo mật (Invalid Signature). Vui lòng kiểm tra file .env";
}
?>