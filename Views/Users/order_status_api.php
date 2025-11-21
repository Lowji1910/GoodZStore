<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Models/db.php';
session_start();

$order_id = intval($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_order']);
    exit;
}

$stmt = $conn->prepare('SELECT id, user_id, status, total_amount FROM orders WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}
$order = $res->fetch_assoc();

// NOTE: relaxed access check so order status page (buyer) can poll updates.
// For better security later, implement an order token for guest orders or
// require login and enforce user_id match. Currently we return status for
// any requester who knows the order_id.

echo json_encode([
    'order_id' => intval($order['id']),
    'status' => $order['status'],
    'total_amount' => floatval($order['total_amount'])
]);
exit;

?>
