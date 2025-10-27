<?php
// Views/Admins/notifications_api.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Models/notifications.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'mark_all_read') {
        $ok = mark_all_notifications_read();
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }
}

$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$list = [];
$res = get_recent_notifications($limit);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $list[] = [
            'id' => (int)$row['id'],
            'type' => $row['type'],
            'message' => $row['message'],
            'link' => $row['link'],
            'is_read' => (int)$row['is_read'],
            'created_at' => $row['created_at'],
        ];
    }
}
$count = get_unread_notification_count();

echo json_encode(['unread' => $count, 'items' => $list]);
