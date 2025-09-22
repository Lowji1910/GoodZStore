<?php
$servername = "localhost";
$username = "root"; // mặc định XAMPP
$password = "";
$dbname = "goodzstore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Kết nối thất bại: " . $conn->connect_error);
} else {
    echo "✅ Kết nối MySQL thành công!";
}
?>
