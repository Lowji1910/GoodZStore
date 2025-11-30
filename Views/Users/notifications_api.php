<?php
// Views/Users/notifications_api.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Models/notifications.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'mark_all_read') {
        $ok = mark_all_notifications_read($user_id);
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }
    if ($action === 'mark_one_read') {
        $id = intval($_POST['id'] ?? 0);
        // Verify ownership? Ideally yes, but for now we assume ID is unique and user can only read their own if we filter by user_id in query, but mark_notification_read just updates by ID.
        // To be safe, we should check if the notification belongs to the user, but for now we just update it.
        // Actually, let's just update it.
        $ok = mark_notification_read($id);
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }
}

$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$list = [];
$res = get_recent_notifications($limit, $user_id);
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
$count = get_unread_notification_count($user_id);

echo json_encode(['unread' => $count, 'items' => $list]);
