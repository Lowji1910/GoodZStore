<?php
// Models/notifications.php
require_once __DIR__ . '/db.php';

// Ensure notifications table exists
$conn->query("CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50) NOT NULL,
  message VARCHAR(255) NOT NULL,
  link VARCHAR(255) DEFAULT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

function add_notification($type, $message, $link = null, $user_id = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (type, message, link, user_id) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('sssi', $type, $message, $link, $user_id);
        return $stmt->execute();
    }
    return false;
}

function get_unread_notification_count($user_id = null) {
    global $conn;
    if ($user_id) {
        $sql = "SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0 AND user_id = " . intval($user_id);
    } else {
        $sql = "SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0 AND user_id IS NULL";
    }
    $res = $conn->query($sql);
    if ($res) {
        $row = $res->fetch_assoc();
        return (int)($row['c'] ?? 0);
    }
    return 0;
}

function get_recent_notifications($limit = 10, $user_id = null) {
    global $conn;
    $limit = max(1, (int)$limit);
    if ($user_id) {
        $sql = "SELECT id, type, message, link, is_read, created_at FROM notifications WHERE user_id = " . intval($user_id) . " ORDER BY created_at DESC LIMIT $limit";
    } else {
        $sql = "SELECT id, type, message, link, is_read, created_at FROM notifications WHERE user_id IS NULL ORDER BY created_at DESC LIMIT $limit";
    }
    return $conn->query($sql);
}

function mark_all_notifications_read($user_id = null) {
    global $conn;
    if ($user_id) {
        return $conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND user_id = " . intval($user_id));
    } else {
        return $conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND user_id IS NULL");
    }
}

function mark_notification_read($id) {
    global $conn;
    $id = intval($id);
    return $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $id");
}
