<?php
// Script để hash lại mật khẩu cho toàn bộ user trong bảng users
require_once __DIR__ . '/Models/db.php';
$sql = "SELECT id, password FROM users";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $plain = $row['password'];
        // Nếu đã hash rồi thì bỏ qua
        if (strlen($plain) === 60 && preg_match('/^\$2y\$/', $plain)) continue;
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $update->bind_param("si", $hash, $id);
        $update->execute();
    }
    echo "Đã hash xong mật khẩu cho toàn bộ user!";
} else {
    echo "Lỗi truy vấn: " . $conn->error;
}
?>
