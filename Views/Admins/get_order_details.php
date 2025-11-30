<?php
// Views/Admins/get_order_details.php
require_once __DIR__ . '/../../Models/db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$order_id = intval($_GET['id']);

// Get Order Info
$sql_order = "SELECT o.*, u.full_name, u.email, u.phone_number, u.address 
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              WHERE o.id = $order_id";
$res_order = $conn->query($sql_order);
$order = $res_order ? $res_order->fetch_assoc() : null;

if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit;
}

// Get Order Items
$sql_items = "SELECT od.*, p.name, p.image_url 
              FROM order_details od 
              JOIN products p ON od.product_id = p.id 
              WHERE od.order_id = $order_id";
$res_items = $conn->query($sql_items);
$items = [];
if ($res_items) {
    while ($row = $res_items->fetch_assoc()) {
        $items[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode(['order' => $order, 'items' => $items]);
