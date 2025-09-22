<?php
$servername = "localhost";
$username = "root"; // mặc định XAMPP
$password = "";
$dbname = "goodzstore";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset để hiển thị tiếng Việt
$conn->set_charset("utf8mb4");
?>
