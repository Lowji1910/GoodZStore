<?php
// Views/Users/vnpay_ipn.php
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/vnpay_helper.php';

$config = load_vnpay_env();
$vnp_HashSecret = $config['VNPAY_HASH_SECRET'] ?? '';
$inputData = array();

// Lấy dữ liệu VNPAY trả về
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash = $inputData['vnp_SecureHash'];
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
$orderId = $inputData['vnp_TxnRef'];
$vnp_Amount = $inputData['vnp_Amount'] / 100;

$returnData = array();

if ($secureHash == $vnp_SecureHash) {
    // Tìm đơn hàng
    $stmt = $conn->prepare("SELECT id, total_amount, status FROM orders WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        if (abs($order["total_amount"] - $vnp_Amount) < 1) { // Check số tiền
            if ($order["status"] == 'pending') { // Chỉ update nếu chưa xử lý
                if ($inputData['vnp_ResponseCode'] == '00') {
                    // Thành công -> Update trạng thái
                    $conn->query("UPDATE orders SET status = 'processing', payment_method = 'vnpay' WHERE id = $orderId");
                } else {
                    // Thất bại -> Hủy đơn
                    $conn->query("UPDATE orders SET status = 'cancelled' WHERE id = $orderId");
                }
                $returnData['RspCode'] = '00';
                $returnData['Message'] = 'Confirm Success';
            } else {
                $returnData['RspCode'] = '02';
                $returnData['Message'] = 'Order already confirmed';
            }
        } else {
            $returnData['RspCode'] = '04';
            $returnData['Message'] = 'Invalid amount';
        }
    } else {
        $returnData['RspCode'] = '01';
        $returnData['Message'] = 'Order not found';
    }
} else {
    $returnData['RspCode'] = '97';
    $returnData['Message'] = 'Invalid signature';
}

echo json_encode($returnData);
?>