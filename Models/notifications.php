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

function add_notification($type, $message, $link = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (type, message, link) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('sss', $type, $message, $link);
        return $stmt->execute();
    }
    return false;
}

function get_unread_notification_count() {
    global $conn;
    $sql = "SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0";
    $res = $conn->query($sql);
    if ($res) {
        $row = $res->fetch_assoc();
        return (int)($row['c'] ?? 0);
    }
    return 0;
}

function get_recent_notifications($limit = 10) {
    global $conn;
    $limit = max(1, (int)$limit);
    $sql = "SELECT id, type, message, link, is_read, created_at FROM notifications ORDER BY created_at DESC LIMIT $limit";
    return $conn->query($sql);
}

function mark_all_notifications_read() {
    global $conn;
    return $conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
}
