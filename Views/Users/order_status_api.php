<?php
require_once __DIR__ . '/../../Models/db.php';
header('Content-Type: application/json');

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['status' => $row['status']]);
        exit;
    }
}
echo json_encode(['status' => 'unknown']);
?>
