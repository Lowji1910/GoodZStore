<?php
session_start();
require_once __DIR__ . '/../../Models/db.php';
require_once __DIR__ . '/../../Models/vnpay_helper.php';

$params = $_GET;
// Verify signature
$verified = verify_vnpay_return($params);

$orderId = intval($params['vnp_TxnRef'] ?? 0);
$respCode = $params['vnp_ResponseCode'] ?? null;

if (!$verified) {
    // Signature mismatch
    $_SESSION['checkout_error'] = 'Chữ ký VNPAY không hợp lệ.';
    header('Location: order_success.php?order_id=' . $orderId . '&status=failed');
    exit;
}

// Update order status depending on response code
if ($respCode === '00') {
    // Payment success
    $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $status = 'processing';
    $stmt->bind_param('si', $status, $orderId);
    $stmt->execute();
    header('Location: order_success.php?order_id=' . $orderId . '&status=paid');
    exit;
} else {
    $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $status = 'cancelled';
    $stmt->bind_param('si', $status, $orderId);
    $stmt->execute();
    header('Location: order_success.php?order_id=' . $orderId . '&status=failed');
    exit;
}

?>
