<?php
// Simple VNPAY helper. Configure credentials via environment variables
// or edit the values below.

$VNPAY_TMN_CODE = getenv('VNPAY_TMN_CODE') ?: '';
$VNPAY_HASH_SECRET = getenv('VNPAY_HASH_SECRET') ?: '';
$VNPAY_RETURN_URL = getenv('VNPAY_RETURN_URL') ?: (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/GoodZStore/Views/Users/vnpay_return.php';
$VNPAY_BASE_URL = getenv('VNPAY_BASE_URL') ?: 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';

function build_vnpay_url($orderId, $amount, $desc = '') {
    global $VNPAY_TMN_CODE, $VNPAY_HASH_SECRET, $VNPAY_RETURN_URL, $VNPAY_BASE_URL;

    $vnp_TmnCode = $VNPAY_TMN_CODE;
    $vnp_HashSecret = $VNPAY_HASH_SECRET;
    $vnp_Url = $VNPAY_BASE_URL;
    $vnp_Returnurl = $VNPAY_RETURN_URL;

    $vnp_TxnRef = strval($orderId);
    $vnp_OrderInfo = $desc ?: ('Thanh toan don hang #' . $orderId);
    $vnp_OrderType = 'other';
    $vnp_Amount = intval($amount) * 100; // VNPAY expects amount in cents (VND * 100)
    $vnp_Locale = 'vn';
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $vnp_CreateDate = date('YmdHis');

    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => $vnp_CreateDate,
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
    );

    ksort($inputData);
    $query = [];
    $hashdata = '';
    foreach ($inputData as $key => $value) {
        $query[] = urlencode($key) . '=' . urlencode($value);
        $hashdata .= $key . '=' . $value . '&';
    }
    $hashdata = rtrim($hashdata, '&');

    if (empty($vnp_HashSecret) || empty($vnp_TmnCode)) {
        // Return a safe error url with message param for debugging locally
        return $vnp_Url . '?error=missing_config';
    }

    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $query[] = 'vnp_SecureHash=' . $vnpSecureHash;
    $vnp_Url = $vnp_Url . '?' . implode('&', $query);
    return $vnp_Url;
}

function verify_vnpay_return($params) {
    global $VNPAY_HASH_SECRET;
    $vnp_SecureHash = $params['vnp_SecureHash'] ?? '';
    // Remove vnp_SecureHash and vnp_SecureHashType from params to build data string
    $filtered = $params;
    unset($filtered['vnp_SecureHash']);
    unset($filtered['vnp_SecureHashType']);
    ksort($filtered);
    $hashdata = '';
    foreach ($filtered as $key => $value) {
        if ($hashdata !== '') $hashdata .= '&';
        $hashdata .= $key . '=' . $value;
    }
    if (empty($VNPAY_HASH_SECRET)) return false;
    $calculated = hash_hmac('sha512', $hashdata, $VNPAY_HASH_SECRET);
    return hash_equals($calculated, $vnp_SecureHash);
}

?>
